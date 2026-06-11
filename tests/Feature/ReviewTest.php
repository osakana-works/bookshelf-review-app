<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証でレビュー投稿できないテスト
     */
    public function test_guest_cannot_post_review(): void
    {
        $book = Book::factory()->create();

        $response = $this->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * 認証済みでレビュー投稿できるテスト
     */
    public function test_authenticated_user_can_post_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'テストコメント',
        ]);
    }

    /**
     * レビュー投稿時のバリデーションエラーのテスト
     */
    public function test_review_store_validation_fails_with_missing_rating(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => '',
            'comment' => 'テストコメント',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    /**
     * 認証済みで自分のレビュー編集画面が表示されるテスト
     */
    public function test_review_owner_can_access_edit(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/reviews/{$review->id}/edit");
        $response->assertStatus(200);
    }

    /**
     * 他人のレビュー編集画面にアクセスできないテスト
     */
    public function test_non_owner_cannot_access_review_edit(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->get("/reviews/{$review->id}/edit");
        $response->assertStatus(403);
    }

    /**
     * 認証済みで自分のレビューを更新できるテスト
     */
    public function test_review_owner_can_update_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'rating' => 3,
            'comment' => '更新コメント',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新コメント',
        ]);
    }

    /**
     * 他人のレビューは更新できないテスト
     */
    public function test_non_owner_cannot_update_review(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->put("/reviews/{$review->id}", [
            'rating' => 3,
            'comment' => '更新コメント',
        ]);

        $response->assertStatus(403);
    }

    /**
     * 認証済みで自分のレビューを削除できるテスト
     */
    public function test_review_owner_can_delete_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /**
     * 他人のレビューは削除できないテスト
     */
    public function test_non_owner_cannot_delete_review(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->delete("/reviews/{$review->id}");
        $response->assertStatus(403);
    }
}
