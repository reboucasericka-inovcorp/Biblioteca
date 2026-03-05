<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ReviewDomainException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RejectReviewRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Mail\ReviewCreatedForAdmin;
use App\Mail\ReviewStatusUpdatedForCitizen;
use App\Models\Requisition;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReviewApiController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService
    ) {}

    public function index(Request $request)
    {
        $query = Review::query()->with(['user', 'book', 'requisition']);
        $status = $request->get('status');

        if ($status) {
            $query->where('status', $status);
        }

        $reviews = $query->orderByDesc('created_at')->paginate(15);

        return ApiResponse::success($reviews);
    }

    public function storeForRequisition(StoreReviewRequest $request, Requisition $requisition)
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Unauthorized.', 401);
        }

        try {
            $review = $this->reviewService->createReview(
                $user,
                $requisition,
                (int) $request->validated('rating'),
                (string) $request->validated('comment'),
            );
        } catch (ReviewDomainException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        $review->load(['user', 'book', 'requisition']);

        $adminEmails = User::role('Admin')->pluck('email')->all();
        if (! empty($adminEmails)) {
            Mail::to($adminEmails)->send(
                new ReviewCreatedForAdmin($review, route('reviews.show', $review))
            );
        }

        return ApiResponse::success($review, 'Review created and awaiting moderation.', 201);
    }

    public function approve(Review $review)
    {
        try {
            $review = $this->reviewService->approveReview($review);
        } catch (ReviewDomainException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        Mail::to($review->user->email)->send(new ReviewStatusUpdatedForCitizen($review));

        return ApiResponse::success($review, 'Review approved successfully.');
    }

    public function reject(RejectReviewRequest $request, Review $review)
    {
        try {
            $review = $this->reviewService->rejectReview(
                $review,
                (string) $request->validated('reason')
            );
        } catch (ReviewDomainException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        Mail::to($review->user->email)->send(new ReviewStatusUpdatedForCitizen($review));

        return ApiResponse::success($review, 'Review rejected successfully.');
    }
}
