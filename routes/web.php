<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use Illuminate\Support\Facades\Route;

// 公開ルート（ゲストもアクセス可能）
Route::get('/', [BookController::class, 'index']);
Route::resource('books', BookController::class)->only(['index', 'show']);

// 認証が必要なルート
Route::middleware('auth')->group(function () {
    Route::resource('books', BookController::class)->except(['index', 'show']);
    Route::resource('genres', GenreController::class);

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
Route::post('/books/{book}/reviews', function () {
    return redirect()->back();
})->name('reviews.store');
Route::post('/reviews/{review}/like', function () {
    return redirect()->back();
})->name('reviews.like');
Route::get('/reviews/{review}/edit', function () {
    return redirect()->back();
})->name('reviews.edit');
Route::delete('/reviews/{review}', function () {
    return redirect()->back();
})->name('reviews.destroy');
