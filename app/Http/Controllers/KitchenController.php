<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function loginForm()
    {
        return view('kitchen.login', [
            'staff' => \App\Models\Staff::where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function index(Request $request)
    {
        $activeOrders = Order::with('items.customizations')
            ->whereIn('status', ['queued', 'preparing', 'ready'])
            ->whereDate('trading_date', now()->toDateString())
            ->orderBy('order_number')
            ->get();

        return view('kitchen.index', [
            'orders' => $activeOrders,
            'staffName' => $request->session()->get('staff_name'),
        ]);
    }

    public function ordersJson()
    {
        return Order::with('items.customizations')
            ->whereIn('status', ['queued', 'preparing', 'ready'])
            ->whereDate('trading_date', now()->toDateString())
            ->orderBy('order_number')
            ->get()
            ->map(fn ($order) => [
                'id'            => $order->id,
                'order_number'  => $order->order_number,
                'status'        => $order->status,
                'customer_name' => $order->customer_name,
                'source'        => $order->source,
                'items'         => $order->items->map(fn ($item) => [
                    'item_name'        => $item->item_name,
                    'quantity'         => $item->quantity,
                    'customizations'   => $item->customizations->map(fn ($c) => [
                        'ingredient' => $c->ingredient,
                        'action'     => $c->action,
                    ]),
                ]),
            ]);
    }
}
