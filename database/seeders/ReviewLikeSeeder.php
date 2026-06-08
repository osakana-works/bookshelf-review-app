<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * いいねの初期データを投入する
     */
    public function run(): void
    {
        $users = User::all();

        Review::all()->each(function ($review) use ($users) {
            $likeUsers = $users
                ->where('id', '!=', $review->user_id)
                ->random(rand(0, min(3, $users->count() - 1)))
                ->pluck('id');

            $review->likes()->syncWithoutDetaching($likeUsers);
        });
    }
}
