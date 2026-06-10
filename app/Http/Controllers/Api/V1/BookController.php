<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得する
     */
    public function index(): AnonymousResourceCollection
    {
        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->paginate(20);

        return BookResource::collection($books);
    }

    /**
     * 書籍を新規登録する
     */
    public function store(BookRequest $request): JsonResource
    {
        $book = Book::create($request->validated());
        $book->genres()->sync($request->input('genres', []));
        $book->load('genres');

        return new BookResource($book);
    }

    /**
     * 書籍詳細を取得する
     */
    public function show(Book $book): JsonResource
    {
        $book->load(['genres', 'reviews.user']);
        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * 書籍を更新する
     */
    public function update(BookRequest $request, Book $book): JsonResource
    {
        $book->update($request->validated());
        $book->genres()->sync($request->input('genres', []));
        $book->load('genres');

        return new BookResource($book);
    }

    /**
     * 書籍を削除する
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(null, 204);
    }
}
