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

    protected $fillable = ['title', 'author', 'isbn', 'published_date', 'description', 'image_url', 'user_id'];

    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     * 本を登録したユーザーを取得する
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 本に紐づくレビューの一覧を取得する
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 本に紐づくジャンルの一覧を取得する
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre')->withTimestamps();
    }

    /**
     * 本をお気に入り登録したユーザーの一覧を取得する
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'book_user')->withTimestamps();
    }

    /**
     * タイトルまたは著者名でキーワード検索するスコープ
     *
     * @param  Builder  $query  クエリビルダ
     * @param  string|null  $keyword  検索キーワード
     * @return Builder 絞り込まれたクエリビルダ
     */
    public function scopeSearchByKeyword(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, function ($q) use ($keyword) {
            $q->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        });
    }

    /**
     * ジャンルIDで絞り込むスコープ
     *
     * @param  Builder  $query  クエリビルダ
     * @param  int|null  $genreId  ジャンルID
     * @return Builder 絞り込まれたクエリビルダ
     */
    public function scopeFilterByGenre(Builder $query, ?int $genreId): Builder
    {
        return $query->when($genreId, function ($q) use ($genreId) {
            $q->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        });
    }

    /**
     * ソート順で並び替えるスコープ
     *
     * @param  Builder  $query  クエリビルダ
     * @param  string|null  $sort  ソート順（newest/oldest/title/rating）
     * @return Builder ソートされたクエリビルダ
     */
    public function scopeSortBy(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'title' => $query->orderBy('title'),
            'rating' => $query->withAvg('reviews', 'rating')
                ->orderByDesc('reviews_avg_rating')
                ->orderByDesc('id'),
            default => $query->latest()
        };
    }
}
