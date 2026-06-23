<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// 公開ルート（ゲストもアクセス可能）
Route::get('/', [BookController::class, 'index']);
Route::get('/books', [BookController::class, 'index'])->name('books.index');

// 認証が必要なルート
Route::middleware('auth')->group(function () {
    Route::resource('genres', GenreController::class);

    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');

    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
});

Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// 仮ルート
Route::get('/aaa', function () {
    return response()->json(['message' => 'ranking.index まだ未実装']);
})->name('ranking.index');

Route::get('/aaaa', function () {
    return response()->json(['message' => 'favorites.index まだ未実装']);
})->name('favorites.index');

Route::get('/aaaaa', function () {
    return response()->json(['message' => 'favorites.toggle まだ未実装']);
})->name('favorites.toggle');

Route::get('/aaaaaa', function () {
    return response()->json(['message' => 'reviews.like まだ未実装']);
})->name('reviews.like');
