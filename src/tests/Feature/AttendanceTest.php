<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** 4-1 現在の日時情報が表示されること */
    public function test_current_date_is_displayed()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('2026年6月7日(日)');
        $response->assertSeeText('10:00');
    }

    /**　5-1　勤務外の時にステータスが勤務外と表示されること */
    public function test_status_is_displayed_as_not_working()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('勤務外');
    }

    /** 5-2 出勤中の時にステータスが出勤中と表示されること */
    public function test_status_is_displayed_as_working()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('出勤中');
    }

    /** 5-3 休憩中の時にステータスが休憩中と表示されること */
    public function test_status_is_displayed_as_break()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');
        $this->actingAs($user)->post('/attendance/break-start');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('休憩中');
    }

    /** 5-4 退勤済みの時にステータスが退勤済と表示されること */
    public function test_status_is_displayed_as_done()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');
        $this->actingAs($user)->post('/attendance/check-out');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('退勤済');
    }

    /** 6-1 出勤ボタンを押下した時に出勤中と表示されること */
    public function test_user_can_check_in()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSeeText('勤務外');
        $response->assertSeeText('出勤');

        $this->actingAs($user)->post('/attendance/check-in');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('出勤中');
    }

    /** 6-2 出勤は１日一回のみできること */
    public function test_user_can_check_in_only_once_per_day()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');
        $this->actingAs($user)->post('/attendance/check-out');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSeeText('退勤済');
        $response->assertSeeText('お疲れ様でした。');
        $response->assertDontSee('<button class="btn attendance-card__button">出勤</button>');
    }

    /** 6-3　出勤時刻が勤怠一覧画面で確認できること */
    public function test_check_in_time_is_displayed_in_attendance_list()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSeeText('10:00');
    }

    /**　7-1　休憩ボタンを押下した時に休憩中と表示されること */
    public function test_user_can_start_break()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSeeText('出勤中');
        $response->assertSeeText('休憩入');

        $this->actingAs($user)->post('/attendance/break-start');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('休憩中');
    }

    /**　7-2　休憩は１日に何回でもできること */
    public function test_user_can_start_break_multiple_times_per_day()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');

        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('休憩入');
    }

    /** 7-3 休憩戻りボタンを押下した時に出勤中と表示されること */
    public function test_user_can_end_break()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');
        $this->actingAs($user)->post('/attendance/break-start');
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('休憩中');
        $response->assertSeeText('休憩戻');

        $this->actingAs($user)->post('/attendance/break-end');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeText('出勤中');
    }

    /**　7-4　休憩戻は１日に何回でもできること */
    public function test_user_can_end_break_multiple_times_per_day()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');
        $this->actingAs($user)->post('/attendance/break-start');
        $this->actingAs($user)->post('/attendance/break-end');
        $this->actingAs($user)->post('/attendance/break-start');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSeeText('休憩中');
        $response->assertSeeText('休憩戻');
    }

    /** 7-5 休憩時刻が勤怠一覧画面で確認できること */
    public function test_break_time_is_displayed_in_attendance_list()
    {
        Carbon::setTestNow('2026-06-07 09:00:00');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/attendance/check-in');

        Carbon::setTestNow('2026-06-07 12:00:00');
        $this->actingAs($user)->post('/attendance/break-start');

        Carbon::setTestNow('2026-06-07 13:00:00');
        $this->actingAs($user)->post('/attendance/break-end');

        Carbon::setTestNow('2026-06-07 17:00:00');
        $this->actingAs($user)->post('/attendance/check-out');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSeeText('1:00');
    }

    /** 8-1 退勤ボタンを押下した時に退勤済と表示されること */
    public function test_user_can_check_out()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSeeText('出勤中');
        $response->assertSeeText('退勤');

        $this->actingAs($user)->post('/attendance/check-out');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSeeText('退勤済');
    }

    /** 8-2 退勤時刻が勤怠一覧画面で確認できること */
    public function test_check_out_time_is_displayed_in_attendance_list()
    {
        Carbon::setTestNow('2026-06-07 09:00:00');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/check-in');

        Carbon::setTestNow('2026-06-07 17:00:00');
        $this->actingAs($user)->post('/attendance/check-out');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSeeText('17:00');
    }

    /** 9-1  自分の勤怠情報が全て表示されること*/
    public function test_all_attendance_information_is_displayed()
    {
        $user = User::factory()->create();

        $times = [
            '2026-06-01' => '09:00:00',
            '2026-06-02' => '10:00:00',
            '2026-06-03' => '11:00:00',
        ];
        foreach ($times as $date => $checkIn) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
                'check_in' => $checkIn,
                'check_out' => '17:00:00',
            ]);

            AttendanceBreak::factory()->create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
            ]);
        }

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSeeText('09:00');
        $response->assertSeeText('10:00');
        $response->assertSeeText('11:00');
    }

    /** 9-2 勤怠一覧画面に遷移した時に現在の月が表示されること */
    public function test_current_month_is_displayed_in_attendance_list()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSeeText(Carbon::now()->format('Y/m'));
        $response->assertDontSeeText('2026/05');
        $response->assertDontSeeText('2026/07');
    }

    /** 9-3 前月を押下した時に前月の勤怠情報が表示されていること */
    public function test_previous_month_is_displayed_in_attendance_list()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
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

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-05');
        $response->assertSeeText('05/01');
        $response->assertSeeText('09:00');
        $response->assertSeeText('17:00');
        $response->assertDontSeeText('06/01');
        $response->assertDontSeeText('10:00');
    }

    /** 9-4 翌月を押下した時に翌月の勤怠情報が表示されていること */
    public function test_next_month_is_displayed_in_attendance_list()
    {
        Carbon::setTestNow('2026-06-07 10:00:00');
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

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-07');
        $response->assertSeeText('07/01');
        $response->assertSeeText('10:00');
        $response->assertSeeText('17:00');
        $response->assertDontSeeText('06/01');
        $response->assertDontSeeText('09:00');
    }

    /** 9-5 詳細ボタンを押下した時に勤怠詳細画面に遷移すること */
    public function test_detail_button_opens_attendance_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('/attendance/detail/' . $attendance->id);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
    }

    /** 10-1 勤怠詳細画面の名前がログインユーザーになっていること */
    public function test_name_is_displayed_in_attendance_detail_page()
    {
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertSeeText('山田 太郎');
    }

    /** 10-2 勤怠詳細画面の日付が選択した日付になっていること */
    public function test_date_is_displayed_in_attendance_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertSeeText('2026年');
        $response->assertSeeText('6月7日');
    }

    /** 10-3 出勤・退勤時刻が勤怠詳細画面で確認できること */
    public function test_check_in_and_check_out_time_is_displayed_in_attendance_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="17:00"', false);
    }

    /** 10-4 休憩時間が勤怠詳細画面で確認できること */
    public function test_break_time_is_displayed_in_attendance_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-06-07',
            'check_in' => '09:00:00',
            'check_out' => '17:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="13:00"', false);
    }
}
