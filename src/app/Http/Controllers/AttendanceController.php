<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function show()
    {
        $user = Auth::User();
        $attendance = $user->attendances()
            ->whereDate('work_date', today())
            ->first();
        $status = $attendance ? $attendance->status() : Attendance::STATUS_NOT_WORKING;

        return view('attendance.stamp', compact('user','status','attendance'));
    }

    public function checkIn(Request $request)
    {
        $user = Auth::user();

        $user->attendances()->create([
            'work_date' => today(),
            'check_in' => now(),
        ]);

        return redirect('/attendance')->with('flashSuccess', '出勤しました！');
    }

    public function checkOut(Request $request)
    {
        $attendance = Auth::user()->attendances()
            ->whereDate('work_date', today())
            ->firstOrFail();

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

        $attendance->Breaks()->create([
            'break_start' => now(),
        ]);

        return redirect('/attendance')->with('flashSuccess', '休憩に入りました！');
    }

    public function endBreak(Request $request)
    {
        $attendance = Auth::user()->attendances()
            ->whereDate('work_date', today())
            ->firstOrFail();

        $break = $attendance->Breaks()
            ->whereNull('break_end')
            ->latest()
            ->firstOrFail();

        $break->update([
            'break_end' => now(),
        ]);

        return redirect('/attendance')->with('flashSuccess', '休憩から戻りました！');
    }
}
