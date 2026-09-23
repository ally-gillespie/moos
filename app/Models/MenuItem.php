<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MenuItem extends Model
{
    protected $fillable = [
        'category',
        'key',
        'name',
        'price_pence',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function extras(): HasMany
    {
        return $this->hasMany(MenuItemExtra::class);
    }

    public function mealDeals(): HasMany
    {
        return $this->hasMany(MealDeal::class, 'side_menu_item_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('active', true);
    }

    public function scopeCategory(Builder $q, string $cat): Builder
    {
        return $q->where('category', $cat);
    }
}
