<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookDownloadController;
use App\Http\Controllers\CheckoutSuccessController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;


// Raiz: sempre página pública. O painel (admin/cidadão) só acessível após login em /dashboard
Route::get('/', function () {
    return view('welcome');
});

// Rotas específicas de livros antes do segmento {book} para evitar que "create" e "edit" sejam interpretados como ID
Route::get('/books/create', [BookController::class, 'create'])
    ->name('books.create')
    ->middleware(['auth', 'role:Admin']);
Route::get('/books/{book}/edit', [BookController::class, 'edit'])
    ->name('books.edit')
    ->middleware(['auth', 'role:Admin']);
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::view('/cart', 'cart.index')->name('cart.index');
Route::view('/checkout', 'checkout.index')->name('checkout.index')->middleware('auth');
Route::get('/checkout/success', [CheckoutSuccessController::class, 'success'])->name('checkout.success');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/citizen', function () {
        return view('dashboard.citizen');
    })->name('dashboard.citizen');

    Route::get('/dashboard/admin', AdminDashboardController::class)
        ->middleware('role:Admin')
        ->name('dashboard.admin');

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
        Route::resource('books', BookController::class)->except(['index', 'show', 'create', 'edit']);
        Route::resource('authors', AuthorController::class)->except(['index', 'show']);
        Route::resource('publishers', PublisherController::class)->except(['index', 'show']);
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    });

    Route::get('/books/{book}/download', [BookDownloadController::class, 'download'])
        ->middleware('auth:sanctum')
        ->name('books.download');

    Route::get('/authors', [AuthorController::class, 'index'])
        ->name('authors.index');

    Route::get('/publishers', [PublisherController::class, 'index'])
        ->name('publishers.index');

    Route::get('/requisitions', [RequisitionController::class, 'index'])
        ->name('requisitions.index');

       /* Route::get('/test-email', function () {

            Mail::raw('Email de teste do sistema Biblioteca', function ($message) {
                $message->to('reboucasericka@gmail.com')
                        ->subject('Teste de Email Laravel');
            });
        
            return "Email enviado!";
        });

    */
});
