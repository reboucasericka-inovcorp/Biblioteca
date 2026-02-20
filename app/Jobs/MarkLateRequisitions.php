<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Requisition;

class MarkLateRequisitions implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Requisition::where('status', Requisition::STATUS_ACTIVE)
            ->whereDate('due_date', '<', now())
            ->update(['status' => Requisition::STATUS_LATE]);
    }
}
