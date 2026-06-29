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
     * 未認証でレビュー投稿できない
     */
    public function test_guest_cannot_create_review(): void
    {
        $book = Book::factory()->create();
        $response = $this->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => 'テストレビュー',
            'rating' => 4,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みでレビュー投稿できる
     */
    public function test_authenticated_user_can_create_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => 'テストレビュー',
            'rating' => 4,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => 'テストレビュー',
            'rating' => 4,
        ]);
    }

    /**
     * バリデーションエラー（rating未入力）
     */
    public function test_review_store_validation_fails_with_missing_rating(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => 'テストレビュー',
            'rating' => null,
        ]);
        $response->assertSessionHasErrors('rating');
    }

    /**
     * 評価が1で投稿できる
     */
    public function test_review_store_with_rating_of_1(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => '最低評価のレビュー',
            'rating' => 1,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => '最低評価のレビュー',
            'rating' => 1,
        ]);
    }

    /**
     * 評価が5で投稿できる
     */
    public function test_review_store_with_rating_of_5(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => '最高評価のレビュー',
            'rating' => 5,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => '最高評価のレビュー',
            'rating' => 5,
        ]);
    }

    /**
     * 評価が0でバリデーションエラーになる
     */
    public function test_review_store_validation_fails_with_rating_of_0(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => '評価が0のレビュー',
            'rating' => 0,
        ]);
        $response->assertSessionHasErrors('rating');
    }

    /**
     * 評価が6でバリデーションエラーになる
     */
    public function test_review_store_validation_fails_with_rating_of_6(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => '評価が6のレビュー',
            'rating' => 6,
        ]);
        $response->assertSessionHasErrors('rating');
    }

    /**
     * バリデーションエラー（comment未入力）
     */
    public function test_review_store_validation_fails_with_missing_comment(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => '',
            'rating' => 4,
        ]);
        $response->assertSessionHasErrors('comment');
    }

    /**
     * コメントが1000文字で投稿できる
     */
    public function test_review_store_with_comment_of_1000_characters(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => str_repeat('a', 1000),
            'rating' => 4,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => str_repeat('a', 1000),
            'rating' => 4,
        ]);
    }

    /**
     * コメントが1001文字でバリデーションエラーになる
     */
    public function test_review_store_validation_fails_with_comment_of_1001_characters(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => str_repeat('a', 1001),
            'rating' => 4,
        ]);
        $response->assertSessionHasErrors('comment');
    }

    /**
     * 認証済みでレビュー編集画面にアクセスできる（本人投稿）
     */
    public function test_authenticated_user_can_access_review_edit_page(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('reviews.edit', ['review' => $review->id]));
        $response->assertStatus(200);
    }

    /**
     * 他人のレビュー編集画面にアクセスできない（403）
     */
    public function test_authenticated_user_cannot_access_review_edit_page_of_others(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->get(route('reviews.edit', ['review' => $review->id]));
        $response->assertStatus(403);
    }

    /**
     * 投稿者本人はレビューを更新できる
     */
    public function test_authenticated_user_can_update_own_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'comment' => '古いレビュー',
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', ['review' => $review->id]), [
            'comment' => '新しいレビュー',
            'rating' => 5,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => '新しいレビュー',
            'rating' => 5,
        ]);
    }

    /**
     * 他人のレビューは更新できない（403）
     */
    public function test_authenticated_user_cannot_update_others_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $otherUser->id,
            'comment' => '他人のレビュー',
            'rating' => 2,
        ]);

        $response = $this->actingAs($user)->put(route('reviews.update', ['review' => $review->id]), [
            'comment' => '更新しようとするレビュー',
            'rating' => 4,
        ]);
        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => '他人のレビュー',
            'rating' => 2,
        ]);
    }

    /**
     * 投稿者本人はレビューを削除できる
     */
    public function test_authenticated_user_can_delete_own_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reviews.destroy', ['review' => $review->id]));
        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /**
     * 他人のレビューは削除できない（403）
     */
    public function test_authenticated_user_cannot_delete_others_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reviews.destroy', ['review' => $review->id]));
        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    /**
     * 同じ書籍に2回目のレビューを投稿しようとするとエラーメッセージになる
     */
    public function test_authenticated_user_cannot_create_multiple_reviews_for_same_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.store', ['book' => $book->id]), [
            'comment' => '2回目のレビュー',
            'rating' => 4,
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => $review->comment,
            'rating' => $review->rating,
        ]);
    }
}
