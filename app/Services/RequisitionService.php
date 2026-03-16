<?php

namespace App\Services;

use App\Exceptions\BookUnavailableException;
use App\Exceptions\UserRequisitionLimitExceededException;
use App\Models\Book;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RequisitionService
{
    /**
     * Cria uma requisição de forma atómica, prevenindo race conditions.
     *
     * Usa lockForUpdate() no livro para garantir que dois utilizadores
     * não possam requisitar o mesmo exemplar simultaneamente.
     *
     * @throws BookUnavailableException Quando o livro já está requisitado
     * @throws UserRequisitionLimitExceededException Quando o utilizador tem 3 requisições ativas
     */
    public function createRequisition(User $user, int $bookId): Requisition
    {
        return DB::transaction(function () use ($user, $bookId) {
            // Lock do livro para evitar race condition entre requisições concorrentes
            $book = Book::where('id', $bookId)->lockForUpdate()->first();

            if (! $book) {
                throw new BookUnavailableException('Book not found.');
            }

            if ((int) $book->stock <= 0) {
                throw new BookUnavailableException('Book has no stock available.');
            }

            // Regra 1: Livro já está requisitado? (active ou late)
            $alreadyRequested = Requisition::where('book_id', $book->id)
                ->whereIn('status', [
                    Requisition::STATUS_ACTIVE,
                    Requisition::STATUS_LATE,
                ])
                ->exists();

            if ($alreadyRequested) {
                throw new BookUnavailableException('Book is not available.');
            }

            // Regra 2: Utilizador já tem 3 requisições ativas?
            $activeCount = Requisition::where('user_id', $user->id)
                ->where('status', Requisition::STATUS_ACTIVE)
                ->count();

            if ($activeCount >= 3) {
                throw new UserRequisitionLimitExceededException('You already have 3 active requisitions.');
            }

            return Requisition::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'photo_path' => $user->profile_photo_path,
            ]);
        });
    }
}
