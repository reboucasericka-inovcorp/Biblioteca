<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\AuthorApiController;
use App\Http\Controllers\Api\PublisherApiController;

Route::get('/books', [BookApiController::class, 'index']);
Route::get('/authors', [AuthorApiController::class, 'index']);
Route::get('/publishers', [PublisherApiController::class, 'index']);


