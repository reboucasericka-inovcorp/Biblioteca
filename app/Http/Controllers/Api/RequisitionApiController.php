<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BookUnavailableException;
use App\Exceptions\UserRequisitionLimitExceededException;
use App\Http\Controllers\Controller;
use App\Mail\RequisitionCreated;
use App\Models\Requisition;
use App\Models\User;
use App\Services\RequisitionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class RequisitionApiController extends Controller
{
    public function __construct(
        private readonly RequisitionService $requisitionService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Requisition::with(['user', 'book.publisher', 'book.authors']);

        // Admin vê todas; utilizador normal vê apenas as suas
        if (! $user->hasRole('Admin')) {
            $query->where('user_id', $user->id);
        }

        // Filtro por status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // FIX: evitar orderBy com campos arbitrários vindos do request (segurança)
        $allowedSorts = ['created_at', 'request_date', 'due_date', 'return_date', 'status'];

        $sort = $request->get('sort', 'created_at');
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $dir = $request->get('dir', 'desc');
        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $dir);

        return ApiResponse::success($query->paginate(15));
    }

    public function stats(Request $request)
    {
        $user = $request->user();

        $query = Requisition::query();
        if (! $user->hasRole('Admin')) {
            $query->where('user_id', $user->id);
        }

        $active = (clone $query)->where('status', Requisition::STATUS_ACTIVE)->count();
        $pending = (clone $query)->where('status', Requisition::STATUS_PENDING)->count();
        $last30Days = (clone $query)->where('request_date', '>=', now()->subDays(30))->count();
        $deliveredToday = (clone $query)->whereDate('return_date', today())->count();

        return ApiResponse::success([
            'active' => $active,
            'pending' => $pending,
            'last_30_days' => $last30Days,
            'delivered_today' => $deliveredToday,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_id' => 'required|integer|exists:books,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'data' => null,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        try {
            $requisition = $this->requisitionService->createRequisition($user, (int) $request->book_id);
        } catch (BookUnavailableException|UserRequisitionLimitExceededException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        $requisition->load(['book', 'user']);
        Mail::to($user->email)->send(new RequisitionCreated($requisition));
        $admins = User::role('Admin')->get();
        if ($admins->isNotEmpty()) {
            Mail::to($admins)->send(new RequisitionCreated($requisition));
        }

        return ApiResponse::success(null, 'Requisition created successfully.', 201);
    }

    public function approve(Requisition $requisition)
    {
        try {
            $this->requisitionService->approveRequisition($requisition);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (BookUnavailableException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success(null, 'Requisition approved.');
    }

    public function reject(Requisition $requisition)
    {
        try {
            $this->requisitionService->rejectRequisition($requisition);
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::success(null, 'Requisition rejected.');
    }
}
