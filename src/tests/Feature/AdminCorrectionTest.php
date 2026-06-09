<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;
use Carbon\Carbon;

class AdminCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /** 15-1 承認待ち一覧表示 */
    public function test_admin_can_see_pending_correction_list()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-01',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-02',
        ]);

        $attendance3 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-03',
        ]);

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance1->id,
            'status' => 'pending',
            'reason' => '修正理由1',
        ]);

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance2->id,
            'status' => 'pending',
            'reason' => '修正理由2',
        ]);

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance3->id,
            'status' => 'pending',
            'reason' => '修正理由3',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?page=pending');
        $response->assertSeeText('修正理由1');
        $response->assertSeeText('修正理由2');
        $response->assertSeeText('修正理由3');
    }

    /** 15-2 承認済み一覧表示 */
    public function test_admin_can_see_approved_correction_list()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-01',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-02',
        ]);

        $attendance3 = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-03',
        ]);

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance1->id,
            'status' => 'approved',
            'reason' => '修正理由1',
        ]);

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance2->id,
            'status' => 'approved',
            'reason' => '修正理由2',
        ]);

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance3->id,
            'status' => 'approved',
            'reason' => '修正理由3',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/list?page=approved');
        $response->assertSeeText('修正理由1');
        $response->assertSeeText('2026/06/01');
        $response->assertSeeText('修正理由2');
        $response->assertSeeText('2026/06/02');
        $response->assertSeeText('修正理由3');
        $response->assertSeeText('2026/06/03');
    }

    /** 15-3 承認待ち詳細表示 */
    public function test_admin_can_see_pending_correction_detail()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-01',
        ]);

        $attendanceCorrection = AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_check_in' => '10:00:00',
            'requested_check_out' => '18:00:00',
            'status' => 'pending',
            'reason' => '修正理由1',
        ]);

        $attendanceCorrectionBreak = AttendanceCorrectionBreak::factory()->create([
            'attendance_correction_id' => $attendanceCorrection->id,
            'requested_break_start' => '13:00:00',
            'requested_break_end' => '14:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/stamp_correction_request/approve/' . $attendanceCorrection->id);
        $response->assertStatus(200);
        $response->assertSeeText('2026年');
        $response->assertSeeText('6月1日');
        $response->assertSeeText('10:00');
        $response->assertSeeText('18:00');
        $response->assertSeeText('13:00');
        $response->assertSeeText('14:00');
        $response->assertSeeText('修正理由1');
    }

    /** 15-4 修正申請の承認処理 */
    public function test_admin_can_approve_correction_request()
    {
        Carbon::setTestNow('2026-06-01 10:00:00');
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-01',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        $attendanceCorrection = AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_check_in' => '10:00:00',
            'requested_check_out' => '18:00:00',
            'status' => 'pending',
            'reason' => '電車遅延のため',
        ]);

        $attendanceCorrectionBreak = AttendanceCorrectionBreak::factory()->create([
            'attendance_correction_id' => $attendanceCorrection->id,
            'requested_break_start' => '13:00:00',
            'requested_break_end' => '14:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->patch('/stamp_correction_request/approve/' . $attendanceCorrection->id);
        $response->assertRedirect(route('correction.index'));

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $attendanceCorrection->id,
            'attendance_id' => $attendance->id,
            'requested_check_in' => '2026-06-01 10:00:00',
            'requested_check_out' => '2026-06-01 18:00:00',
            'reason' => '電車遅延のため',
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('attendance_correction_breaks', [
            'attendance_correction_id' => $attendanceCorrection->id,
            'requested_break_start' => '2026-06-01 13:00:00',
            'requested_break_end' => '2026-06-01 14:00:00',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-06-01 13:00:00',
            'break_end' => '2026-06-01 14:00:00',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'check_in' => '2026-06-01 10:00:00',
            'check_out' => '2026-06-01 18:00:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '2026-06-01 13:00:00',
            'break_end' => '2026-06-01 14:00:00',
        ]);
    }
}