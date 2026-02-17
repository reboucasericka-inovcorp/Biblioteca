<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
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

        return $query->paginate(15);
    }

    public function stats(Request $request)
    {
        $user = $request->user();

        $query = Requisition::query();
        if (! $user->hasRole('Admin')) {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', Requisition::STATUS_ACTIVE)->count(),
            'returned' => (clone $query)->where('status', Requisition::STATUS_RETURNED)->count(),
            'late' => (clone $query)->where('status', Requisition::STATUS_LATE)->count(),
        ]);
    }
}
