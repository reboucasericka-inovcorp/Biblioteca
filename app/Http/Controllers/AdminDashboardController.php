<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\Publisher;
use App\Models\Requisition;
use App\Models\User;

class AdminDashboardController extends Controller
{
    /**
     * Mostra o painel administrativo com métricas e dados recentes.
     */
    public function __invoke()
    {
        $totalBooks = Book::query()->count();
        $totalAuthors = Author::query()->count();
        $totalPublishers = Publisher::query()->count();
        $totalUsers = User::query()->count();
        $totalRequisitions = Requisition::query()->count();
        $lateRequisitions = Requisition::query()
            ->where('status', Requisition::STATUS_LATE)
            ->count();

        $latestBooks = Book::query()
            ->with('publisher')
            ->latest()
            ->limit(5)
            ->get();

        $recentRequisitions = Requisition::query()
            ->with(['user', 'book'])
            ->latest('request_date')
            ->limit(5)
            ->get();

        return view('dashboard.admin', [
            'totalBooks' => $totalBooks,
            'totalAuthors' => $totalAuthors,
            'totalPublishers' => $totalPublishers,
            'totalUsers' => $totalUsers,
            'totalRequisitions' => $totalRequisitions,
            'lateRequisitions' => $lateRequisitions,
            'latestBooks' => $latestBooks,
            'recentRequisitions' => $recentRequisitions,
        ]);
    }
}

