<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioService
{
    public function sendOrderReady(Order $order): void
    {
        if (! $order->sms_opt_in || ! $order->phone_number) {
            return;
        }

        $accountSid = Setting::get('twilio_account_sid');
        $authToken  = Setting::get('twilio_auth_token');
        $from       = Setting::get('twilio_from_number');

        if (! $accountSid || ! $authToken || ! $from) {
            return;
        }

        $items = $order->items
            ->map(fn ($i) => $i->quantity > 1 ? "{$i->quantity}x {$i->item_name}" : $i->item_name)
            ->join(', ');

        $greeting = $order->customer_name ? "Hi {$order->customer_name}, your" : 'Your';
        $body = "{$greeting} order #{$order->order_number} ({$items}) is ready for collection!";

        try {
            (new Client($accountSid, $authToken))->messages->create($order->phone_number, [
                'from' => $from,
                'body' => $body,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Twilio SMS failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
