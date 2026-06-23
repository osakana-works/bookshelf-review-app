<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * レビューを登録する
     *
     * @param  ReviewRequest  $request  バリデーション済みリクエスト
     * @param  Book  $book  対象の書籍
     */
    public function store(ReviewRequest $request, Book $book): RedirectResponse
    {
        $validated = $request->validated();

        if (Review::where('book_id', $book->id)->where('user_id', auth()->id())->exists()) {
            return redirect()->route('books.show', $book)->with('error', 'この書籍には既にレビューを投稿済みです。');
        }

        $review = Review::create([
            'book_id' => $book->id,
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('books.show', $book)->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集フォームを表示する
     *
     * @param  Review  $review  対象のレビュー
     * @return View
     */
    public function edit(Review $review)
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する
     *
     * @param  ReviewRequest  $request  バリデーション済みリクエスト
     * @param  Review  $review  対象のレビュー
     */
    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューを削除する
     *
     * @param  Review  $review  対象のレビュー
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return redirect()->route('books.show', $review->book)->with('success', 'レビューを削除しました。');
    }
}
