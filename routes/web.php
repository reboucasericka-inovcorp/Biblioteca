<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\RequisitionController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $booksCount = \App\Models\Book::count();
        $authorsCount = \App\Models\Author::count();
        $publishersCount = \App\Models\Publisher::count();
        $recentBooks = \App\Models\Book::with(['publisher', 'authors'])->latest()->take(5)->get();
        
        return view('dashboard', compact('booksCount', 'authorsCount', 'publishersCount', 'recentBooks'));
    })->name('dashboard');

    Route::get('/books', [BookController::class, 'index'])
        ->name('books.index');
    Route::get('/books/export', [BookController::class, 'export'])
        ->name('books.export');

    Route::middleware(['auth', 'role:Admin'])->group(function () {
        Route::resource('books', BookController::class)->except(['index', 'show']);
        Route::resource('authors', AuthorController::class)->except(['index']);
        Route::resource('publishers', PublisherController::class)->except(['index']);
    });

    Route::get('/books/{book}', [BookController::class, 'show'])
        ->name('books.show');

    Route::get('/authors', [AuthorController::class, 'index'])
        ->name('authors.index');

    Route::get('/publishers', [PublisherController::class, 'index'])
        ->name('publishers.index');

    Route::get('/requisitions', [RequisitionController::class, 'index'])
        ->name('requisitions.index');
});
