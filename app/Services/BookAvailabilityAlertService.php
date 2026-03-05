<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookAvailabilityAlert;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class BookAvailabilityAlertService
{
    public function subscribe(User $user, Book $book): BookAvailabilityAlert
    {
        $isUnavailable = $book->requisitions()
            ->whereIn('status', [
                Requisition::STATUS_ACTIVE,
                Requisition::STATUS_LATE,
            ])
            ->exists();

        if (! $isUnavailable) {
            throw new \DomainException('Alerts are available only for unavailable books.');
        }

        $alert = BookAvailabilityAlert::firstOrCreate(
            [
                'user_id' => $user->id,
                'book_id' => $book->id,
            ],
            [
                'notified_at' => null,
            ]
        );

        if ($alert->notified_at !== null) {
            $alert->update(['notified_at' => null]);
            $alert->refresh();
        }

        return $alert;
    }

    public function notifyBookAvailable(Book $book): int
    {
        $isStillUnavailable = $book->requisitions()
            ->whereIn('status', [
                Requisition::STATUS_ACTIVE,
                Requisition::STATUS_LATE,
            ])
            ->exists();

        if ($isStillUnavailable) {
            return 0;
        }

        $alerts = BookAvailabilityAlert::query()
            ->with(['user', 'book'])
            ->where('book_id', $book->id)
            ->whereNull('notified_at')
            ->get();

        $sentCount = 0;
        foreach ($alerts as $alert) {
            Mail::to($alert->user->email)->send(new \App\Mail\BookAvailableNotification($alert->book));
            $alert->update(['notified_at' => now()]);
            $sentCount++;
        }

        return $sentCount;
    }
}
