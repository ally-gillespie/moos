<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use App\Services\OrderBuilder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function create(MenuService $menuService)
    {
        return view('order.create', [
            'menu' => $menuService->getMenu(),
        ]);
    }

    /**
     * Customer has built their order on the public site. We create the
     * order in 'awaiting_payment' state, then hand off to PaymentController
     * to create the SumUp hosted checkout and redirect.
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
        ]);

        $order = $builder->build(
            cartItems: $validated['items'],
            source: 'web',
            staffId: null,
            customerName: $validated['customer_name'] ?? null,
        );

        // Return JSON rather than an HTTP redirect: the client does a full
        // page navigation to the payment step itself, so the browser (not
        // a JS fetch) follows the eventual redirect out to SumUp's hosted
        // checkout page — avoiding cross-origin fetch/redirect complications.
        return response()->json([
            'payment_url' => route('payment.initiate', $order),
        ]);
    }
}
