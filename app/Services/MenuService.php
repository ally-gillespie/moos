<?php

namespace App\Services;

use App\Models\MealDeal;
use App\Models\MenuCategory;
use App\Models\MenuIngredient;
use App\Models\MenuItem;
use Illuminate\Support\Str;

class MenuService
{
    private function itemData(MenuItem $i): array
    {
        return [
            'key'         => $i->key,
            'name'        => $i->name,
            'price_pence' => $i->price_pence,
            'extras'      => $i->extras
                ->map(fn ($e) => ['key' => $e->key, 'name' => $e->name, 'price_pence' => $e->price_pence])
                ->toArray(),
        ];
    }

    public function getMenu(): array
    {
        $allItems = MenuItem::active()->orderBy('sort_order')->with('extras')->get();

        // Build dynamic categories for the order form optgroups
        $categories = MenuCategory::orderBy('sort_order')->get()->map(fn ($cat) => [
            'key'   => $cat->key,
            'label' => $cat->label,
            'items' => $allItems->where('category', $cat->key)->map(fn ($i) => $this->itemData($i))->values()->toArray(),
        ])->toArray();

        return [
            // Dynamic category list — used by order/POS views for optgroups
            'categories' => $categories,

            // Kept for backward compat (meal deal logic, BURGER_KEYS JS constant)
            'burgers' => $allItems->where('category', 'burger')
                ->map(fn ($i) => ['key' => $i->key, 'name' => $i->name, 'price_pence' => $i->price_pence])
                ->values()->toArray(),

            'sides' => $allItems->where('category', 'side')
                ->map(fn ($i) => $this->itemData($i))
                ->values()->toArray(),

            'standard_ingredients' => MenuIngredient::active()
                ->where('type', 'standard')
                ->orderBy('sort_order')
                ->pluck('name')
                ->toArray(),

            'paid_extras' => MenuIngredient::active()
                ->where('type', 'paid')
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($i) => [
                    'key'         => Str::slug($i->name, '_'),
                    'name'        => $i->name,
                    'price_pence' => $i->price_pence,
                ])
                ->toArray(),

            'meal_deals' => MealDeal::with('side')
                ->get()
                ->filter(fn ($d) => $d->side)
                ->map(fn ($d) => [
                    'label'          => $d->label,
                    'side_key'       => $d->side->key,
                    'discount_pence' => $d->discount_pence,
                ])
                ->values()
                ->toArray(),
        ];
    }
}
