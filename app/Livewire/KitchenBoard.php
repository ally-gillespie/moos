<?php

namespace App\Livewire;

use App\Events\KitchenOrderUpdated;
use App\Events\QueueDisplayUpdated;
use App\Models\Order;
use App\Services\TwilioService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title("Kitchen — Moo's")]
class KitchenBoard extends Component
{
    public function bump(int $orderId): void
    {
        $order = Order::find($orderId);
        if (! $order) return;

        $next = $order->nextStatus();
        if (! $next) return;

        $updates = ['status' => $next];

        if ($next === 'preparing') {
            $updates['preparing_at'] = now();
        } elseif ($next === 'ready') {
            $updates['ready_at'] = now();
        }

        $order->update($updates);

        if ($next === 'ready') {
            app(TwilioService::class)->sendOrderReady($order->fresh()->load('items'));
        }

        broadcast(new KitchenOrderUpdated($order));
        broadcast(new QueueDisplayUpdated($order));
    }

    public function cancel(int $orderId): void
    {
        $order = Order::find($orderId);
        if (! $order) return;

        $order->update(['status' => 'cancelled']);

        broadcast(new QueueDisplayUpdated($order));
    }

    public function render()
    {
        $orders = Order::with('items.customizations')
            ->whereIn('status', ['queued', 'preparing', 'ready'])
            ->whereDate('trading_date', now()->toDateString())
            ->orderBy('order_number')
            ->get();

        return view('livewire.kitchen-board', [
            'orders'    => $orders,
            'staffName' => session('staff_name'),
        ]);
    }
}
