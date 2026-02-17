<?php

namespace App\Jobs;

use App\Mail\RequisitionReminder;
use App\Models\Requisition;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendRequisitionReminder implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Requisition::whereDate('due_date', now()->addDay())
            ->where('status', Requisition::STATUS_ACTIVE)
            ->with(['user', 'book'])
            ->each(function (Requisition $req) {
                Mail::to($req->user->email)->send(new RequisitionReminder($req));
            });
    }
}
