<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class CorrectionTest extends TestCase
{
    use RefreshDatabase;

    private function validCorrectionData(): array
    {
        return [
            'requested_check_in' => '09:00',
            'requested_check_out' => '17:00',
            'reason' => '休日出勤です',
        ];
    }
    private function validCorrectionBreakData(): array
    {
        return [
            'requested_break_start' => '12:00',
            'requested_break_end' => '13:00',
        ];
    }

    /** 11-1 修正画面で出勤時間が退勤時間より後の場合エラー */
    public function test_check_in_time_must_be_before_check_out_time()
    {
        $user = User::factory()->create();
        $data = $this->validCorrectionData();
        $data['requested_check_in'] = '18:00';

        $response = $this->actingAs($user)->post('/stamp_correction_request', $data);
        $response->assertSessionHasErrors([
            'requested_check_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /**　11-2　修正画面で休憩開始時間が退勤時間より後の場合エラー */
    public function test_break_start_time_must_be_before_check_out_time()
    {
        $user = User::factory()->create();
        $data = $this->validCorrectionData();
        $data['requested_check_out'] = '18:00';
        $data['breaks'][0]['requested_break_start'] = '19:00';

        $response = $this->actingAs($user)->post('/stamp_correction_request', $data);
        $response->assertSessionHasErrors([
            'breaks.0.requested_break_start' => '休憩時間が不適切な値です',
        ]);
    }

    /** 11-3 修正画面で休憩終了時間が退勤時間より後の場合エラー */
    public function test_break_end_time_must_be_before_check_out_time()
    {
        $user = User::factory()->create();
        $data = $this->validCorrectionData();
        $data['breaks'][0]['requested_break_end'] = '18:00';

        $response = $this->actingAs($user)->post('/stamp_correction_request', $data);
        $response->assertSessionHasErrors([
            'breaks.0.requested_break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** 11-4 修正画面で備考欄が未入力の場合エラー */
    public function test_reason_is_required()
    {
        $user = User::factory()->create();
        $data = $this->validCorrectionData();
        $data['reason'] = '';

        $response = $this->actingAs($user)->post('/stamp_correction_request', $data);
        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    /** 11-5 修正申請処理 */
    public function test_user_can_submit_correction_request()
    {
        Carbon::setTestNow('2026-06-07 09:00:00');
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
        ]);

        $data = $this->validCorrectionData();
        $data['attendance_id'] = $attendance->id;
        $data['requested_check_in'] = '10:00';
        $data['requested_check_out'] = '18:00';
        $data['breaks'] = [
            [
                'requested_break_start' => '12:00',
                'requested_break_end' => '13:00',
            ],
        ];

        $response = $this->actingAs($user)->post('/stamp_correction_request', $data);
        $response->assertRedirect(route('attendance.edit', $attendance->id));

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'requested_check_in' => '2026-06-07 10:00:00',
            'requested_check_out' => '2026-06-07 18:00:00',
            'reason' => '休日出勤です',
        ]);

        $correction = AttendanceCorrection::where('attendance_id', $attendance->id)->first();

        $response = $this->actingAs($admin, 'admin')
            ->actingAs($admin)
            ->get('/admin/stamp_correction_request/approve/' . $correction->id);

        $response->assertStatus(200);
        $response->assertSeeText('休日出勤です');

        $response = $this->actingAs($admin, 'admin')
            ->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?page=pending');

        $response->assertSeeText('休日出勤です');
    }

    /** 11-6 承認待ち一覧表示 */
    public function test_user_can_see_pending_correction_list()
    {
        $user = User::factory()->create();

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

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?page=pending');
        $response->assertSeeText('修正理由1');
        $response->assertSeeText('修正理由2');
        $response->assertSeeText('修正理由3');
    }

    /** 11-7 承認済み一覧表示 */
    public function test_user_can_see_approved_correction_list()
    {
        $user = User::factory()->create();

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

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?page=approved');
        $response->assertSeeText('修正理由1');
        $response->assertSeeText('修正理由2');
        $response->assertSeeText('修正理由3');
    }

    /** 11-8 申請詳細遷移 */
    public function test_user_can_see_correction_detail()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-01',
        ]);

        $attendanceCorrection = AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/' . $attendanceCorrection->id);
        $response->assertStatus(200);
        $response->assertSeeText($user->name);
        $response->assertSeeText($attendanceCorrection->reason);
    }
}
