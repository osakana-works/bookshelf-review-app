<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RankingController;
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
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

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

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{book}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/reviews/{review}/like', [LikeController::class, 'toggle'])->name('reviews.like');

    // 応用機能実装中の仮ルート
    Route::get('/reports', fn () => '実装中')->name('reports.index');

    Route::get('/reading-plans', fn () => '実装中')->name('reading-plans.index');
    Route::get('/reading-plans/create', fn () => '実装中')->name('reading-plans.create');
    Route::post('/reading-plans', fn () => '実装中')->name('reading-plans.store');
    Route::get('/reading-plans/{readingPlan}/edit', fn () => '実装中')->name('reading-plans.edit');
    Route::put('/reading-plans/{readingPlan}', fn () => '実装中')->name('reading-plans.update');
    Route::delete('/reading-plans/{readingPlan}', fn () => '実装中')->name('reading-plans.destroy');
    Route::post('/reading-plans/{readingPlan}/complete', fn () => '実装中')->name('reading-plans.complete');

    Route::get('/notifications', fn () => '実装中')->name('notifications.index');
    Route::post('/notifications/{id}/read', fn () => '実装中')->name('notifications.read');

});

// ｛book｝が別のルートと競合するため、最後に配置する
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
