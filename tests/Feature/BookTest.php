<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みで書籍登録画面にアクセスできるテスト
     */
    public function test_authenticated_user_can_access_book_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/books/create');
        $response->assertStatus(200);
    }

    /**
     * 書籍が登録できるテスト
     */
    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'description' => 'テスト説明',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'user_id' => $user->id,
        ]);
    }

    /**
     * 書籍登録時のバリデーションエラー（タイトル未入力）のテスト
     */
    public function test_book_store_validation_fails_with_missing_title(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => '',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('title');
    }

    /**
     * 書籍がタイトルが255文字で登録できるテスト
     */
    public function test_book_store_validation_passes_with_max_length_title(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => str_repeat('a', 255),
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'title' => str_repeat('a', 255),
            'user_id' => $user->id,
        ]);
    }

    /**
     * 書籍がタイトル256文字でバリデーションエラーになるテスト
     */
    public function test_book_store_validation_fails_with_too_long_title(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => str_repeat('a', 256),
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('title');
    }

    /**
     * 著者名が未入力でバリデーションエラーになる
     */
    public function test_book_store_validation_fails_with_missing_author(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => '',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('author');
    }

    /**
     * 著者が255文字で登録できるテスト
     */
    public function test_book_store_validation_passes_with_max_length_author(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => str_repeat('a', 255),
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'author' => str_repeat('a', 255),
            'user_id' => $user->id,
        ]);
    }

    /**
     * 著者が256文字でバリデーションエラーになるテスト
     */
    public function test_book_store_validation_fails_with_too_long_author(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => str_repeat('a', 256),
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('author');
    }

    /**
     * ISBNが未入力でバリデーションエラーになるテスト
     */
    public function test_book_store_validation_fails_with_missing_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    /**
     * ISBNが13桁で登録できるテスト
     */
    public function test_book_store_validation_passes_with_valid_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'isbn' => '9784000000001',
            'user_id' => $user->id,
        ]);
    }

    /**
     * IsBNが12桁でバリデーションエラーになるテスト
     */
    public function test_book_store_validation_fails_with_invalid_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '978400000000',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    /**
     * ISBNが14桁でバリデーションエラーになるテスト
     */
    public function test_book_store_validation_fails_with_too_long_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '97840000000012',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    /**
     * ジャンル未選択でバリデーションエラーになるテスト
     */
    public function test_book_store_validation_fails_without_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors('genres');
    }

    /**
     * 同じISBNは登録できない（一意性チェック）テスト
     */
    public function test_book_store_validation_fails_with_duplicate_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $existingBook = Book::factory()->create(['isbn' => '9784000000001']);

        $response = $this->actingAs($user)->post('/books', [
            'title' => '別の書籍',
            'author' => '別の著者',
            'isbn' => $existingBook->isbn,
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    /**
     * 出版日が未入力でバリデーションエラーになる
     */
    public function test_book_store_validation_fails_with_missing_published_date(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('published_date');
    }

    /**
     * 画像URLが255文字で登録できるテスト
     */
    public function test_book_store_validation_passes_with_max_length_image_url(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $baseUrl = 'https://example.com/';
        $imageUrl = $baseUrl.str_repeat('a', 255 - strlen($baseUrl));

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'image_url' => $imageUrl,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'image_url' => $imageUrl,
            'user_id' => $user->id,
        ]);
    }

    /**
     * 画像URLが256文字でバリデーションエラーになるテスト
     */
    public function test_book_store_validation_fails_with_too_long_image_url(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $baseUrl = 'https://example.com/';
        $imageUrl = $baseUrl.str_repeat('a', 256 - strlen($baseUrl));

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
            'image_url' => $imageUrl,
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('image_url');
    }

    /**
     * 書籍所有者は編集画面にアクセスできるテスト
     */
    public function test_book_owner_can_access_edit(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/books/{$book->id}/edit");
        $response->assertStatus(200);
    }

    /**
     * 他人の書籍編集画面にアクセスできないテスト
     */
    public function test_non_owner_cannot_access_book_edit(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->get("/books/{$book->id}/edit");
        $response->assertStatus(403);
    }

    /**
     * 書籍所有者は書籍を更新できるテスト
     */
    public function test_book_owner_can_update_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put("/books/{$book->id}", [
            'title' => '更新タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新タイトル',
        ]);
    }

    /**
     * 他人の書籍は更新できないテスト
     */
    public function test_non_owner_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($other)->put("/books/{$book->id}", [
            'title' => '更新タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);
    }

    /**
     * 書籍所有者は書籍を削除できるテスト
     */
    public function test_book_owner_can_delete_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /**
     * 他人の書籍は削除できないテスト
     */
    public function test_non_owner_cannot_delete_book(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->delete("/books/{$book->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }
}
