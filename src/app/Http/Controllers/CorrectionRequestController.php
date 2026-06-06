<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page', 'pending');
        $user = Auth::user();

        $status = $page === 'approved' ? 'approved' : 'pending';

        $attendanceCorrections = AttendanceCorrection::with('attendance.user')
            ->where('status', $status)
            ->whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        return view('correction.index', compact('attendanceCorrections', 'page'));
    }

    public function show($attendanceCorrection_id)
    {
        $correction = AttendanceCorrection::with('attendance.user', 'breaks')
            ->where('id', $attendanceCorrection_id)
            ->whereHas('attendance', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        $attendance = $correction->attendance;

        return view('correction.show', [
            'correction' => $correction,
            'user' => $correction->attendance->user,
            'workDate' => $correction->attendance->work_date,
        ]);
    }
}
