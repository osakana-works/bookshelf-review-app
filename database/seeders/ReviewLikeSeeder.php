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
        $reviews = Review::all();
        $users = User::all();

        foreach ($reviews as $review) {

            $candidateUsers = $users->reject(fn ($user) => $user->id === $review->user_id);

            $likeCount = rand(0, 3);
            $likedUsers = $candidateUsers->random(min($likeCount, $candidateUsers->count()));

            $review->likedByUsers()->syncWithoutDetaching(
                $likedUsers->pluck('id')->toArray()
            );
        }
    }
}
