<?php

namespace App\Observers;

use App\Models\Book;
use App\Services\LogService;

class BookObserver
{
    /**
     * Registar quando um livro é criado
     */
    public function created(Book $book): void
    {
        LogService::recordModel(
            model: $book,
            action: 'created'
        );
    }

    /**
     * Registar quando um livro é atualizado
     */
    public function updated(Book $book): void
    {
        LogService::recordModel(
            model: $book,
            action: 'updated',
            originalData: $book->getOriginal()
        );
    }

    /**
     * Registar quando um livro é eliminado
     */
    public function deleted(Book $book): void
    {
        LogService::recordModel(
            model: $book,
            action: 'deleted'
        );
    }

    /**
     * Registar quando um livro é restaurado (soft delete)
     */
    public function restored(Book $book): void
    {
        LogService::recordModel(
            model: $book,
            action: 'restored'
        );
    }
}
