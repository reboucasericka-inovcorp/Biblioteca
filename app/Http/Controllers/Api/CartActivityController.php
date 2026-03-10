<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartActivity;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class CartActivityController extends Controller
{
    /**
     * Regista atividade do carrinho (quando o utilizador altera o carrinho).
     * Usado para enviar email de carrinho abandonado após 1h sem compra.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return ApiResponse::error('Unauthorized', 401);
        }

        CartActivity::updateOrInsert(
            ['user_id' => $user->id],
            ['updated_at' => now()]
        );

        return response()->noContent(204);
    }
}
