<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrection;
use App\Http\Requests\AdminAttendanceRequest;


class AttendanceController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->get();
        $currentDay = request('day')
        ? Carbon::parse(request('day'))
        : Carbon::now();

        $prevDay = $currentDay->copy()
            ->subDay()
            ->format('Y-m-d');

        $nextDay = $currentDay->copy()
            ->addDay()
            ->format('Y-m-d');

        $attendances = Attendance::whereDate('work_date', $currentDay->format('Y-m-d'))
            ->get()
            ->keyBy('user_id');

        return view('admin.attendance.index', compact(
            'users',
            'currentDay',
            'prevDay',
            'nextDay',
            'attendances'
        ));
    }

    public function create(Request $request)
    {
        $user = User::where('role', 'user')
            ->findOrFail($request->user_id);

        $day = $request->day;

        $attendance = Attendance::with('breaks', 'user')
            ->where('user_id', $user->id)
            ->whereDate('work_date', $day)
            ->first();

        return view('admin.attendance.edit', [
            'user' => $user,
            'day' => $day,
            'attendance' => $attendance,
            'correction' => null,
            'isReadonly' => false,
        ]);
    }

    public function edit($id)
    {
        $attendance = Attendance::with('user', 'breaks')->findOrFail($id);

        $correction = AttendanceCorrection::with('breaks')
            ->where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if($correction) {
            return view('admin.attendance.edit', [
                'attendance' => $attendance,
                'user' => $attendance->user,
                'day' => $attendance->work_date->format('Y-m-d'),
                'correction' => $correction,
                'isReadonly' => true,
            ]);
        }

        return view('admin.attendance.edit', [
            'attendance' => $attendance,
            'user' => $attendance->user,
            'day' => $attendance->work_date->format('Y-m-d'),
            'correction' => $correction,
            'isReadonly' => false,
        ]);
    }

    public function save(AdminAttendanceRequest $request)
    {
        $validated = $request->validated();

        $attendance = DB::transaction(function () use ($validated) {
            $attendance = Attendance::updateOrCreate([
                    'user_id' => $validated['user_id'],
                    'work_date' => $validated['work_date'],
                ],
                [
                    'check_in' => $validated['check_in'],
                    'check_out' => $validated['check_out'],
                    'note' => $validated['note']
                ]);

            foreach ($validated['breaks'] ?? [] as $breakId => $break) {
                if (
                    $breakId === 'new' &&
                    empty($break['break_start'] ?? null) &&
                    empty($break['break_end'] ?? null)
                ) {
                    continue;
                }

                if ($breakId === 'new') {
                    $attendance->breaks()->create([
                        'break_start' => $break['break_start'],
                        'break_end' => $break['break_end'],
                    ]);
                } else {
                    AttendanceBreak::where('id', $breakId)
                        ->where('attendance_id', $attendance->id)
                        ->update([
                            'break_start' => $break['break_start'],
                            'break_end' => $break['break_end'],
                        ]);
                }
            }

            return $attendance;
        });

        AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->update(['status' => 'approved']);

        return redirect()->route('admin.attendance.edit', $attendance->id)->with('flashSuccess', '勤怠を更新しました');
    }
}
