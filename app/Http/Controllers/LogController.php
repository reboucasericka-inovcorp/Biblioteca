<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    /**
     * Listar todos os logs com filtros
     */
    public function index(Request $request): View
    {
        $query = Log::with('user');

        // Filtro por módulo
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filtro por utilizador
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por data (log_date)
        if ($request->filled('start_date')) {
            $query->whereDate('log_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('log_date', '<=', $request->end_date);
        }

        // Ordenar por log_date e log_time descendente (mais recentes primeiro)
        $logs = $query->orderBy('log_date', 'desc')
            ->orderBy('log_time', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Obter lista de módulos únicos para o filtro
        $modules = Log::distinct('module')
            ->orderBy('module')
            ->pluck('module');

        // Obter lista de utilizadores para o filtro
        $users = User::whereIn('id', Log::distinct('user_id')->whereNotNull('user_id')->pluck('user_id'))
            ->orderBy('name')
            ->get();

        return view('logs.index', compact('logs', 'modules', 'users'));
    }

    /**
     * Ver detalhe de um log específico
     */
    public function show(Log $log): View
    {
        $log->load('user');

        return view('logs.show', compact('log'));
    }
}

