<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証でお気に入り一覧にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_cannot_access_favorites_index(): void
    {
        $response = $this->get('/favorites');
        $response->assertRedirect('/login');
    }

    /**
     * 認証済みでお気に入り一覧が表示されるテスト
     */
    public function test_authenticated_user_can_access_favorites_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/favorites');
        $response->assertStatus(200);
    }

    /**
     * 未認証でお気に入り追加できないテスト
     */
    public function test_guest_cannot_toggle_favorite(): void
    {
        $book = Book::factory()->create();

        $response = $this->post("/books/{$book->id}/favorites");
        $response->assertRedirect('/login');
    }

    /**
     * 認証済みでお気に入り追加できるテスト
     */
    public function test_authenticated_user_can_add_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/favorites");

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * お気に入りをトグルして解除できるテスト
     */
    public function test_authenticated_user_can_remove_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 一度お気に入りに追加
        $user->favoriteBooks()->attach($book->id);

        // もう一度押して解除
        $response = $this->actingAs($user)->post("/books/{$book->id}/favorites");

        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
