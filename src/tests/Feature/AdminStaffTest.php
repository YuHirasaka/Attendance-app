<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    /** 14-1 管理者が全一般ユーザーの氏名とメールアドレスを確認できること */
    public function test_admin_can_view_all_users_name_and_email()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $user1 = User::factory()->create([
            'name' => '山田 太郎',
            'email' => 'taro.y@coachtech.com',
        ]);
        $user2 = User::factory()->create([
            'name' => '増田 一世',
            'email' => 'issei.m@coachtech.com',
        ]);
        $user3 = User::factory()->create([
            'name' => '秋田 朋美',
            'email' => 'tomomi.a@coachtech.com',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/staff/list');
        $response->assertSeeText('山田 太郎');
        $response->assertSeeText('taro.y@coachtech.com');
        $response->assertSeeText('増田 一世');
        $response->assertSeeText('issei.m@coachtech.com');
        $response->assertSeeText('秋田 朋美');
        $response->assertSeeText('tomomi.a@coachtech.com');
    }

    /** 14-2 選択したユーザーの月次勤怠一覧が正しく表示されること */
    public function test_selected_user_monthly_attendance_is_displayed()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-01',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-02',
            'check_in' => '10:00:00',
            'check_out' => '17:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-03',
            'check_in' => '11:00:00',
            'check_out' => '20:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id);
        $response->assertSeeText('山田 太郎さんの勤怠');
        $response->assertSeeText('2026/06');
        $response->assertSeeText('06/01');
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
        $response->assertSeeText('06/02');
        $response->assertSeeText('10:00');
        $response->assertSeeText('17:00');
        $response->assertSeeText('06/03');
        $response->assertSeeText('11:00');
        $response->assertSeeText('20:00');
    }

    /** 14-3 前月ボタン押下時に選択ユーザーの前月の勤怠情報が表示されること */
    public function test_previous_month_attendance_is_displayed_for_selected_user()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $user = User::factory()->create();
        $times = [
            '2026-05-01' => '09:00:00',
            '2026-06-01' => '10:00:00',
        ];
        foreach ($times as $date => $checkIn) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
                'check_in' => $checkIn,
                'check_out' => '17:00:00',
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-05');
        $response->assertSeeText('2026/05');
        $response->assertSeeText('05/01');
        $response->assertSeeText('09:00');
        $response->assertSeeText('17:00');
        $response->assertDontSeeText('06/01');
        $response->assertDontSeeText('10:00');
    }

    /** 14-4 翌月ボタン押下時に選択ユーザーの翌月の勤怠情報が表示されること */
    public function test_next_month_attendance_is_displayed_for_selected_user()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $user = User::factory()->create();
        $times = [
            '2026-06-01' => '09:00:00',
            '2026-07-01' => '10:00:00',
        ];
        foreach ($times as $date => $checkIn) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
                'check_in' => $checkIn,
                'check_out' => '17:00:00',
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/staff/' . $user->id . '?month=2026-07');
        $response->assertSeeText('2026/07');
        $response->assertSeeText('07/01');
        $response->assertSeeText('10:00');
        $response->assertSeeText('17:00');
        $response->assertDontSeeText('06/01');
        $response->assertDontSeeText('09:00');
    }

    /** 14-5 詳細ボタン押下時にその日の勤怠詳細画面に遷移すること */
    public function test_admin_can_view_selected_user_attendance_detail()
    {
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

        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSeeText('2026年');
        $response->assertSeeText('6月7日');
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="17:00"', false);
    }
}
