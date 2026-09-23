<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', params: ['bodyClass' => 'bg-neutral-900 text-white min-h-screen p-10'])]
#[Title("Order Status — Moo's")]
class QueueDisplay extends Component
{
    public function render()
    {
        $orders = Order::whereIn('status', ['preparing', 'ready'])
            ->whereDate('trading_date', now()->toDateString())
            ->orderBy('order_number')
            ->get(['order_number', 'status', 'customer_name']);

        return view('livewire.queue-display', [
            'orders' => $orders,
        ]);
    }
}
