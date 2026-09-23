<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'trading_date', 'source', 'status', 'customer_name',
        'phone_number', 'sms_opt_in',
        'total_pence', 'staff_id', 'sumup_checkout_id', 'payment_status',
        'paid_at', 'preparing_at', 'ready_at',
    ];

    protected $casts = [
        'trading_date' => 'date',
        'sms_opt_in'   => 'boolean',
        'paid_at'      => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at'     => 'datetime',
    ];

    // Order in which the kitchen sees statuses progress
    public const STATUS_FLOW = ['queued', 'preparing', 'ready', 'collected'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function nextStatus(): ?string
    {
        $index = array_search($this->status, self::STATUS_FLOW, true);

        if ($index === false || $index + 1 >= count(self::STATUS_FLOW)) {
            return null;
        }

        return self::STATUS_FLOW[$index + 1];
    }

    public function totalFormatted(): string
    {
        return '£' . number_format($this->total_pence / 100, 2);
    }
}
