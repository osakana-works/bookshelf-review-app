<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証でジャンル一覧にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_cannot_access_genre_index(): void
    {
        $response = $this->get('/genres');
        $response->assertRedirect('/login');
    }

    /**
     * 認証済みでジャンル一覧が表示されるテスト
     */
    public function test_authenticated_user_can_access_genre_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/genres');
        $response->assertStatus(200);
    }

    /**
     * 認証済みでジャンル登録画面が表示されるテスト
     */
    public function test_authenticated_user_can_access_genre_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/genres/create');
        $response->assertStatus(200);
    }

    /**
     * ジャンルが登録できるテスト
     */
    public function test_authenticated_user_can_create_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', [
            'name' => 'テストジャンル',
        ]);

        $response->assertRedirect('/genres');
        $this->assertDatabaseHas('genres', ['name' => 'テストジャンル']);
    }

    /**
     * ジャンル登録時のバリデーションエラーのテスト
     */
    public function test_genre_store_validation_fails_with_missing_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * ジャンル名の重複登録ができないテスト
     */
    public function test_genre_store_validation_fails_with_duplicate_name(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->actingAs($user)->post('/genres', [
            'name' => '小説',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * ジャンルが更新できるテスト
     */
    public function test_authenticated_user_can_update_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", [
            'name' => '更新ジャンル',
        ]);

        $response->assertRedirect('/genres');
        $this->assertDatabaseHas('genres', ['name' => '更新ジャンル']);
    }

    /**
     * 書籍が紐づいていないジャンルは削除できるテスト
     */
    public function test_genre_without_books_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect('/genres');
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    /**
     * 書籍が紐づいているジャンルは削除できないテスト
     */
    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect('/genres');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    /**
     * 認証済みでジャンル詳細が表示されるテスト
     */
    public function test_authenticated_user_can_access_genre_show(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get("/genres/{$genre->id}");
        $response->assertStatus(200);
    }

    /**
     * 未認証でジャンル詳細にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_cannot_access_genre_show(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get("/genres/{$genre->id}");
        $response->assertRedirect('/login');
    }
}
