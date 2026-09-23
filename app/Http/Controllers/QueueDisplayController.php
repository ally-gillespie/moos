<?php

namespace App\Http\Controllers;

use App\Models\Order;

class QueueDisplayController extends Controller
{
    public function index()
    {
        $orders = Order::whereIn('status', ['preparing', 'ready'])
            ->whereDate('trading_date', now()->toDateString())
            ->orderBy('order_number')
            ->get(['order_number', 'status', 'customer_name']);

        return view('queue.index', ['orders' => $orders]);
    }

    public function statusJson()
    {
        return Order::whereIn('status', ['preparing', 'ready'])
            ->whereDate('trading_date', now()->toDateString())
            ->orderBy('order_number')
            ->get(['order_number', 'status', 'customer_name']);
    }
}
