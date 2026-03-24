<?php

namespace App\Services;

use App\Exceptions\BookUnavailableException;
use App\Exceptions\UserRequisitionLimitExceededException;
use App\Models\Book;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RequisitionService
{
    /**
     * Estados que contam para o limite de requisições por utilizador (máx. 3).
     */
    private function userSlotStatuses(): array
    {
        return [
            Requisition::STATUS_PENDING,
            Requisition::STATUS_ACTIVE,
            Requisition::STATUS_LATE,
        ];
    }

    /**
     * Estados em que o exemplar está emprestado (bloqueia novo pedido).
     * Pending não consome stock nem bloqueia o livro até aprovação.
     */
    private function bookBlockingStatuses(): array
    {
        return [
            Requisition::STATUS_ACTIVE,
            Requisition::STATUS_LATE,
        ];
    }

    /**
     * Cria um pedido em estado pendente até aprovação do administrador.
     *
     * @throws BookUnavailableException
     * @throws UserRequisitionLimitExceededException
     */
    public function createRequisition(User $user, int $bookId): Requisition
    {
        return DB::transaction(function () use ($user, $bookId) {
            $slotCount = Requisition::where('user_id', $user->id)
                ->whereIn('status', $this->userSlotStatuses())
                ->count();

            if ($slotCount >= 3) {
                throw new UserRequisitionLimitExceededException(
                    'You already have 3 requisitions pending or active on loan.'
                );
            }

            $book = Book::where('id', $bookId)->lockForUpdate()->first();

            if (! $book) {
                throw new BookUnavailableException('Book not found.');
            }

            if ((int) $book->stock <= 0) {
                throw new BookUnavailableException('Book has no stock available.');
            }

            $alreadyRequested = Requisition::where('book_id', $book->id)
                ->whereIn('status', $this->bookBlockingStatuses())
                ->exists();

            if ($alreadyRequested) {
                throw new BookUnavailableException('Book is not available.');
            }

            return Requisition::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'photo_path' => $user->profile_photo_path,
                'status' => Requisition::STATUS_PENDING,
            ]);
        });
    }

    /**
     * Aprova um pedido pendente (passa a requisição ativa).
     *
     * @throws BookUnavailableException
     * @throws InvalidArgumentException
     */
    public function approveRequisition(Requisition $requisition): Requisition
    {
        return DB::transaction(function () use ($requisition) {
            $locked = Requisition::whereKey($requisition->getKey())->lockForUpdate()->first();

            if (! $locked || $locked->status !== Requisition::STATUS_PENDING) {
                throw new InvalidArgumentException('Only pending requisitions can be approved.');
            }

            $book = Book::where('id', $locked->book_id)->lockForUpdate()->first();

            if (! $book || (int) $book->stock <= 0) {
                throw new BookUnavailableException('Book has no stock available.');
            }

            $taken = Requisition::where('book_id', $book->id)
                ->where('id', '!=', $locked->id)
                ->whereIn('status', [
                    Requisition::STATUS_ACTIVE,
                    Requisition::STATUS_LATE,
                ])
                ->exists();

            if ($taken) {
                throw new BookUnavailableException('Book is not available.');
            }

            $locked->update(['status' => Requisition::STATUS_ACTIVE]);
            $book->decrement('stock');

            return $locked->fresh();
        });
    }

    /**
     * Rejeita um pedido pendente.
     *
     * @throws InvalidArgumentException
     */
    public function rejectRequisition(Requisition $requisition): Requisition
    {
        return DB::transaction(function () use ($requisition) {
            $locked = Requisition::whereKey($requisition->getKey())->lockForUpdate()->first();

            if (! $locked || $locked->status !== Requisition::STATUS_PENDING) {
                throw new InvalidArgumentException('Only pending requisitions can be rejected.');
            }

            $locked->update(['status' => Requisition::STATUS_REJECTED]);

            return $locked->fresh();
        });
    }

    /**
     * Confirma devolução: estado returned e repõe stock do exemplar.
     *
     * @throws InvalidArgumentException
     */
    public function confirmReturn(Requisition $requisition): ?Book
    {
        return DB::transaction(function () use ($requisition) {
            $locked = Requisition::whereKey($requisition->getKey())->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, [
                Requisition::STATUS_ACTIVE,
                Requisition::STATUS_LATE,
            ], true)) {
                throw new InvalidArgumentException('Already returned');
            }

            $returnDate = now();

            $locked->update([
                'return_date' => $returnDate,
                'days_elapsed' => $returnDate->diffInDays($locked->request_date),
                'status' => Requisition::STATUS_RETURNED,
            ]);

            $book = Book::where('id', $locked->book_id)->lockForUpdate()->first();
            if ($book) {
                $book->increment('stock');
            }

            LogService::record(
                module: 'Requisition',
                action: 'returned',
                objectId: $locked->id,
                description: "Requisição devolvida (#{$locked->sequential_number})"
            );

            return $book?->fresh();
        });
    }
}
