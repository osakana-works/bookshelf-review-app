<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;

class FavoriteSeeder extends Seeder
{
    /**
     * お気に入りの初期データを投入する
     */
    public function run(): void
    {
        $books = Book::all();

        $favorites = [
            'yamada@example.com'    => [1, 2, 5, 8],
            'suzuki@example.com'    => [3, 7, 9, 10, 11],
            'tanaka@example.com'    => [2, 4, 6, 9],
            'sato@example.com'      => [1, 5, 8, 10, 11],
            'takahashi@example.com' => [3, 6, 7],
        ];

        foreach ($favorites as $email => $bookIndexes) {
            $user = User::where('email', $email)->first();
            $bookIds = $books->whereIn('id', $bookIndexes)->pluck('id');
            $user->favorites()->syncWithoutDetaching($bookIds);
        }
    }
}
