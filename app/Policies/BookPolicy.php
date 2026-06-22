<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * 書籍を更新できるかどうかを判定する
     *
     * @param  User  $user  認証中のユーザー
     * @param  Book  $book  対象の書籍
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * 書籍を削除できるかどうかを判定する
     *
     * @param  User  $user  認証中のユーザー
     * @param  Book  $book  対象の書籍
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }
}
