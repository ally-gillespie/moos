<?php

namespace App\Services;

use App\Events\KitchenOrderUpdated;
use App\Events\OrderCreated;
use App\Events\QueueDisplayUpdated;
use App\Models\DailyCounter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemCustomization;
use Illuminate\Support\Facades\DB;

class OrderBuilder
{
    public function __construct(private MenuService $menu) {}

    /**
     * Build an order + items + customizations from cart data.
     *
     * $cartItems format:
     * [
     *   [
     *     'key' => 'cheese', 'name' => 'Cheeseburger', 'unit_price_pence' => 700, 'quantity' => 1,
     *     'removed' => ['pickles'],
     *     'added' => [['ingredient' => 'bacon', 'extra_price_pence' => 100]],
     *   ],
     *   ...
     * ]
     */
    public function build(array $cartItems, string $source, ?int $staffId = null, ?string $customerName = null, ?string $phoneNumber = null, bool $smsOptIn = false): Order
    {
        $tradingDate = now()->toDateString();

        return DB::transaction(function () use ($cartItems, $source, $staffId, $customerName, $phoneNumber, $smsOptIn, $tradingDate) {
            $orderNumber = DailyCounter::nextNumberFor($tradingDate);

            $totalPence = 0;
            foreach ($cartItems as $item) {
                $lineTotal = ($item['unit_price_pence'] * $item['quantity']);
                foreach ($item['added'] ?? [] as $extra) {
                    $lineTotal += ($extra['extra_price_pence'] ?? 0) * $item['quantity'];
                }
                $totalPence += $lineTotal;
            }

            $menuData   = $this->menu->getMenu();
            $burgerKeys = array_column($menuData['burgers'], 'key');
            $itemKeys   = array_column($cartItems, 'key');
            $hasBurger  = (bool) array_intersect($burgerKeys, $itemKeys);
            foreach ($menuData['meal_deals'] as $deal) {
                if ($hasBurger && in_array($deal['side_key'], $itemKeys)) {
                    $totalPence -= $deal['discount_pence'];
                }
            }
            $totalPence = max(0, $totalPence);

            $order = Order::create([
                'order_number'  => $orderNumber,
                'trading_date'  => $tradingDate,
                'source'        => $source,
                'status'        => 'awaiting_payment',
                'customer_name' => $customerName,
                'phone_number'  => $phoneNumber,
                'sms_opt_in'    => $smsOptIn,
                'total_pence'   => $totalPence,
                'staff_id'      => $staffId,
                'payment_status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_name' => $item['name'],
                    'unit_price_pence' => $item['unit_price_pence'],
                    'quantity' => $item['quantity'],
                ]);

                foreach ($item['removed'] ?? [] as $ingredient) {
                    OrderItemCustomization::create([
                        'order_item_id' => $orderItem->id,
                        'ingredient' => $ingredient,
                        'action' => 'removed',
                    ]);
                }

                foreach ($item['added'] ?? [] as $extra) {
                    OrderItemCustomization::create([
                        'order_item_id' => $orderItem->id,
                        'ingredient' => $extra['ingredient'],
                        'action' => 'added',
                        'extra_price_pence' => $extra['extra_price_pence'] ?? 0,
                    ]);
                }
            }

            return $order;
        });
    }

    /**
     * Mark an order paid and push it into the queue: fires the kitchen
     * event (full detail) and the public queue event (number + status only).
     */
    public function markPaidAndQueue(Order $order): Order
    {
        $order->update([
            'status' => 'queued',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        broadcast(new OrderCreated($order));
        broadcast(new QueueDisplayUpdated($order));

        return $order;
    }
}
