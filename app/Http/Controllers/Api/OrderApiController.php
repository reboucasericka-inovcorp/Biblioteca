<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['user', 'items.book']);

        $status = $request->get('status');
        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderByDesc('created_at')->paginate(15);

        return ApiResponse::success($orders);
    }

    /**
     * Dashboard de vendas: total vendas, pedidos hoje, livros mais vendidos.
     */
    public function stats()
    {
        $totalSales = (float) Order::where('status', Order::STATUS_PAID)->sum('total');
        $ordersToday = Order::where('status', Order::STATUS_PAID)->whereDate('created_at', today())->count();
        $topBooks = OrderItem::query()
            ->selectRaw('book_id, book_title, sum(quantity) as total_qty')
            ->whereHas('order', fn ($q) => $q->where('status', Order::STATUS_PAID))
            ->groupBy('book_id', 'book_title')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return ApiResponse::success([
            'total_sales' => round($totalSales, 2),
            'orders_today' => $ordersToday,
            'top_books' => $topBooks->map(fn ($row) => [
                'book_id' => $row->book_id,
                'book_title' => $row->book_title,
                'total_quantity' => (int) $row->total_qty,
            ]),
        ]);
    }
}
