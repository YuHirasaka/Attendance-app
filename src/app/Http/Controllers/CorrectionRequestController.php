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
        $user = Auth::guard('admin')->user() ?? Auth::user();

        if ($user->role !== 'admin' && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $status = $page === 'approved' ? 'approved' : 'pending';

        $query = AttendanceCorrection::with('attendance.user')
            ->where('status', $status);

        if ($user->role !== 'admin') {
            $query->whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        $corrections = $query->get();

        if ($user->role === 'admin') {
            return view('admin.correction.index', compact(
                'page',
                'corrections',
            ));
        }

        return view('correction.index', [
            'attendanceCorrections' => $corrections,
            'page' => $page,
        ]);
    }

    public function show($attendanceCorrection_id)
    {
        $correction = AttendanceCorrection::with('attendance.user', 'breaks')
            ->where('id', $attendanceCorrection_id)
            ->whereHas('attendance', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();

        return view('correction.show', [
            'correction' => $correction,
            'user' => $correction->attendance->user,
            'workDate' => $correction->attendance->work_date,
        ]);
    }
}
