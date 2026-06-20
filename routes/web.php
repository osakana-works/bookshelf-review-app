<?php

use App\Http\Controllers\GenreController;
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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::resource('genres', GenreController::class);
});

// 仮ルート
Route::get('/aaa', [GenreController::class, 'aa'])->name('books.index');
Route::get('/vvv', [GenreController::class, 'aa'])->name('ranking.index');
Route::get('/ccc', [GenreController::class, 'aa'])->name('books.create');
Route::get('/ddd', [GenreController::class, 'aa'])->name('favorites.index');
