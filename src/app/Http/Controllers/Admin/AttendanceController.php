<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;


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
}
