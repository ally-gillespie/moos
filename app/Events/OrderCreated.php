<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        // Kitchen only — full detail. Never broadcast this to the public channel.
        return [
            new PrivateChannel('kitchen-orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderCreated';
    }

    public function broadcastWith(): array
    {
        $this->order->load('items.customizations');

        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'source' => $this->order->source,
            'status' => $this->order->status,
            'customer_name' => $this->order->customer_name,
            'items' => $this->order->items->map(fn ($item) => [
                'name' => $item->item_name,
                'quantity' => $item->quantity,
                'customizations' => $item->customizations->map(fn ($c) => [
                    'ingredient' => $c->ingredient,
                    'action' => $c->action,
                ]),
            ]),
            'created_at' => $this->order->created_at->toIso8601String(),
        ];
    }
}
