<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ModelTest extends TestCase
{
    /**
     * 1-1: ユーザーは複数の書籍を持つ
     */
    public function test_user_has_many_books(): void
    {
        $user = new User;
        $this->assertInstanceOf(HasMany::class, $user->books());
    }

    /**
     * 1-2: ユーザーは複数のレビューを持つ
     */
    public function test_user_has_many_reviews(): void
    {
        $user = new User;
        $this->assertInstanceOf(HasMany::class, $user->reviews());
    }

    /**
     * 1-3: ユーザーは複数のお気に入り書籍を持つ
     */
    public function test_user_belongs_to_many_favorite_books(): void
    {
        $user = new User;
        $this->assertInstanceOf(BelongsToMany::class, $user->favoriteBooks());
    }

    /**
     * 1-4: ユーザーは複数のいいねしたレビューを持つ
     */
    public function test_user_belongs_to_many_liked_reviews(): void
    {
        $user = new User;
        $this->assertInstanceOf(BelongsToMany::class, $user->likedReviews());
    }

    /**
     * 1-5: 書籍は登録したユーザーに属する
     */
    public function test_book_belongs_to_user(): void
    {
        $book = new Book;
        $this->assertInstanceOf(BelongsTo::class, $book->user());
    }

    /**
     * 1-6: 書籍は複数のレビューを持つ
     */
    public function test_book_has_many_reviews(): void
    {
        $book = new Book;
        $this->assertInstanceOf(HasMany::class, $book->reviews());
    }

    /**
     * 1-7: 書籍は複数のジャンルに属する
     */
    public function test_book_belongs_to_many_genres(): void
    {
        $book = new Book;
        $this->assertInstanceOf(BelongsToMany::class, $book->genres());
    }

    /**
     * 1-8: 書籍は複数のユーザーにお気に入り登録される
     */
    public function test_book_belongs_to_many_favorited_by_users(): void
    {
        $book = new Book;
        $this->assertInstanceOf(BelongsToMany::class, $book->favoritedByUsers());
    }

    /**
     * 1-9: レビューは投稿したユーザーに属する
     */
    public function test_review_belongs_to_user(): void
    {
        $review = new Review;
        $this->assertInstanceOf(BelongsTo::class, $review->user());
    }

    /**
     * 1-10: レビューは対象の書籍に属する
     */
    public function test_review_belongs_to_book(): void
    {
        $review = new Review;
        $this->assertInstanceOf(BelongsTo::class, $review->book());
    }

    /**
     * 1-11: レビューは複数のユーザーにいいねされる
     */
    public function test_review_belongs_to_many_liked_by_users(): void
    {
        $review = new Review;
        $this->assertInstanceOf(BelongsToMany::class, $review->likedByUsers());
    }

    /**
     * 1-12: ジャンルは複数の書籍に属する
     */
    public function test_genre_belongs_to_many_books(): void
    {
        $genre = new Genre;
        $this->assertInstanceOf(BelongsToMany::class, $genre->books());
    }

    /**
     * 1-2-1: ユーザーは複数の読書計画を持つ
     */
    public function test_user_has_many_reading_plans(): void
    {
        $user = new User;
        $this->assertInstanceOf(HasMany::class, $user->readingPlans());
    }

    /**
     * 1-2-2: 書籍は複数の読書計画を持つ
     */
    public function test_book_has_many_reading_plans(): void
    {
        $book = new Book;
        $this->assertInstanceOf(HasMany::class, $book->readingPlans());
    }

    /**
     * 1-2-3: 読書計画は作成したユーザーに属する
     */
    public function test_reading_plan_belongs_to_user(): void
    {
        $readingPlan = new ReadingPlan;
        $this->assertInstanceOf(BelongsTo::class, $readingPlan->user());
    }

    /**
     * 1-2-4: 読書計画は対象の書籍に属する
     */
    public function test_reading_plan_belongs_to_book(): void
    {
        $readingPlan = new ReadingPlan;
        $this->assertInstanceOf(BelongsTo::class, $readingPlan->book());
    }
}
