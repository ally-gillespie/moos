<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MenuIngredient extends Model
{
    protected $fillable = [
        'name',
        'type',
        'price_pence',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }
}
