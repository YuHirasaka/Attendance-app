<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function validRegisterData(): array
    {
        return [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }

    private function validLoginData(): array
    {
        return [
            'email' => 'test@example.com',
            'password' => 'password',
        ];
    }

    private function validAdminLoginData(): array
    {
        return [
            'email' => 'admin@example.com',
            'password' => 'password',
        ];
    }

    /** 1-1 名前が未入力の場合エラーが表示されること */
    public function test_register_requires_name()
    {
        $data = $this->validRegisterData();
        $data['name'] = '';
        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
        $this->assertGuest();
    }

    /** 1-2　メールアドレスが未入力の場合エラーが表示されること */
    public function test_register_requires_email()
    {
        $data = $this->validRegisterData();
        $data['email'] = '';
        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
        $this->assertGuest();
    }

    /** 1-3 パスワードが8文字未満の場合エラーが表示されること */
    public function test_password_must_be_at_least_8_characters()
    {
        $data = $this->validRegisterData();
        $data['password'] = 'short';
        $data['password_confirmation'] = 'short';
        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
        $this->assertGuest();
    }

    /** 1-4 確認用パスワードが一致しない場合エラーが表示されること */
    public function test_password_confirmation_must_be_matched()
    {
        $data = $this->validRegisterData();
        $data['password_confirmation'] = 'mismatched';
        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
        ]);
        $this->assertGuest();
    }

    /** 1-5 パスワードが未入力の場合エラーが表示されること */
    public function test_register_requires_password()
    {
        $data = $this->validRegisterData();
        $data['password'] = '';
        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
        $this->assertGuest();
    }

    /** 1-6 会員登録が完了しデータベースにユーザーが登録されること */
    public function test_user_registration_succeeds_and_user_is_registered_in_the_database()
    {
        $data = $this->validRegisterData();
        $response = $this->post('/register', $data);

        $response->assertRedirect('/email/verify');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    /** 2-1 ログイン時にメールアドレスが未入力の場合エラーが表示されること */
    public function test_login_requires_email()
    {
        $data = $this->validLoginData();
        $data['email'] = '';
        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
        $this->assertGuest();
    }

    /** 2-2 ログイン時にパスワードが未入力の場合エラーが表示されること */
    public function test_login_requires_password()
    {
        $data = $this->validLoginData();
        $data['password'] = '';
        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
        $this->assertGuest();
    }

    /** 2-3 ログイン時にメールアドレスが登録内容と一致しない場合エラーが表示されること */
    public function test_login_fails_with_incorrect_email()
    {
        $data = $this->validLoginData();
        $data['email'] = 'wrong@example.com';
        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
        $this->assertGuest();
    }

    /** 3-1 管理者ログイン時にメールアドレスが未入力の場合エラーが表示されること */
    public function test_admin_login_requires_email()
    {
        $data = $this->validAdminLoginData();

        $data['email'] = '';
        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
        $this->assertGuest();
    }

    /** 3-2 管理者ログイン時にパスワードが未入力の場合エラーが表示されること */
    public function test_admin_login_requires_password()
    {
        $data = $this->validAdminLoginData();
        $data['password'] = '';
        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
        $this->assertGuest();
    }

    /** 3-3 管理者ログイン時にメールアドレスが登録内容と一致しない場合エラーが表示されること */
    public function test_admin_login_fails_with_incorrect_email()
    {
        $data = $this->validAdminLoginData();
        $data['email'] = 'wrong@example.com';
        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
        $this->assertGuest();
    }
}
