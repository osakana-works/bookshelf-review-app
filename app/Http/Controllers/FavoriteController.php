<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧を表示する
     */
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * お気に入りをトグル（追加・解除）する
     *
     * @param  Book  $book  対象の書籍
     */
    public function toggle(Book $book): RedirectResponse
    {
        $user = auth()->user();

        if ($user->favoriteBooks()->where('book_id', $book->id)->exists()) {
            $user->favoriteBooks()->detach($book->id);

            return redirect()->back()->with('success', 'お気に入りから解除しました。');
        } else {
            $user->favoriteBooks()->attach($book->id);

            return redirect()->back()->with('success', 'お気に入りに追加しました。');
        }
    }
}
