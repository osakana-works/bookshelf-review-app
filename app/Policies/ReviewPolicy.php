<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * レビューを更新できるかどうかを判定する
     *
     * @param  User  $user  認証中のユーザー
     * @param  Review  $review  対象のレビュー
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * レビューを削除できるかどうかを判定する
     *
     * @param  User  $user  認証中のユーザー
     * @param  Review  $review  対象のレビュー
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}
