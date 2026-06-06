<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;

class AttendanceController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $attendance = $user->attendances()
            ->whereDate('work_date', today())
            ->first();
        $status = $attendance ? $attendance->status() : Attendance::STATUS_NOT_WORKING;

        return view('attendance.stamp', compact('user', 'status', 'attendance'));
    }

    public function checkIn(Request $request)
    {
        $user = Auth::user();

        $attendance = $user->attendances()->firstOrCreate(
            ['work_date' => today()],
            ['check_in' => null, 'check_out' => null]
        );

        if ($attendance->check_in) {
            return redirect('/attendance')
                ->withErrors(['check_in' => '本日は既に出勤済です']);
        }

        $attendance->update(
            ['check_in' => now(),
        ]);

        return redirect('/attendance')->with('flashSuccess', '出勤しました！');
    }

    public function checkOut(Request $request)
    {
        $attendance = Auth::user()->attendances()
            ->whereDate('work_date', today())
            ->firstOrFail();

        if ($attendance->check_out) {
            return redirect('/attendance')
                ->withErrors(['check_out' => '本日は既に退勤済です']);
        }

        $attendance->update([
            'check_out' => now(),
        ]);

        return redirect('attendance')->with('flashSuccess', '退勤しました！');
    }

    public function startBreak(Request $request)
    {
        $attendance = Auth::user()->attendances()
            ->whereDate('work_date', today())
            ->firstOrFail();

        $attendance->breaks()->create([
            'break_start' => now(),
        ]);

        return redirect('/attendance')->with('flashSuccess', '休憩に入りました！');
    }

    public function endBreak(Request $request)
    {
        $attendance = Auth::user()->attendances()
            ->whereDate('work_date', today())
            ->firstOrFail();

        $break = $attendance->breaks()
            ->whereNull('break_end')
            ->latest()
            ->firstOrFail();

        $break->update([
            'break_end' => now(),
        ]);

        return redirect('/attendance')->with('flashSuccess', '休憩から戻りました！');
    }

    public function index()
    {
        $currentMonth = request('month')
        ? Carbon::parse(request('month'))
        : Carbon::now();

        $prevMonth = $currentMonth->copy()
            ->subMonth()
            ->format('Y-m');

        $nextMonth = $currentMonth->copy()
            ->addMonth()
            ->format('Y-m');

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $days = collect();

        for ($date = $start; $date <= $end; $date->addDay()) {
            $days->push($date->copy());
        }

        $attendances = Attendance::where('user_id', auth()->id())
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)
                    ->format('Y-m-d');
            });

        return view('attendance.index', compact(
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'days',
            'attendances'
        ));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $request->date,
        ]);

        return redirect('/attendance/detail/' . $attendance->id);
    }

    public function edit($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        $correction = AttendanceCorrection::with('breaks')
            ->where('attendance_id', $attendance->id)
            ->latest()
            ->first();

        if($correction) {
            return view('attendance.edit', [
                'attendance' => $attendance,
                'correction' => $correction,
                'isReadonly' => true,
            ]);
        }

        return view('attendance.edit', [
            'attendance' => $attendance,
            'isReadonly' => false,
        ]);
    }

    public function store(AttendanceCorrectionRequest $request)
    {
        $validated = $request->validated();
        $attendance = Attendance::findOrFail($validated['attendance_id']);

        DB::transaction(function () use ($validated, $attendance) {
                $attendanceCorrection = AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'requested_check_in' => $validated['requested_check_in'],
                'requested_check_out' => $validated['requested_check_out'],
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);

            foreach ($validated['breaks'] as $breakId => $break) {
                if (
                    $breakId === 'new' &&
                    empty($break['requested_break_start'] ?? null) &&
                    empty($break['requested_break_end'] ?? null)
                ) {
                    continue;
                }

                AttendanceCorrectionBreak::create([
                'attendance_correction_id' => $attendanceCorrection->id,
                'requested_break_start' => $break['requested_break_start'],
                'requested_break_end' => $break['requested_break_end'],
                ]);
            }
        });

        return redirect()->route('attendance.edit', $attendance->id)->with('flashSuccess', '修正を申請しました');
    }
}
