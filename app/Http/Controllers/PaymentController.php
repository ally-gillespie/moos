<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderBuilder;
use App\Services\SumUpService;

class PaymentController extends Controller
{
    /**
     * Create the SumUp hosted checkout for this order and redirect the
     * customer to SumUp's payment page.
     */
    public function initiate(Order $order, SumUpService $sumUp)
    {
        abort_if($order->payment_status === 'paid', 400, 'Order already paid.');

        $reference = 'order-' . $order->id;
        $redirectUrl = route('payment.callback', ['order' => $order->id]);

        $checkout = $sumUp->createHostedCheckout($reference, $order->total_pence, $redirectUrl);

        $order->update(['sumup_checkout_id' => $checkout['id']]);

        return redirect()->away($checkout['hosted_checkout_url']);
    }

    /**
     * SumUp redirects the customer back here after payment. We NEVER trust
     * this redirect alone — we re-verify the checkout status server-side
     * via the API before marking the order paid. (Ideally also confirm via
     * a SumUp webhook for belt-and-braces reliability.)
     */
    public function callback(Order $order, SumUpService $sumUp, OrderBuilder $builder)
    {
        if ($order->payment_status === 'paid') {
            return view('order.confirmation', ['order' => $order]);
        }

        abort_if(! $order->sumup_checkout_id, 400, 'No checkout associated with this order.');

        $checkout = $sumUp->getCheckout($order->sumup_checkout_id);

        if (($checkout['status'] ?? null) === 'PAID') {
            $builder->markPaidAndQueue($order);

            return view('order.confirmation', ['order' => $order]);
        }

        return view('order.payment-failed', ['order' => $order]);
    }
}
