<?php

namespace App\Http\Controllers;

use App\Services\StockReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class StockReconciliationController extends Controller
{
    public function reconcile(): JsonResponse
    {
        $exit = Artisan::call('app:fix-book-stock', [
            '--force' => true,
        ]);

        return response()->json([
            'ok' => $exit === 0,
            'message' => $exit === 0
                ? 'Comando de reconciliação concluído.'
                : 'O comando terminou com aviso ou erro. Consulte os logs.',
            'output' => trim(Artisan::output()),
        ], $exit === 0 ? 200 : 422);
    }

    public function rollback(): JsonResponse
    {
        $exit = Artisan::call('app:rollback-book-stock', [
            '--force' => true,
        ]);

        return response()->json([
            'ok' => $exit === 0,
            'message' => $exit === 0
                ? 'Rollback concluído.'
                : 'Rollback não aplicado (nada para reverter ou erro).',
            'output' => trim(Artisan::output()),
        ], $exit === 0 ? 200 : 422);
    }

    public function inconsistencies(StockReconciliationService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->inconsistencies(),
        ]);
    }
}
