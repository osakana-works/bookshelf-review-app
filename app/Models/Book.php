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
     * キャストするべき属性
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_date' => 'date',
        ];
    }

    /**
     * キーワードで検索するスコープ
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
     * ジャンルで絞り込むスコープ
     */
    public function scopeFilterByGenre(Builder $query, ?int $genreId): Builder
    {
        return $query->when($genreId, function ($q) use ($genreId) {
            $q->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        });
    }
}
