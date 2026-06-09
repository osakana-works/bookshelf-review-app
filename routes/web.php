<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// 公開ルート（ゲストもアクセス可能）
Route::get('/', [BookController::class, 'index']);
Route::resource('books', BookController::class)->only(['index', 'show']);

// 認証が必要なルート
Route::middleware('auth')->group(function () {
    Route::resource('books', BookController::class)->except(['index', 'show']);
    Route::resource('genres', GenreController::class);

    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // 仮ルート
    Route::get('/favorites', function () {
        return view('favorites.index');
    })->name('favorites.index');
});

// 公開ルート
Route::get('/ranking', function () {
    return view('ranking.index');
})->name('ranking.index');

// 仮ルート
Route::post('/books/{book}/favorites', function () {
    return redirect()->back();
})->name('favorites.toggle');

Route::post('/books/{book}/aaa', function () {
    return redirect()->back();
})->name('reviews.like');
