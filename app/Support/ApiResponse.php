<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message = 'Erro', int $status = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => null,
        ], $status);
    }
}