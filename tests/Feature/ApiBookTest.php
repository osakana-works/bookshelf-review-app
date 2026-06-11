<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiBookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍一覧がJSON形式で取得できるテスト
     */
    public function test_can_get_book_list(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'user_id',
                    'genres',
                    'reviews_avg_rating',
                    'reviews_count',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    /**
     * 書籍詳細がJSON形式で取得できるテスト
     */
    public function test_can_get_book_detail(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'user_id',
                'genres',
                'reviews_avg_rating',
                'reviews_count',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonFragment([
            'id' => $book->id,
            'title' => $book->title,
        ]);
    }

    /**
     * 存在しない書籍にアクセスすると404が返るテスト
     */
    public function test_returns_404_for_non_existent_book(): void
    {
        $response = $this->getJson('/api/v1/books/9999');
        $response->assertStatus(404);
    }

    /**
     * 書籍が登録できるテスト
     */
    public function test_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'description' => 'テスト説明',
            'user_id' => $user->id,
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
        ]);
    }

    /**
     * バリデーションエラー時に422が返るテスト
     */
    public function test_returns_422_when_validation_fails(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors',
        ]);
    }

    /**
     * 書籍が更新できるテスト
     */
    public function test_can_update_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => '2024-01-01',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新タイトル',
        ]);
    }

    /**
     * 存在しない書籍を更新すると404が返るテスト
     */
    public function test_returns_404_when_updating_non_existent_book(): void
    {
        $user = User::factory()->create();

        $response = $this->putJson('/api/v1/books/9999', [
            'title' => '更新タイトル',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'user_id' => $user->id,
        ]);

        $response->assertStatus(404);
    }

    /**
     * 書籍が削除できるテスト
     */
    public function test_can_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /**
     * 存在しない書籍を削除すると404が返るテスト
     */
    public function test_returns_404_when_deleting_non_existent_book(): void
    {
        $response = $this->deleteJson('/api/v1/books/9999');
        $response->assertStatus(404);
    }

    /**
     * 書籍詳細にレビュー情報が含まれるテスト
     */
    public function test_book_detail_includes_reviews(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'reviews' => [
                    '*' => [
                        'id',
                        'user_name',
                        'rating',
                        'comment',
                        'created_at',
                    ],
                ],
            ],
        ]);
    }
}
