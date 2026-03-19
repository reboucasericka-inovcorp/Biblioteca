<?php

namespace App\Http\Controllers;

use App\Models\Log;

class LogController extends Controller
{
    /**
     * Exibe os logs mais recentes.
     */
    public function index()
    {
        $logs = Log::query()
            ->with('user')
            ->orderBy('log_date', 'desc')
            ->orderBy('log_time', 'desc')
            ->paginate(10);

        return view('logs.index', compact('logs'));
    }

    /**
     * Ver detalhe de um log específico
     */
    public function show(Log $log)
    {
        $log->load('user');
        return view('logs.show', compact('log'));
    }
}

