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
     * 未認証でお気に入り一覧にアクセスするとリダイレクトされる
     */
    public function test_guest_is_redirected_to_login_when_accessing_favorites(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みでお気に入り一覧が表示される
     */
    public function test_authenticated_user_can_access_favorites(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertStatus(200);
    }

    /**
     * 未認証でお気に入りのトグルができない（リダイレクト）
     */
    public function test_guest_cannot_toggle_favorite(): void
    {
        $book = Book::factory()->create();
        $response = $this->post(route('favorites.toggle', ['book' => $book->id]));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みでお気に入りのトグルができる
     */
    public function test_authenticated_user_can_toggle_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.toggle', ['book' => $book->id]));
        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * 既にお気に入りに入っている場合は解除される（トグル：解除）
     */
    public function test_authenticated_user_can_toggle_favorite_off(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->post(route('favorites.toggle', ['book' => $book->id]));
        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
