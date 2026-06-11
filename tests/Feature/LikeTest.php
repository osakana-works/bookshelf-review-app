<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証でいいねできないテスト
     */
    public function test_guest_cannot_toggle_like(): void
    {
        $review = Review::factory()->create();

        $response = $this->post("/reviews/{$review->id}/like");
        $response->assertRedirect('/login');
    }

    /**
     * 認証済みでいいね追加できるテスト
     */
    public function test_authenticated_user_can_add_like(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $response->assertRedirect();
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /**
     * いいねをトグルして解除できるテスト
     */
    public function test_authenticated_user_can_remove_like(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // 一度いいねを追加
        $user->likedReviews()->attach($review->id);

        // もう一度押して解除
        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $response->assertRedirect();
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /**
     * 自分のレビューにはいいねできないテスト
     */
    public function test_user_cannot_like_own_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $response->assertRedirect();
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
