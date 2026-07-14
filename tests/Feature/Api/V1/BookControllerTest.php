<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================
    // 公開API（一覧）
    // =========================================

    /**
     * 2-9-1: 書籍一覧を取得すると200とJSON構造（BookResource）が返る
     */
    public function test_index_returns_200_with_book_resource_structure(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
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
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'per_page', 'total'],
            ]);
    }

    /**
     * 2-9-2: 各書籍にgenres・reviews_avg_rating・reviews_countが含まれる（reviewsの中身は含まれない）
     */
    public function test_index_includes_genres_avg_rating_and_count_but_not_reviews(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 5]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $genre->id,
                'name' => $genre->name,
            ]);

        $data = $response->json('data.0');
        $this->assertArrayHasKey('genres', $data);
        $this->assertArrayHasKey('reviews_avg_rating', $data);
        $this->assertArrayHasKey('reviews_count', $data);
        $this->assertArrayNotHasKey('reviews', $data);
    }

    /**
     * 2-9-3: keywordパラメータでタイトル部分一致検索ができる
     */
    public function test_index_filters_by_keyword_in_title(): void
    {
        Book::factory()->create(['title' => '吾輩は猫である']);
        Book::factory()->create(['title' => '別の本']);

        $response = $this->getJson('/api/v1/books?keyword=猫');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => '吾輩は猫である']);
    }

    /**
     * 2-9-4: keywordパラメータで著者名部分一致検索ができる
     */
    public function test_index_filters_by_keyword_in_author(): void
    {
        Book::factory()->create(['author' => '夏目漱石']);
        Book::factory()->create(['author' => '別の著者']);

        $response = $this->getJson('/api/v1/books?keyword=漱石');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['author' => '夏目漱石']);
    }

    /**
     * 2-9-5: genre_idパラメータでジャンル絞り込みができる
     */
    public function test_index_filters_by_genre_id(): void
    {
        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();
        $book1 = Book::factory()->create();
        $book1->genres()->attach($genre1->id);
        $book2 = Book::factory()->create();
        $book2->genres()->attach($genre2->id);

        $response = $this->getJson("/api/v1/books?genre_id={$genre1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $book1->id]);
    }

    /**
     * 2-9-6: per_pageパラメータでページネーション件数が変わる
     */
    public function test_index_respects_per_page_parameter(): void
    {
        Book::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/books?per_page=2');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    /**
     * 2-9-7: per_pageの最大値（100）を超えるとバリデーションエラーになる
     */
    public function test_index_validation_fails_when_per_page_exceeds_max(): void
    {
        $response = $this->getJson('/api/v1/books?per_page=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    /**
     * 2-9-8: レビューが0件の書籍はreviews_avg_ratingがnull、reviews_countが0になる
     */
    public function test_index_book_without_reviews_has_null_avg_and_zero_count(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $book->id,
                'reviews_avg_rating' => null,
                'reviews_count' => 0,
            ]);
    }

    /**
     * 2-9-9: 存在するIDで書籍詳細を取得すると200とdata（genres・reviews含む）が返る
     */
    public function test_show_returns_200_with_genres_and_reviews(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);
        Review::factory()->create(['book_id' => $book->id]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
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
                    'reviews',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /**
     * 2-9-10: reviewsにuser_name・rating・comment・created_atが含まれる
     */
    public function test_show_reviews_include_user_name_rating_comment_created_at(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create(['name' => '山田太郎']);
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => '面白かった',
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'reviews' => [
                        '*' => ['id', 'user_name', 'rating', 'comment', 'created_at'],
                    ],
                ],
            ])
            ->assertJsonFragment([
                'user_name' => '山田太郎',
                'rating' => 5,
                'comment' => '面白かった',
            ]);
    }

    /**
     * 2-9-11: 存在しないIDを指定すると404と日本語エラーメッセージが返る（詳細）
     */
    public function test_show_returns_404_for_nonexistent_id(): void
    {
        $response = $this->getJson('/api/v1/books/9999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'リソースが見つかりませんでした。']);
    }

    /**
     * 2-9-12: 必須項目を全て満たして登録すると201とメッセージが返る
     */
    public function test_store_returns_201_with_message(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => '書籍を登録しました。']);
    }

    /**
     * 2-9-13: 登録した書籍がDBに保存されている
     */
    public function test_store_saves_book_to_database(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'isbn' => '9784000000001',
            'user_id' => $user->id,
        ]);
    }

    /**
     * 2-9-14: 登録した書籍とジャンルの紐付け（book_genre）が保存されている
     */
    public function test_store_saves_genre_relation(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $book = Book::where('isbn', '9784000000001')->first();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /**
     * 2-9-15: titleが未入力だと422と日本語エラーメッセージが返る
     */
    public function test_store_validation_fails_with_missing_title(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => '',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title')
            ->assertJsonFragment(['title' => ['タイトルは必須です。']]);
    }

    /**
     * 2-9-16: authorが未入力だと422と日本語エラーメッセージが返る
     */
    public function test_store_validation_fails_with_missing_author(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => '',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author')
            ->assertJsonFragment(['author' => ['著者名は必須です。']]);
    }

    /**
     * 2-9-18: isbnが13桁でないと422になる
     */
    public function test_store_validation_fails_with_invalid_isbn_length(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '123456789012', // 12桁
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('isbn');
    }

    /**
     * 2-9-19: isbnが既存の値と重複すると422になる
     */
    public function test_store_validation_fails_with_duplicate_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);
        $existingBook = Book::factory()->create(['isbn' => '9784000000001']);

        $response = $this->postJson('/api/v1/books', [
            'title' => '別の書籍',
            'author' => '別の著者',
            'isbn' => $existingBook->isbn,
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('isbn');
    }

    /**
     * 2-9-21: published_dateが日付形式でないと422になる
     */
    public function test_store_validation_fails_with_invalid_date_format(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => 'not-a-date',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('published_date');
    }

    /**
     * 2-9-23: genresが未指定（空配列）だと422になる
     */
    public function test_store_validation_fails_with_empty_genres(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('genres');
    }

    /**
     * 2-9-24: genresに存在しないジャンルIDが含まれると422になる
     */
    public function test_store_validation_fails_with_nonexistent_genre_id(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [9999],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('genres.0');
    }

    /**
     * 2-9-25: 書籍を更新すると200とメッセージが返る
     */
    public function test_update_returns_200_with_message(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => '書籍を更新しました。']);
    }

    /**
     * 2-9-26: 更新内容がDBに反映されている
     */
    public function test_update_reflects_changes_in_database(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genre->id],
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
        ]);
    }

    /**
     * 2-9-27: 更新時にジャンルの紐付け（book_genre）が同期される
     */
    public function test_update_syncs_genre_relation(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $oldGenre = Genre::factory()->create();
        $newGenre = Genre::factory()->create();
        $book->genres()->attach($oldGenre->id);
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/books/{$book->id}", [
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$newGenre->id],
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);
    }

    /**
     * 2-9-28: 存在しないIDを指定すると404が返る（更新）
     */
    public function test_update_returns_404_for_nonexistent_id(): void
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/books/9999', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'リソースが見つかりませんでした。']);
    }

    /**
     * 2-9-29: 自分自身のISBNのまま更新すると一意性エラーにならない
     */
    public function test_update_with_own_isbn_does_not_trigger_validation_error(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id, 'isbn' => '9784000000001']);
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(200);
    }

    /**
     * 2-9-30: 他の書籍のISBNと重複すると422になる
     */
    public function test_update_validation_fails_with_other_books_isbn(): void
    {
        $user = User::factory()->create();
        $otherBook = Book::factory()->create(['isbn' => '9784000000002']);
        $book = Book::factory()->create(['user_id' => $user->id, 'isbn' => '9784000000001']);
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $otherBook->isbn,
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('isbn');
    }

    /**
     * 2-9-31: バリデーションエラー時は登録時と同様の日本語エラーが返る（更新）
     */
    public function test_update_validation_fails_with_missing_title(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['title' => ['タイトルは必須です。']]);
    }

    /**
     * 2-9-32: 書籍を削除すると204が返る
     */
    public function test_destroy_returns_204(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
    }

    /**
     * 2-9-33: 削除した書籍がDBから消えている
     */
    public function test_destroy_removes_book_from_database(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/books/{$book->id}");

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /**
     * 2-9-34: 削除時に紐づくレビューもCASCADEで削除される
     */
    public function test_destroy_cascades_to_reviews(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $this->deleteJson("/api/v1/books/{$book->id}");

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /**
     * 2-9-35: 削除時に紐づくお気に入りもCASCADEで削除される
     */
    public function test_destroy_cascades_to_favorites(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);
        $user->favoriteBooks()->attach($book->id);

        $this->deleteJson("/api/v1/books/{$book->id}");

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * 2-9-36: 削除時に紐づくジャンル紐付け（book_genre）もCASCADEで削除される
     */
    public function test_destroy_cascades_to_book_genre(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        $this->deleteJson("/api/v1/books/{$book->id}");

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /**
     * 2-9-37: 存在しないIDを指定すると404が返る（削除）
     */
    public function test_destroy_returns_404_for_nonexistent_id(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/v1/books/9999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'リソースが見つかりませんでした。']);
    }

    // =========================================
    // Sanctum認証・nullable化の確認
    // =========================================

    /**
     * 3-6-13: isbnが未入力でも登録できる（nullable化の確認）
     */
    public function test_store_succeeds_without_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books/', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'isbn' => null,
        ]);
    }

    /**
     * 3-6-14: published_dateが未入力でも登録できる（nullable化の確認）
     */
    public function test_store_succeeds_without_published_date(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/books/', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'published_date' => null,
        ]);
    }
}
