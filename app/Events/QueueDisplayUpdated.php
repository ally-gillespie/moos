<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueDisplayUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        // Public channel — no auth. Only order number + status, ever.
        return [new Channel('queue-display')];
    }

    public function broadcastAs(): string
    {
        return 'QueueDisplayUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
        ];
    }
}
