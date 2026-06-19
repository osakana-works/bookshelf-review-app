<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = Book::all();
        $users = User::all();

        $favorites = [
            'yamada@example.com' => [1, 2, 5, 8],
            'suzuki@example.com' => [3, 7, 9, 10, 11],
            'tanaka@example.com' => [2, 4, 6, 9],
            'sato@example.com' => [1, 5, 8, 10, 11],
            'takahashi@example.com' => [3, 6, 7],
        ];

        foreach ($favorites as $email => $bookIds) {
            $user = $users->firstWhere('email', $email);
            $favoriteBookIds = $books->whereIn('id', $bookIds)->pluck('id')->toArray();

            if ($user) {
                $user->favoriteBooks()->syncWithoutDetaching($favoriteBookIds);
            }
        }
    }
}
