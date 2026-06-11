<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ゲストでもランキング画面にアクセスできるテスト
     */
    public function test_ranking_is_accessible_by_guest(): void
    {
        $response = $this->get('/ranking');
        $response->assertStatus(200);
    }

    /**
     * レビューがない書籍はランキングに表示されないテスト
     */
    public function test_books_without_reviews_are_not_shown_in_ranking(): void
    {
        $book = Book::factory()->create();

        $response = $this->get('/ranking');
        $response->assertStatus(200);
        $response->assertDontSee($book->title);
    }

    /**
     * レビューがある書籍はランキングに表示されるテスト
     */
    public function test_books_with_reviews_are_shown_in_ranking(): void
    {
        $book = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->get('/ranking');
        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    /**
     * ランキングは10件以上あっても10件だけ表示されるテスト
     */
    public function test_ranking_shows_only_top_10_books(): void
    {
        $topBooks = Book::factory()->count(10)->create();
        foreach ($topBooks as $book) {
            Review::factory()->create([
                'book_id' => $book->id,
                'rating' => 5,
            ]);
        }

        $lowestBook = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $lowestBook->id,
            'rating' => 1,
        ]);

        $response = $this->get('/ranking');
        $response->assertStatus(200);

        // 11番目の書籍はランキングに表示されないことを確認
        $response->assertDontSee($lowestBook->title);
    }

    /**
     * 平均評価の高い順に表示されるテスト
     */
    public function test_ranking_is_ordered_by_average_rating(): void
    {
        $highRatedBook = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        $lowRatedBook = Book::factory()->create();
        Review::factory()->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 1,
        ]);

        $response = $this->get('/ranking');
        $response->assertStatus(200);

        // 高評価の書籍が先に表示されることを確認
        $response->assertSeeInOrder([
            $highRatedBook->title,
            $lowRatedBook->title,
        ]);
    }
}
