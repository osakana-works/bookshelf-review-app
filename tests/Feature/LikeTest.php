<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証でいいねのトグルができない（リダイレクト）
     */
    public function test_guest_cannot_toggle_like(): void
    {
        $book = Book::factory()->create();
        $review = $book->reviews()->create([
            'user_id' => $book->user_id,
            'comment' => 'This is a review.',
            'rating' => 5,
        ]);
        $response = $this->post(route('reviews.like', ['review' => $review->id]));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みでいいねができる（トグル：追加）
     */
    public function test_authenticated_user_can_toggle_like(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = $book->reviews()->create([
            'user_id' => $user->id,
            'comment' => 'This is a review.',
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.like', ['review' => $review->id]));
        $response->assertRedirect();
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /**
     * 既にいいねしている場合は解除される（トグル：解除）
     */
    public function test_authenticated_user_can_toggle_like_off(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = $book->reviews()->create([
            'user_id' => $user->id,
            'comment' => 'This is a review.',
            'rating' => 5,
        ]);
        $user->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)->post(route('reviews.like', ['review' => $review->id]));
        $response->assertRedirect();
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /**
     * 自分のレビューにもいいねできる（制限なし方針の確認）
     */
    public function test_authenticated_user_can_like_own_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $review = $book->reviews()->create([
            'user_id' => $user->id,
            'comment' => 'This is a review.',
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.like', ['review' => $review->id]));
        $response->assertRedirect();
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
