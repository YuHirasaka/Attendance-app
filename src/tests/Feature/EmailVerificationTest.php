<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    /**
    * 会員登録後に認証メールが送信されること
    */
    public function test_verification_email_is_sent_after_registration(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
    * 「認証はこちらから」をクリックしたらメール認証サイトに遷移すること
    */
    public function test_clicking_mailtrap_link_redirects_to_mailtrap_site(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertSee('認証はこちらから');
        $response->assertSee('href="https://mailtrap.io/inboxes"', false);
    }

    /**
     * メール認証後は勤怠登録画面へアクセスすること
     */
    public function test_verified_user_can_access_attendance_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
    }
}
