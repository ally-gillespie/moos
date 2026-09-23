<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealDeal extends Model
{
    protected $fillable = [
        'label',
        'side_menu_item_id',
        'discount_pence',
    ];

    public function side(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'side_menu_item_id');
    }
}
