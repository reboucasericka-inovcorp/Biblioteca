<?php

use App\Http\Controllers\Api\AuthorApiController;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\BookAvailabilityAlertApiController;
use App\Http\Controllers\Api\BookSuggestionApiController;
use App\Http\Controllers\Api\CartActivityController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ChatApiController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GoogleBooksApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\PublisherApiController;
use App\Http\Controllers\Api\RequisitionApiController;
use App\Http\Controllers\Api\ReviewApiController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\RequisitionController;
use Illuminate\Support\Facades\Route;

Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/books', [BookApiController::class, 'index']);
Route::get('/books/{book}', [BookApiController::class, 'show']);
Route::get('/authors', [AuthorApiController::class, 'index']);
Route::get('/publishers', [PublisherApiController::class, 'index']);
Route::get('/google-books/search', [GoogleBooksApiController::class, 'search']);
Route::middleware('auth:sanctum')->post('/checkout', [CheckoutController::class, 'checkout']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

Route::middleware(['auth:sanctum', 'update.last.seen'])->group(function () {
    Route::get('/requisitions/stats', [RequisitionApiController::class, 'stats']);
    Route::get('/requisitions', [RequisitionApiController::class, 'index']);
    Route::post('/requisitions', [RequisitionApiController::class, 'store']);
    Route::patch('/requisitions/{requisition}/approve', [RequisitionApiController::class, 'approve'])
        ->middleware('role:Admin');
    Route::patch('/requisitions/{requisition}/reject', [RequisitionApiController::class, 'reject'])
        ->middleware('role:Admin');
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

    Route::post('/requisitions/{requisition}/review', [ReviewApiController::class, 'storeForRequisition'])
        ->middleware('role:Cidadao');
    Route::get('/orders', [OrderApiController::class, 'index'])
        ->middleware('role:Admin');
    Route::get('/orders/stats', [OrderApiController::class, 'stats'])
        ->middleware('role:Admin');
    Route::get('/reviews', [ReviewApiController::class, 'index'])
        ->middleware('role:Admin');
    Route::patch('/reviews/{review}/approve', [ReviewApiController::class, 'approve'])
        ->middleware('role:Admin');
    Route::patch('/reviews/{review}/reject', [ReviewApiController::class, 'reject'])
        ->middleware('role:Admin');

    Route::post('/books/{book}/alerts', [BookAvailabilityAlertApiController::class, 'store'])
        ->middleware('role:Cidadao');
    Route::post('/cart/activity', [CartActivityController::class, 'store']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/books/{book}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/books/{book}/favorite', [FavoriteController::class, 'destroy']);

    Route::prefix('chat')->group(function () {
        Route::get('/presence', [ChatApiController::class, 'presence']);
        Route::post('/presence/status', [ChatApiController::class, 'setPresenceStatus']);
        Route::get('/users', [ChatApiController::class, 'users']);
        Route::get('/rooms', [ChatApiController::class, 'rooms']);
        Route::post('/rooms', [ChatApiController::class, 'storeRoom']);
        Route::patch('/rooms/{room}', [ChatApiController::class, 'updateRoom']);
        Route::put('/rooms/{room}', [ChatApiController::class, 'updateRoom']);
        Route::post('/rooms/{room}/invite', [ChatApiController::class, 'inviteUser']);
        Route::post('/rooms/{room}/users', [ChatApiController::class, 'inviteUser']);
        Route::delete('/rooms/{room}', [ChatApiController::class, 'destroyRoom']);
        Route::delete('/rooms/{room}/users/{user}', [ChatApiController::class, 'removeUser']);
        Route::delete('/conversations/{conversation}', [ChatApiController::class, 'destroyConversation']);
        Route::post('/direct/{user}', [ChatApiController::class, 'startDirect']);
        Route::get('/direct/{conversation}', [ChatApiController::class, 'directMessages']);
        Route::get('/rooms/{room}/messages', [ChatApiController::class, 'roomMessages']);
        Route::post('/messages', [ChatApiController::class, 'storeMessage']);
        Route::put('/messages/{message}', [ChatApiController::class, 'updateMessage']);
        Route::delete('/messages/{message}', [ChatApiController::class, 'destroyMessage']);
        Route::post('/upload', [ChatApiController::class, 'upload']);
        Route::post('/messages/read', [ChatApiController::class, 'markAsRead']);
    });
});
