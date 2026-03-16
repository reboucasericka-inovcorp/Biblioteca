<?php

namespace App\Observers;

use App\Models\Requisition;
use App\Services\LogService;

class RequisitionObserver
{
    /**
     * Registar quando uma requisição é criada
     */
    public function created(Requisition $requisition): void
    {
        LogService::recordModel(
            model: $requisition,
            action: 'created'
        );
    }

    /**
     * Registar quando uma requisição é atualizada.
     * Não regista update genérico quando o status mudou para returned
     * (já tratado manualmente em RequisitionController::confirmReturn).
     */
    public function updated(Requisition $requisition): void
    {
        $originalStatus = $requisition->getOriginal('status');
        $newStatus = $requisition->status;

        if ($originalStatus !== Requisition::STATUS_RETURNED && $newStatus === Requisition::STATUS_RETURNED) {
            return;
        }

        LogService::recordModel(
            model: $requisition,
            action: 'updated',
            originalData: $requisition->getOriginal()
        );
    }

    /**
     * Registar quando uma requisição é eliminada
     */
    public function deleted(Requisition $requisition): void
    {
        LogService::recordModel(
            model: $requisition,
            action: 'deleted'
        );
    }

    /**
     * Registar quando uma requisição é restaurada (soft delete)
     */
    public function restored(Requisition $requisition): void
    {
        LogService::recordModel(
            model: $requisition,
            action: 'restored'
        );
    }
}
