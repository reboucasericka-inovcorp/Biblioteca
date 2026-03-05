<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookDownloadController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/citizen', function () {
        return view('dashboard.citizen');
    })->name('dashboard.citizen');

    Route::get('/dashboard/admin', function () {
        return view('dashboard.admin');
    })->middleware('role:Admin')->name('dashboard.admin');

    Route::get('/dashboard', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('Admin')) {
            return redirect()->route('dashboard.admin');
        }

        return redirect()->route('dashboard.citizen');
    })->name('dashboard');

    Route::get('/books', [BookController::class, 'index'])
        ->name('books.index');
    Route::get('/books/export', [BookController::class, 'export'])
        ->name('books.export');

    Route::middleware(['auth', 'role:Admin'])->group(function () {
        Route::resource('books', BookController::class)->except(['index', 'show']);
        Route::resource('authors', AuthorController::class)->except(['index', 'show']);
        Route::resource('publishers', PublisherController::class)->except(['index', 'show']);
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    });

    Route::get('/books/{book}', [BookController::class, 'show'])
        ->name('books.show');
    Route::get('/books/{book}/download', [BookDownloadController::class, 'download'])
        ->middleware('auth:sanctum')
        ->name('books.download');

    Route::get('/authors', [AuthorController::class, 'index'])
        ->name('authors.index');

    Route::get('/publishers', [PublisherController::class, 'index'])
        ->name('publishers.index');

    Route::get('/requisitions', [RequisitionController::class, 'index'])
        ->name('requisitions.index');
});
