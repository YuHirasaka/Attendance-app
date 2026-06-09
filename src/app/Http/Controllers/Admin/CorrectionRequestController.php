<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\DB;

class CorrectionRequestController extends Controller
{
    public function show($attendance_correct_request_id)
    {
        $correction = AttendanceCorrection::with('attendance.breaks', 'breaks')
            ->findOrFail($attendance_correct_request_id);

        return view('admin.correction.show', compact('correction'));
    }

    public function update($attendance_correct_request_id)
    {
        DB::transaction(function () use ($attendance_correct_request_id){
            $correction = AttendanceCorrection::with('attendance', 'breaks')
                ->where('id', $attendance_correct_request_id)
                ->where('status', 'pending')
                ->firstOrFail();

            $attendance = $correction->attendance;

            $attendance->update([
                'check_in' => $correction->requested_check_in,
                'check_out' => $correction->requested_check_out,
                'note' => $correction->reason,
            ]);

            $attendance->breaks()->delete();

            foreach ($correction->breaks as $breakCorrection) {
                $attendance->breaks()->create([
                    'break_start' => $breakCorrection->requested_break_start,
                    'break_end' => $breakCorrection->requested_break_end,
                ]);
            }

            $correction->update([
                'status' => 'approved',
                'approved_by' => auth('admin')->id(),
                'approved_at' => now(),
            ]);

        });

        return redirect()->route('correction.index')->with('flashSuccess', '勤怠修正申請を承認しました');
    }
}
