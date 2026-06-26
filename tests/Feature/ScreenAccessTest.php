<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * トップページがゲストでもアクセスできるテスト
     */
    public function test_top_page_is_accessible_by_guest(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * 書籍一覧がゲストでもアクセスできるテスト
     */
    public function test_books_index_is_accessible_by_guest(): void
    {
        $response = $this->get('/books');
        $response->assertStatus(200);
    }

    /**
     * 書籍詳細がゲストでもアクセスできるテスト
     */
    public function test_books_show_is_accessible_by_guest(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}");
        $response->assertStatus(200);
    }

    /**
     * ランキングがゲストでもアクセスできるテスト
     */
    public function test_ranking_is_accessible_by_guest(): void
    {
        $response = $this->get('/ranking');
        $response->assertStatus(200);
    }

    /**
     * 未認証で書籍登録画面にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_is_redirected_from_books_create(): void
    {
        $response = $this->get('/books/create');
        $response->assertRedirect('/login');
    }

    /**
     * 未認証で書籍編集画面にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_is_redirected_from_books_edit(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}/edit");
        $response->assertRedirect('/login');
    }

    /**
     * 未認証でジャンル一覧にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_is_redirected_from_genres_index(): void
    {
        $response = $this->get('/genres');
        $response->assertRedirect('/login');
    }

    /**
     * 未認証でジャンル詳細にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_is_redirected_from_genres_show(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get("/genres/{$genre->id}");
        $response->assertRedirect('/login');
    }

    /**
     * 未認証でジャンル登録画面にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_is_redirected_from_genres_create(): void
    {
        $response = $this->get('/genres/create');
        $response->assertRedirect('/login');
    }

    /**
     * 未認証でジャンル編集画面にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_is_redirected_from_genres_edit(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get("/genres/{$genre->id}/edit");
        $response->assertRedirect('/login');
    }

    /**
     * 未認証でお気に入り一覧にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_is_redirected_from_favorites(): void
    {
        $response = $this->get('/favorites');
        $response->assertRedirect('/login');
    }

    /**
     * 未認証でレビュー編集画面にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_is_redirected_from_reviews_edit(): void
    {
        $review = Review::factory()->create();

        $response = $this->get("/reviews/{$review->id}/edit");
        $response->assertRedirect('/login');
    }
}
