<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\PublisherController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/books', [BookController::class, 'index'])
        ->name('books.index');

    Route::get('/authors', [AuthorController::class, 'index'])
        ->name('authors.index');

    Route::get('/publishers', [PublisherController::class, 'index'])
        ->name('publishers.index');

});
