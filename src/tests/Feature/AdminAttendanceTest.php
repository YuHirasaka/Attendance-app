<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function validAdminAttendanceData(): array
    {
        return [
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
            'note' => '修正しました',
        ];
    }
    private function validAdminAttendanceBreakData(): array
    {
        return [
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ];
    }

    /** 12-1 その日の全ユーザーの勤怠情報が正しく表示されること */
    public function test_all_users_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user1 = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        $user2 = User::factory()->create([
            'name' => '増田 一世',
        ]);

        $user3 = User::factory()->create([
            'name' => '秋田 朋美',
        ]);

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => '2026-06-07',
        ]);

        Attendance::factory()->create([
            'user_id' => $user3->id,
            'work_date' => '2026-06-07',
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');
        $response->assertSeeText('山田 太郎');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');

        $response->assertSeeText('増田 一世');

        $response->assertSeeText('秋田 朋美');
        $response->assertSeeText('08:00');
        $response->assertSeeText('17:00');
    }

    /** 12-2 管理者勤怠一覧画面に現在の日付が表示されること */
    public function test_current_date_is_displayed_in_admin_attendance_list()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');
        $response->assertSeeText('2026/06/07');
    }

    /** 12-3 前日ボタン押下時に前日の勤怠情報が表示されること */
    public function test_previous_day_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-06',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?day=2026-06-06');
        $response->assertSeeText('2026/06/06');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }

    /** 12-4 翌日ボタン押下時に翌日の勤怠情報が表示されること */
    public function test_next_day_attendance_is_displayed()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-08',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?day=2026-06-08');
        $response->assertSeeText('2026/06/08');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
    }

    /** 13-1 管理者勤怠詳細画面に選択した勤怠情報が表示されること */
    public function test_selected_attendance_information_is_displayed_in_admin_attendance_detail()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/' . $attendance->id);
        $response->assertSeeText($user->name);
        $response->assertSeeText('2026年');
        $response->assertSeeText('6月7日');
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="18:00"', false);
    }

    /** 13-2 管理者勤怠修正で出勤時間が退勤時間より後の場合エラー */
    public function test_admin_check_in_time_must_be_before_check_out_time()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $data = $this->validAdminAttendanceData();
        $data['user_id'] = $user->id;
        $data['check_in'] = '18:00';
        $data['check_out'] = '17:00';

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/save', $data);
        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** 13-3 管理者勤怠修正で休憩開始時間が退勤時間より後の場合エラー */
    public function test_admin_break_start_time_must_be_before_check_out_time()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $data = $this->validAdminAttendanceData();
        $data['user_id'] = $user->id;
        $data['check_out'] = '18:00';
        $data['breaks'][0] = [
            'break_start' => '19:00',
            'break_end' => '20:00',
        ];

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/save', $data);
        $response->assertSessionHasErrors([
            'breaks.0.break_start' => '休憩時間が不適切な値です',
        ]);
    }

    /** 13-4 管理者勤怠修正で休憩終了時間が退勤時間より後の場合エラー */
    public function test_admin_break_end_time_must_be_before_check_out_time()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $data = $this->validAdminAttendanceData();
        $data['user_id'] = $user->id;
        $data['check_out'] = '18:00';
        $data['breaks'][0] = [
            'break_start' => '17:00',
            'break_end' => '20:00',
        ];

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/save', $data);
        $response->assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** 13-5 管理者勤怠修正で備考未入力の場合エラー */
    public function test_admin_note_is_required()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $data = $this->validAdminAttendanceData();
        $data['user_id'] = $user->id;
        $data['note'] = '';

        $response = $this->actingAs($admin, 'admin')->post('/admin/attendance/save', $data);
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
