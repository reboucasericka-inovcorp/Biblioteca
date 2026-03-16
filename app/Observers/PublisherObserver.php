<?php

namespace App\Observers;

use App\Models\Publisher;
use App\Services\LogService;

class PublisherObserver
{
    /**
     * Registar quando uma editora é criada
     */
    public function created(Publisher $publisher): void
    {
        LogService::recordModel(
            model: $publisher,
            action: 'created'
        );
    }

    /**
     * Registar quando uma editora é atualizada
     */
    public function updated(Publisher $publisher): void
    {
        LogService::recordModel(
            model: $publisher,
            action: 'updated',
            originalData: $publisher->getOriginal()
        );
    }

    /**
     * Registar quando uma editora é eliminada
     */
    public function deleted(Publisher $publisher): void
    {
        LogService::recordModel(
            model: $publisher,
            action: 'deleted'
        );
    }

    /**
     * Registar quando uma editora é restaurada (soft delete)
     */
    public function restored(Publisher $publisher): void
    {
        LogService::recordModel(
            model: $publisher,
            action: 'restored'
        );
    }
}
