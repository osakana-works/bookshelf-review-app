<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未認証でジャンル一覧にアクセスするとリダイレクトされる
     */
    public function test_guest_cannot_access_genre_index(): void
    {
        $response = $this->get(route('genres.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みでジャンル一覧が表示される
     */
    public function test_authenticated_user_can_access_genre_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.index'));
        $response->assertStatus(200);
    }

    /**
     * 認証済みでジャンル作成画面が表示される
     */
    public function test_authenticated_user_can_access_genre_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.create'));
        $response->assertStatus(200);
    }

    /**
     * 認証済みでジャンルが作成できる
     */
    public function test_authenticated_user_can_create_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'テストジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'name' => 'テストジャンル',
        ]);
    }

    /**
     * ジャンル登録時のバリデーションエラー（名前未入力）
     */
    public function test_genre_store_validation_fails_with_missing_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * ジャンル名が255文字で登録できる
     */
    public function test_genre_store_with_name_of_255_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => str_repeat('a', 255),
        ]);
        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'name' => str_repeat('a', 255),
        ]);
    }

    /**
     * ジャンル名が256文字でバリデーションエラーになる
     */
    public function test_genre_store_validation_fails_with_name_of_256_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => str_repeat('a', 256),
        ]);
        $response->assertSessionHasErrors('name');
    }

    /**
     * ジャンル名の重複登録ができない（一意性チェック）
     */
    public function test_genre_store_validation_fails_with_duplicate_name(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => '重複ジャンル']);

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => '重複ジャンル',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * 認証済みでジャンル詳細が表示される
     */
    public function test_authenticated_user_can_access_genre_show_page(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.show', ['genre' => $genre->id]));
        $response->assertStatus(200);
    }

    /**
     * 未認証でジャンル詳細にアクセスするとリダイレクトされる
     */
    public function test_guest_cannot_access_genre_show_page(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.show', ['genre' => $genre->id]));
        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みでジャンル編集画面が表示される
     */
    public function test_authenticated_user_can_access_genre_edit_page(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.edit', ['genre' => $genre->id]));
        $response->assertStatus(200);
    }

    /**
     * 未認証でジャンル編集画面にアクセスするとリダイレクトされる
     */
    public function test_guest_cannot_access_genre_edit_page(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.edit', ['genre' => $genre->id]));
        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みでジャンルが更新できる
     */
    public function test_authenticated_user_can_update_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '旧ジャンル']);

        $response = $this->actingAs($user)->put(route('genres.update', ['genre' => $genre->id]), [
            'name' => '新ジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '新ジャンル',
        ]);
    }

    /**
     * 書籍が紐づいていないジャンルは削除できる
     */
    public function test_genre_without_books_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete(route('genres.destroy', ['genre' => $genre->id]));

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    /**
     * 書籍が紐づいているジャンルは削除できない
     */
    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete(route('genres.destroy', ['genre' => $genre->id]));

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }

    /**
     * ジャンル名を変更せず更新するとバリデーションエラーになる
     */
    public function test_genre_update_without_changes_fails_validation(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->put(route('genres.update', ['genre' => $genre->id]), [
            'name' => $genre->name,
        ]);

        $response->assertSessionHasErrors('name');
    }
}
