<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookAvailabilityAlertRequest;
use App\Http\Resources\BookAvailabilityAlertResource;
use App\Models\Book;
use App\Services\BookAvailabilityAlertService;
use App\Support\ApiResponse;

class BookAvailabilityAlertApiController extends Controller
{
    public function __construct(
        private readonly BookAvailabilityAlertService $bookAvailabilityAlertService
    ) {}

    public function store(StoreBookAvailabilityAlertRequest $request, Book $book)
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Unauthorized.', 401);
        }

        try {
            $alert = $this->bookAvailabilityAlertService->subscribe($user, $book);
        } catch (\DomainException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        return ApiResponse::success(
            new BookAvailabilityAlertResource($alert),
            'You will be notified when the book is available.'
        );
    }
}
