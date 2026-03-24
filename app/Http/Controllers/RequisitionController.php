<?php

namespace App\Http\Controllers;

use App\Exceptions\BookUnavailableException;
use App\Exceptions\UserRequisitionLimitExceededException;
use App\Mail\RequisitionCreated;
use App\Models\Requisition;
use App\Models\User;
use App\Services\BookAvailabilityAlertService;
use App\Services\RequisitionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class RequisitionController extends Controller
{
    public function __construct(
        private readonly RequisitionService $requisitionService,
        private readonly BookAvailabilityAlertService $bookAvailabilityAlertService
    ) {}

    public function index(Request $request)
    {
        if ($request->user()?->hasRole('Admin')) {
            return view('requisitions.index');
        }

        return view('requisitions.mine');
    }

    public function store(Request $request)
    {
        $request->validate(['book_id' => 'required|exists:books,id']);

        $user = Auth::user();
        if (! $user) {
            return ApiResponse::error('Unauthorized.', 401);
        }

        try {
            $requisition = $this->requisitionService->createRequisition($user, (int) $request->book_id);
        } catch (BookUnavailableException|UserRequisitionLimitExceededException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }

        $requisition->load(['book', 'user']);
        Mail::to($user->email)->send(new RequisitionCreated($requisition));
        $admins = User::role('Admin')->get();
        if ($admins->isNotEmpty()) {
            Mail::to($admins)->send(new RequisitionCreated($requisition));
        }

        return ApiResponse::success(null, 'Requisition created successfully.', 201);
    }

    public function confirmReturn(Requisition $requisition)
    {
        try {
            $book = $this->requisitionService->confirmReturn($requisition);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        if ($book && $book->isAvailable()) {
            $this->bookAvailabilityAlertService->notifyBookAvailable($book);
        }

        return ApiResponse::success(null, 'Return confirmed');
    }
}
