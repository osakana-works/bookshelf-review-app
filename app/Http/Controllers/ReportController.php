<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): View
    {
        $reviews = auth()->user()->reviews()->with('book.genres')->get();
        $readingPlans = auth()->user()->readingPlans()->get();

        $stats = [
            'summary' => [
                'books_read' => $readingPlans->where('status', ReadingPlanStatus::Completed)->count(),
                'total_reviews' => $reviews->count(),
                'average_rating' => $reviews->avg('rating') ?? 0,
            ],

            'rating_distribution' => collect([
                0 => $reviews->where('rating', 1)->count(),  // ★1の件数
                1 => $reviews->where('rating', 2)->count(),  // ★2の件数
                2 => $reviews->where('rating', 3)->count(),  // ★3の件数
                3 => $reviews->where('rating', 4)->count(),  // ★4の件数
                4 => $reviews->where('rating', 5)->count(),  // ★5の件数
            ]),

            'top_rated_books' => $reviews
                ->where('rating', '>=', 4)
                ->sortByDesc('rating')
                ->take(5)
                ->map(function ($review) {
                    return [
                        'id' => $review->book->id,
                        'title' => $review->book->title,
                        'author' => $review->book->author,
                        'rating' => $review->rating,
                    ];
                })
                ->values(),

            'genre_ratings' => $reviews
                ->flatMap(function ($review) {
                    return $review->book->genres->map(fn ($genre) => [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'rating' => $review->rating,
                    ]);
                })
                ->groupBy('id')
                ->map(fn ($group) => [
                    'id' => $group->first()['id'],
                    'name' => $group->first()['name'],
                    'count' => $group->count(),
                    'average_rating' => $group->avg('rating'),
                ])
                ->sortByDesc('average_rating')
                ->take(5)
                ->values(),
        ];

        return view('reports.index', compact('stats'));
    }
}
