<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemCustomization extends Model
{
    protected $fillable = ['order_item_id', 'ingredient', 'action', 'extra_price_pence'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
