<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\AuthorApiController;
use App\Http\Controllers\Api\PublisherApiController;
use App\Http\Controllers\Api\RequisitionApiController;
use App\Http\Controllers\Api\GoogleBooksApiController;
use App\Http\Controllers\Api\BookSuggestionApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\RequisitionController;

Route::get('/books', [BookApiController::class, 'index']);
//Route::get('/books/export', [BookApiController::class, 'export']);
Route::get('/authors', [AuthorApiController::class, 'index']);
Route::get('/publishers', [PublisherApiController::class, 'index']);
Route::get('/google-books/search', [GoogleBooksApiController::class, 'search']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/books/{book}', [BookApiController::class, 'show']);
    Route::get('/requisitions/stats', [RequisitionApiController::class, 'stats']);
    Route::get('/requisitions', [RequisitionApiController::class, 'index']);
    Route::post('/requisitions', [RequisitionController::class, 'store']);
    Route::post('/requisitions/{requisition}/return', [RequisitionController::class, 'confirmReturn'])
        ->middleware('role:Admin');
    Route::post('/google-books/import', [GoogleBooksApiController::class, 'import'])
        ->middleware('role:Admin');

    Route::post('/book-suggestions', [BookSuggestionApiController::class, 'store']);
    Route::get('/book-suggestions', [BookSuggestionApiController::class, 'index']);
    Route::patch('/book-suggestions/{bookSuggestion}/approve', [BookSuggestionApiController::class, 'approve'])
        ->middleware('role:Admin');
    Route::patch('/book-suggestions/{bookSuggestion}/reject', [BookSuggestionApiController::class, 'reject'])
        ->middleware('role:Admin');

    Route::get('/users', [UserApiController::class, 'index'])
        ->middleware('role:Admin');
    Route::patch('/users/{user}/role', [UserApiController::class, 'updateRole'])
        ->middleware('role:Admin');
});


