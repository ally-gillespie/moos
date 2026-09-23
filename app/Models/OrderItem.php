<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'item_name', 'unit_price_pence', 'quantity'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customizations()
    {
        return $this->hasMany(OrderItemCustomization::class);
    }
}
