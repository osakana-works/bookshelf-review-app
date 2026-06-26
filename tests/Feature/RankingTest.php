<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ゲストでもランキング画面にアクセスできることのテスト
     */
    public function test_guest_can_access_ranking_page(): void
    {
        $response = $this->get(route('ranking.index'));
        $response->assertStatus(200);
    }

    /**
     * 認証済みユーザーでもランキング画面にアクセスできることのテスト
     */
    public function test_authenticated_user_can_access_ranking_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('ranking.index'));
        $response->assertStatus(200);
    }

    /**
     * レビューがない書籍は表示されないことのテスト
     */
    public function test_books_without_reviews_are_not_displayed_in_ranking(): void
    {
        $bookWithoutReviews = Book::factory()->create();
        $response = $this->get(route('ranking.index'));
        $response->assertDontSee($bookWithoutReviews->title);
    }

    /**
     * レビューがある書籍は表示されることを検証する
     */
    public function test_books_with_reviews_are_displayed_in_ranking(): void
    {
        $bookWithReviews = Book::factory()->create();
        $bookWithReviews->reviews()->create([
            'user_id' => $bookWithReviews->user_id,
            'comment' => 'This is a review.',
            'rating' => 5,
        ]);
        $response = $this->get(route('ranking.index'));
        $response->assertSee($bookWithReviews->title);
    }

    /**
     * 上位10件のみ表示されることを検証する
     */
    public function test_only_top_10_books_are_displayed_in_ranking(): void
    {
        // 10冊の書籍を作成し、それぞれにレビューを追加
        for ($i = 1; $i <= 10; $i++) {
            $book = Book::factory()->create(['title' => "Book $i"]);
            $book->reviews()->create([
                'user_id' => $book->user_id,
                'comment' => "Review for Book $i",
                'rating' => 5,
            ]);
        }
        // 11冊目の書籍を作成し、レビューを追加
        $book11 = Book::factory()->create(['title' => 'Book 11']);
        $book11->reviews()->create([
            'user_id' => $book11->user_id,
            'comment' => 'Review for Book 11',
            'rating' => 1,
        ]);

        $response = $this->get(route('ranking.index'));

        // 上位10冊のタイトルが表示されることを確認
        for ($i = 1; $i <= 10; $i++) {
            $response->assertSee("Book $i");
        }

        // 11冊目のタイトルは表示されないことを確認
        $response->assertDontSee('Book 11');
    }
}
