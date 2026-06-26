<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 会員登録画面が表示できる
     */
    public function test_registration_page_can_be_displayed(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * 会員登録ができる
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
    }

    /**
     * 会員登録時のバリデーションエラー（名前未入力）
     */
    public function test_registration_validation_error_when_name_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * 会員登録時のバリデーションエラー（メールアドレス未入力）
     */
    public function test_registration_validation_error_when_email_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * 会員登録時のバリデーションエラー（メールアドレス重複）
     */
    public function test_registration_validation_error_when_email_is_duplicate(): void
    {
        User::factory()->create(['email' => 'testuser@example.com']);

        $response = $this->post('/register', [
            'name' => 'Another User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * 会員登録時のバリデーションエラー（パスワード確認不一致）
     */
    public function test_registration_validation_error_when_password_confirmation_does_not_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'differentpassword',
        ]);
        $response->assertSessionHasErrors('password');
    }

    /**
     * ログイン画面が表示できる
     */
    public function test_login_page_can_be_displayed(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /**
     * ログインができる
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/'); // ログイン後のリダイレクト先を確認
        $this->assertAuthenticatedAs($user);
    }

    /**
     * ログイン失敗（パスワード間違い）
     */
    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * ログアウトができる
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * 既にログイン済みの場合、/loginにアクセスすると書籍一覧にリダイレクトされる
     */
    public function test_authenticated_user_redirected_from_login_to_books_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }

    /**
     *  既にログイン済みの場合、/registerにアクセスすると書籍一覧にリダイレクトされる
     */
    public function test_authenticated_user_redirected_from_register_to_books_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/');
    }
}
