<?php

namespace App\Jobs;

use App\Mail\AbandonedCartNotification;
use App\Models\CartActivity;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartEmails implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $cutoff = now()->subHour();

        CartActivity::query()
            ->with('user')
            ->where('updated_at', '<=', $cutoff)
            ->get()
            ->each(function (CartActivity $activity) use ($cutoff) {
                $user = $activity->user;
                if (! $user?->email) {
                    return;
                }

                $hasOrderSince = Order::where('user_id', $user->id)
                    ->where('created_at', '>=', $activity->updated_at)
                    ->where('status', Order::STATUS_PAID)
                    ->exists();

                if ($hasOrderSince) {
                    return;
                }

                Mail::to($user->email)->send(new AbandonedCartNotification($user));

                $activity->delete();
            });
    }
}
