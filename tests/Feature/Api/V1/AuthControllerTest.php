<?php

namespace Tests\Feature\Api\V1;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 3-6-9: 正しいメール・パスワードでログインするとトークンが発行される
     */
    public function test_login_issues_token_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    /**
     * 3-6-10: 誤ったパスワードでログインすると422になる
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    /**
     * 3-6-11: ログアウトするとトークンが無効化される
     */
    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /**
     * 3-6-12: 無効化されたトークンで再度APIを叩くと401が返る
     */
    public function test_revoked_token_returns_401_on_next_request(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout');

        $this->app['auth']->forgetGuards();

        $genre = Genre::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'genres' => [$genre->id],
            ]);

        $response->assertStatus(401);
    }
}
