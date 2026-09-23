<?php

namespace App\Http\Controllers;

use App\Events\KitchenOrderUpdated;
use App\Events\QueueDisplayUpdated;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    /**
     * Bump an order to its next status (queued -> preparing -> ready -> collected).
     * Fires two events: full detail to the private kitchen channel, and
     * order number + status only to the public queue-display channel.
     */
    public function bump(Order $order)
    {
        $next = $order->nextStatus();

        abort_if(! $next, 400, 'Order has no further status to move to.');

        $order->status = $next;

        if ($next === 'preparing') {
            $order->preparing_at = now();
        } elseif ($next === 'ready') {
            $order->ready_at = now();
        }

        $order->save();

        broadcast(new KitchenOrderUpdated($order));
        broadcast(new QueueDisplayUpdated($order));

        return response()->json(['status' => $order->status]);
    }

    public function cancel(Order $order)
    {
        $order->update(['status' => 'cancelled']);

        broadcast(new QueueDisplayUpdated($order));

        return response()->json(['status' => 'cancelled']);
    }
}
