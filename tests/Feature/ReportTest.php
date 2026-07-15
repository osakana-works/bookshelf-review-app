<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 3-5-1: 認証済みユーザーは自分の統計情報が表示される
     */
    public function test_authenticated_user_can_view_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
    }

    /**
     * 3-5-2: 未認証ユーザーはログイン画面にリダイレクトされる
     */
    public function test_guest_is_redirected_to_login_from_report(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 3-5-3: 総レビュー数が正しく集計される
     */
    public function test_total_reviews_are_counted_correctly(): void
    {
        $user = User::factory()->create();
        Review::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 3;
        });
    }

    /**
     * 3-5-4: 読了冊数(読書計画のCompleted件数)が正しく集計される
     */
    public function test_books_read_counts_completed_reading_plans(): void
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->completed()->create(['user_id' => $user->id]);
        ReadingPlan::factory()->completed()->create(['user_id' => $user->id]);
        ReadingPlan::factory()->create(['user_id' => $user->id]); // InProgress

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['books_read'] === 2;
        });
    }

    /**
     * 3-5-5: 平均評価が正しく計算される
     */
    public function test_average_rating_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'rating' => 2]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $stats = $response->viewData('stats');
        $this->assertEquals(3.0, $stats['summary']['average_rating']);
    }

    /**
     * 3-5-6: レビューが0件の場合、平均評価が0になる
     */
    public function test_average_rating_is_zero_when_no_reviews(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['average_rating'] === 0;
        });
    }

    /**
     * 3-5-7: 評価分布が正しく集計される
     */
    public function test_rating_distribution_is_counted_correctly(): void
    {
        $user = User::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'rating' => 3]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution'][2] === 1  // ★3が1件
                && $stats['rating_distribution'][4] === 2; // ★5が2件
        });
    }

    /**
     * 3-5-8: 高評価書籍TOP5が、評価順・4星以上のみで表示される
     */
    public function test_top_rated_books_only_include_four_stars_or_more(): void
    {
        $user = User::factory()->create();
        $bookLow = Book::factory()->create();
        $bookHigh = Book::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookLow->id, 'rating' => 3]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookHigh->id, 'rating' => 5]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) use ($bookHigh) {
            $topRatedIds = collect($stats['top_rated_books'])->pluck('id')->toArray();

            return count($stats['top_rated_books']) === 1
                && in_array($bookHigh->id, $topRatedIds);
        });
    }

    /**
     * 3-5-9: ジャンル別評価傾向TOP5が、平均評価順で表示される
     */
    public function test_genre_ratings_are_sorted_by_average_rating(): void
    {
        $user = User::factory()->create();
        $genreHigh = Genre::factory()->create();
        $genreLow = Genre::factory()->create();
        $bookHigh = Book::factory()->create();
        $bookHigh->genres()->attach($genreHigh->id);
        $bookLow = Book::factory()->create();
        $bookLow->genres()->attach($genreLow->id);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookHigh->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookLow->id, 'rating' => 2]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) use ($genreHigh) {
            return $stats['genre_ratings']->first()['id'] === $genreHigh->id;
        });
    }
}
