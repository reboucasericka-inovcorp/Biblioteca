<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\AuthorApiController;
use App\Http\Controllers\Api\PublisherApiController;
use App\Http\Controllers\Api\RequisitionApiController;
use App\Http\Controllers\Api\GoogleBooksApiController;
use App\Http\Controllers\RequisitionController;

Route::get('/books', [BookApiController::class, 'index']);
//Route::get('/books/export', [BookApiController::class, 'export']);
Route::get('/books/{book}', [BookApiController::class, 'show']);
Route::get('/authors', [AuthorApiController::class, 'index']);
Route::get('/publishers', [PublisherApiController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/requisitions/stats', [RequisitionApiController::class, 'stats']);
    Route::get('/requisitions', [RequisitionApiController::class, 'index']);
    Route::post('/requisitions', [RequisitionController::class, 'store']);
    Route::post('/requisitions/{requisition}/return', [RequisitionController::class, 'confirmReturn'])
        ->middleware('role:Admin');

    Route::get('/google-books/search', [GoogleBooksApiController::class, 'search']);
    Route::post('/google-books/import', [GoogleBooksApiController::class, 'import'])
        ->middleware('role:Admin');
});


