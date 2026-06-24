<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    /**
     * いいねをトグル（追加・解除）する
     *
     * @param  Review  $review  対象のレビュー
     */
    public function toggle(Review $review): RedirectResponse
    {
        $user = auth()->user();

        if ($user->likedReviews()->where('review_id', $review->id)->exists()) {

            $user->likedReviews()->detach($review->id);
        } else {

            $user->likedReviews()->attach($review->id);
        }

        return back();
    }
}
