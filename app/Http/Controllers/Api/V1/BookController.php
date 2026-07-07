<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookRequest;
use App\Http\Requests\Api\V1\BookSearchRequest;
use App\Http\Resources\Api\V1\BookDetailResource;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    /**
     * 書籍一覧API
     */
    public function index(BookSearchRequest $request): AnonymousResourceCollection
    {
        $books = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->searchByKeyword($request->input('keyword'))
            ->filterByGenre($request->input('genre_id'))
            ->paginate($request->per_page ?? 20);

        return BookResource::collection($books);
    }

    /**
     * 書籍登録API
     */
    public function store(BookRequest $request): JsonResponse
    {
        $book = Book::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);
        $book->genres()->sync($request->genres);

        return response()->json([
            'message' => '書籍を登録しました。',
        ], 201);
    }

    /**
     * 書籍詳細API
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load('genres', 'reviews.user')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookDetailResource($book);
    }

    /**
     * 書籍更新API
     */
    public function update(BookRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $book->update($request->validated());
        $book->genres()->sync($request->genres);

        return response()->json([
            'message' => '書籍を更新しました。',
        ], 200);
    }

    /**
     * 書籍削除API
     */
    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);
        $book->delete();

        return response()->json(null, 204);
    }
}
