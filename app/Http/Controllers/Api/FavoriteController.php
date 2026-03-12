<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Favorite;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Lista os livros favoritos do utilizador autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Não autenticado.', 401);
        }

        $favorites = Favorite::where('user_id', $user->id)
            ->with(['book.publisher', 'book.authors'])
            ->orderBy('favorites.created_at', 'desc')
            ->get()
            ->map(fn (Favorite $f) => $f->book)
            ->filter(fn (?Book $b) => $b && $b->is_active);

        return ApiResponse::success(BookResource::collection($favorites));
    }

    /**
     * Adiciona um livro aos favoritos. Se já existir, retorna sucesso na mesma.
     */
    public function store(Request $request, Book $book)
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Não autenticado.', 401);
        }

        if (! $book->is_active) {
            return ApiResponse::error('Livro indisponível.', 404);
        }

        $fav = Favorite::firstOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id]
        );

        return ApiResponse::success(['favorited' => true]);
    }

    /**
     * Remove um livro dos favoritos.
     */
    public function destroy(Request $request, Book $book)
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Não autenticado.', 401);
        }

        Favorite::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->delete();

        return ApiResponse::success(['favorited' => false]);
    }
}
