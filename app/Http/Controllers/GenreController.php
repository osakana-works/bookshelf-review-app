<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録フォームを表示する
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * ジャンルを登録する
     *
     * @param  GenreRequest  $request  バリデーション済みリクエスト
     */
    public function store(GenreRequest $request): RedirectResponse
    {
        Genre::create($request->validated());

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを登録しました。');
    }

    /**
     * ジャンル詳細を表示する
     *
     * @param  Genre  $genre  ジャンルモデル
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル編集フォームを表示する
     *
     * @param  Genre  $genre  ジャンルモデル
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンルを更新する
     *
     * @param  GenreRequest  $request  バリデーション済みリクエスト
     * @param  Genre  $genre  ジャンルモデル
     */
    public function update(GenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを更新しました。');
    }

    /**
     * ジャンルを削除する
     *
     * @param  Genre  $genre  ジャンルモデル
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return redirect()
                ->route('genres.index')
                ->with('error', 'このジャンルには書籍が紐づいているため削除できません。');
        }

        $genre->delete();

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}
