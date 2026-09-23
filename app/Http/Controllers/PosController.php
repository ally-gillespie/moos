<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use App\Services\OrderBuilder;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function create(MenuService $menuService)
    {
        return view('pos.create', [
            'menu' => $menuService->getMenu(),
        ]);
    }

    /**
     * Cashier submits the order. Payment is taken via the SumUp card
     * reader (handled client-side by the SumUp Reader SDK on the POS
     * device, or cash handled manually) BEFORE this request is sent, so
     * by the time we hit this endpoint the order is already paid — we
     * create it straight into 'queued' and broadcast immediately.
     *
     * If you'd rather record card-reader results server-side (e.g. via
     * a transaction ID SumUp's SDK returns), pass that through here and
     * store it against the order for reconciliation.
     */
    public function store(Request $request, OrderBuilder $builder)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.key' => 'required|string',
            'items.*.name' => 'required|string',
            'items.*.unit_price_pence' => 'required|integer|min:0',
            'items.*.quantity' => 'required|integer|min:1|max:20',
            'items.*.removed' => 'array',
            'items.*.added' => 'array',
            'customer_name' => 'nullable|string|max:60',
            'payment_method' => 'required|in:card,cash',
        ]);

        $order = $builder->build(
            cartItems: $validated['items'],
            source: 'pos',
            staffId: $request->session()->get('staff_id'),
            customerName: $validated['customer_name'] ?? null,
        );

        $builder->markPaidAndQueue($order);

        return response()->json([
            'order_number' => $order->order_number,
            'total' => $order->totalFormatted(),
        ]);
    }
}
