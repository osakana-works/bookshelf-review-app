<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class LikeController extends Controller
{
    public function toggle(Review $review): RedirectResponse
    {
        $user = auth()->user();

        // 自分のレビューにはいいねできない
        if ($review->user_id === $user->id) {
            return redirect()->back();
        }

        if ($user->likedReviews()->where('review_id', $review->id)->exists()) {
            $user->likedReviews()->detach($review->id);
        } else {
            $user->likedReviews()->attach($review->id);
        }

        return redirect()->back();
    }
}
