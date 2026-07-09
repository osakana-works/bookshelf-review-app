<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
        'user_id',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     * 本の登録者を取得する
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 本のレビュー一覧を取得する
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 本のジャンル一覧を取得する
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre')->withTimestamps();
    }

    /**
     * 本をお気に入り登録したユーザー一覧を取得する
     */
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * キーワード検索
     *
     * @return Builder<Book>
     */
    public function scopeSearchByKeyword(Builder $query, ?string $keyword): Builder
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%'.$keyword.'%')
                    ->orWhere('author', 'like', '%'.$keyword.'%');
            });
        }

        return $query;
    }

    /**
     * ジャンルで絞り込み
     *
     * @return Builder<Book>
     */
    public function scopeFilterByGenre(Builder $query, ?int $genreId): Builder
    {
        if ($genreId) {
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('id', $genreId);
            });
        }

        return $query;
    }

    /**
     * sortで並び変え
     */
    public function scopeSortBy(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'newest' => $query->latest(),               // 新しい順
            'oldest' => $query->oldest(),               // 古い順
            'rating' => $query->orderByDesc('reviews_avg_rating'), // 評価順
            'title' => $query->orderBy('title'),       // タイトル順
            default => $query->latest(),               // デフォルト
        };
    }

    /**
     * この本に紐づく読書計画一覧を取得する
     */
    public function readingPlans(): HasMany
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
