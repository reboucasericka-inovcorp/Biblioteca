<?php

namespace App\Services;

use App\Exceptions\ReviewDomainException;
use App\Models\Requisition;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * @throws ReviewDomainException
     */
    public function createReview(User $user, Requisition $requisition, int $rating, string $comment): Review
    {
        if ((int) $requisition->user_id !== (int) $user->id) {
            throw new ReviewDomainException('You can only review your own requisitions.');
        }

        if ($requisition->status !== Requisition::STATUS_RETURNED) {
            throw new ReviewDomainException('Review is allowed only after the requisition is returned.');
        }

        if ($requisition->review()->exists()) {
            throw new ReviewDomainException('A review already exists for this requisition.');
        }

        return DB::transaction(function () use ($user, $requisition, $rating, $comment): Review {
            return Review::create([
                'user_id' => $user->id,
                'book_id' => $requisition->book_id,
                'requisition_id' => $requisition->id,
                'rating' => $rating,
                'comment' => $comment,
                'status' => Review::STATUS_SUSPENDED,
                'refusal_reason' => null,
            ]);
        });
    }

    /**
     * @throws ReviewDomainException
     */
    public function approveReview(Review $review): Review
    {
        if ($review->status !== Review::STATUS_SUSPENDED) {
            throw new ReviewDomainException('Only suspended reviews can be approved.');
        }

        $review->update([
            'status' => Review::STATUS_ACTIVE,
            'refusal_reason' => null,
        ]);

        return $review->fresh(['user', 'book', 'requisition']);
    }

    /**
     * @throws ReviewDomainException
     */
    public function rejectReview(Review $review, string $reason): Review
    {
        if ($review->status !== Review::STATUS_SUSPENDED) {
            throw new ReviewDomainException('Only suspended reviews can be rejected.');
        }

        $review->update([
            'status' => Review::STATUS_REFUSED,
            'refusal_reason' => $reason,
        ]);

        return $review->fresh(['user', 'book', 'requisition']);
    }
}
