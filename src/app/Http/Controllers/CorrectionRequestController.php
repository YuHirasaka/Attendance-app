<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->page;
        $user = Auth::user();

        if ($page === 'pending') {
            $attendanceCorrections = AttendanceCorrection::where('status', 'pending')
            ->whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();
        } else {
            $attendanceCorrections = AttendanceCorrection::where('status','approved')
            ->whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();
        }

        return view('correction.index', compact('attendanceCorrections'));
    }

    public function show($attendanceCorrection_id)
    {
        $correction = AttendanceCorrection::with('attendance.breaks', 'breaks')->findOrFail($attendanceCorrection_id);

        $attendance = $correction->attendance;

        return view('attendance.edit', [
            'attendance' => $attendance,
            'correction' => $correction,
            'isReadonly' => true,
        ]);
    }
}
