<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index']);

Route::resource('books', BookController::class);

// 仮ルート
Route::get('/ranking', function () {
    return view('ranking.index');
})->name('ranking.index');

Route::get('/favorites', function () {
    return view('favorites.index');
})->name('favorites.index');

Route::get('/genres', function () {
    return view('genres.index');
})->name('genres.index');

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
