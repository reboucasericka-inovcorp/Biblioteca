<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        return view('orders.index');
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.book', 'events']);

        return view('orders.show', compact('order'));
    }
}
