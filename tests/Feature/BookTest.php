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
     * 書籍一覧がゲストでも表示されるテスト
     */
    public function test_book_index_is_accessible_by_guest(): void
    {
        $response = $this->get('/books');
        $response->assertStatus(200);
    }

    /**
     * 書籍詳細がゲストでも表示されるテスト
     */
    public function test_book_show_is_accessible_by_guest(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}");
        $response->assertStatus(200);
    }

    /**
     * 未認証で書籍登録画面にアクセスするとリダイレクトされるテスト
     */
    public function test_guest_cannot_access_book_create(): void
    {
        $response = $this->get('/books/create');
        $response->assertRedirect('/login');
    }

    /**
     * 認証済みで書籍登録画面が表示されるテスト
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
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'user_id' => $user->id,
        ]);
    }

    /**
     * 書籍登録時のバリデーションエラーのテスト
     */
    public function test_book_store_validation_fails_with_missing_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', [
            'title' => '',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2024-01-01',
        ]);

        $response->assertSessionHasErrors('title');
    }

    /**
     * 書籍編集画面が表示されるテスト
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
     * 書籍が更新できるテスト
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
            'description' => $book->description,
            'image_url' => $book->image_url,
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
            'description' => $book->description,
            'image_url' => $book->image_url,
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);
    }

    /**
     * 書籍が削除できるテスト
     */
    public function test_book_owner_can_delete_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        $response->assertRedirect('/books');
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
    }
}
