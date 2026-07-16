<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する
     */
    public function index(Request $request): View
    {
        $books = Book::with(['genres', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->searchByKeyword($request->keyword)
            ->filterByGenre($request->genre)
            ->sortBy($request->sort)
            ->paginate(10);

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録フォームを表示する
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を登録する
     */
    public function store(BookRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $book = Book::create([
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
                'user_id' => auth()->id(),
            ]);

            $book->genres()->sync($request->input('genres', []));
        });

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍詳細を表示する
     */
    public function show(Book $book): View
    {
        $book->load(['genres', 'reviews.user', 'reviews.likedByUsers']);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集フォームを表示する
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍を更新する
     */
    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        DB::transaction(function () use ($request, $book) {
            $book->update([
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
            ]);

            $book->genres()->sync($request->input('genres', []));
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    /**
     * 書籍を削除する
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    /**
     * ISBNからGoogleBooksの書籍情報を取得する
     */
    public function fetchByIsbn(string $isbn): JsonResponse
    {
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => "isbn:{$isbn}",
            'key' => config('services.google_books.key'),
        ]);

        $data = $response->json();

        if (empty($data['items'])) {
            return response()->json(['error' => '該当する書籍が見つかりませんでした。']);
        }

        $volumeInfo = $data['items'][0]['volumeInfo'];

        return response()->json([
            'title' => $volumeInfo['title'] ?? '',
            'author' => implode(', ', $volumeInfo['authors'] ?? []),
            'description' => $volumeInfo['description'] ?? '',
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
            'published_date' => $this->formatPublishedDate($volumeInfo['publishedDate'] ?? null),
        ]);
    }

    /**
     * Google Books APIのpublishedDateを補完してY-m-d形式に整形する
     */
    private function formatPublishedDate(?string $publishedDate): ?string
    {
        if (! $publishedDate) {
            return null;
        }

        return match (strlen($publishedDate)) {
            4 => $publishedDate.'-01-01',      // "2026" → "2026-01-01"
            7 => $publishedDate.'-01',          // "2026-03" → "2026-03-01"
            default => $publishedDate,          // "2026-03-15" のような完全な形式はそのまま
        };
    }
}
