<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Like;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザーが複数の書籍を持つリレーションのテスト
     */
    public function test_user_has_many_books(): void
    {
        $user = new User;
        $this->assertInstanceOf(HasMany::class, $user->books());
    }

    /**
     * ユーザーが複数のレビューを持つリレーションのテスト
     */
    public function test_user_has_many_reviews(): void
    {
        $user = new User;
        $this->assertInstanceOf(HasMany::class, $user->reviews());
    }

    /**
     * ユーザーが複数のお気に入り書籍を持つリレーションのテスト
     */
    public function test_user_belongs_to_many_favorite_books(): void
    {
        $user = new User;
        $this->assertInstanceOf(BelongsToMany::class, $user->favoriteBooks());
    }

    /**
     * ユーザーが複数のいいねしたレビューを持つリレーションのテスト
     */
    public function test_user_belongs_to_many_liked_reviews(): void
    {
        $user = new User;
        $this->assertInstanceOf(BelongsToMany::class, $user->likedReviews());
    }

    /**
     * 書籍がユーザーに属するリレーションのテスト
     */
    public function test_book_belongs_to_user(): void
    {
        $book = new Book;
        $this->assertInstanceOf(BelongsTo::class, $book->user());
    }

    /**
     * 書籍が複数のレビューを持つリレーションのテスト
     */
    public function test_book_has_many_reviews(): void
    {
        $book = new Book;
        $this->assertInstanceOf(HasMany::class, $book->reviews());
    }

    /**
     * 書籍が複数のジャンルに属するリレーションのテスト
     */
    public function test_book_belongs_to_many_genres(): void
    {
        $book = new Book;
        $this->assertInstanceOf(BelongsToMany::class, $book->genres());
    }

    /**
     * 書籍が複数のユーザーにお気に入り登録されるリレーションのテスト
     */
    public function test_book_belongs_to_many_favorited_by(): void
    {
        $book = new Book;
        $this->assertInstanceOf(BelongsToMany::class, $book->favoritedBy());
    }

    /**
     * レビューがユーザーに属するリレーションのテスト
     */
    public function test_review_belongs_to_user(): void
    {
        $review = new Review;
        $this->assertInstanceOf(BelongsTo::class, $review->user());
    }

    /**
     * レビューが書籍に属するリレーションのテスト
     */
    public function test_review_belongs_to_book(): void
    {
        $review = new Review;
        $this->assertInstanceOf(BelongsTo::class, $review->book());
    }

    /**
     * レビューが複数のユーザーにいいねされるリレーションのテスト
     */
    public function test_review_belongs_to_many_liked_by_users(): void
    {
        $review = new Review;
        $this->assertInstanceOf(BelongsToMany::class, $review->likedByUsers());
    }

    /**
     * ジャンルが複数の書籍に属するリレーションのテスト
     */
    public function test_genre_belongs_to_many_books(): void
    {
        $genre = new Genre;
        $this->assertInstanceOf(BelongsToMany::class, $genre->books());
    }

    // Favoriteモデル
    /**
     * お気に入りがユーザーに属するリレーションのテスト
     */
    public function test_favorite_belongs_to_user(): void
    {
        $favorite = new Favorite;
        $this->assertInstanceOf(BelongsTo::class, $favorite->user());
    }

    /**
     * お気に入りが書籍に属するリレーションのテスト
     */
    public function test_favorite_belongs_to_book(): void
    {
        $favorite = new Favorite;
        $this->assertInstanceOf(BelongsTo::class, $favorite->book());
    }

    // Likeモデル
    /**
     * いいねがユーザーに属するリレーションのテスト
     */
    public function test_like_belongs_to_user(): void
    {
        $like = new Like;
        $this->assertInstanceOf(BelongsTo::class, $like->user());
    }

    /**
     * いいねがレビューに属するリレーションのテスト
     */
    public function test_like_belongs_to_review(): void
    {
        $like = new Like;
        $this->assertInstanceOf(BelongsTo::class, $like->review());
    }
}
