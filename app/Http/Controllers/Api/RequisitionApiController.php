<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class RequisitionApiController extends Controller
{
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

        // Ordenação
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('dir', 'desc');
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
        $last30Days = (clone $query)->where('request_date', '>=', now()->subDays(30))->count();
        $deliveredToday = (clone $query)->whereDate('return_date', today())->count();

        return ApiResponse::success([
            'active' => $active,
            'last_30_days' => $last30Days,
            'delivered_today' => $deliveredToday,
        ]);
    }
}
