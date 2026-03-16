<?php

namespace App\Observers;

use App\Models\Author;
use App\Services\LogService;

class AuthorObserver
{
    /**
     * Registar quando um autor é criado
     */
    public function created(Author $author): void
    {
        LogService::recordModel(
            model: $author,
            action: 'created'
        );
    }

    /**
     * Registar quando um autor é atualizado
     */
    public function updated(Author $author): void
    {
        LogService::recordModel(
            model: $author,
            action: 'updated',
            originalData: $author->getOriginal()
        );
    }

    /**
     * Registar quando um autor é eliminado
     */
    public function deleted(Author $author): void
    {
        LogService::recordModel(
            model: $author,
            action: 'deleted'
        );
    }

    /**
     * Registar quando um autor é restaurado (soft delete)
     */
    public function restored(Author $author): void
    {
        LogService::recordModel(
            model: $author,
            action: 'restored'
        );
    }
}
