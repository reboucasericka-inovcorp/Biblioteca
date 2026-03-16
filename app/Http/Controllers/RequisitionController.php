<?php

namespace App\Http\Controllers;

use App\Exceptions\BookUnavailableException;
use App\Exceptions\UserRequisitionLimitExceededException;
use App\Mail\RequisitionCreated;
use App\Models\Requisition;
use App\Models\User;
use App\Services\BookAvailabilityAlertService;
use App\Services\LogService;
use App\Services\RequisitionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RequisitionController extends Controller
{
    public function __construct(
        private readonly RequisitionService $requisitionService,
        private readonly BookAvailabilityAlertService $bookAvailabilityAlertService
    ) {}

    public function index()
    {
        return view('requisitions.index');
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
        Mail::to(User::role('Admin')->get())->send(new RequisitionCreated($requisition));

        return ApiResponse::success(null, 'Requisition created successfully.', 201);
    }

    public function confirmReturn(Requisition $requisition)
    {
        if (! in_array($requisition->status, [
            Requisition::STATUS_ACTIVE,
            Requisition::STATUS_LATE,
        ])) {
            return ApiResponse::error('Already returned', 422);
        }

        $returnDate = now();

        $requisition->update([
            'return_date' => $returnDate,
            'days_elapsed' => $returnDate->diffInDays($requisition->request_date),
            'status' => Requisition::STATUS_RETURNED,
        ]);

        // Registar a devolução no log (ação especial, não capturada por observer de update)
        LogService::record(
            module: 'Requisition',
            action: 'returned',
            objectId: $requisition->id,
            description: "Requisição devolvida (#{$requisition->sequential_number})"
        );

        $book = $requisition->book()->first();
        if ($book && $book->isAvailable()) {
            $this->bookAvailabilityAlertService->notifyBookAvailable($book);
        }

        return ApiResponse::success(null, 'Return confirmed');
    }
}
