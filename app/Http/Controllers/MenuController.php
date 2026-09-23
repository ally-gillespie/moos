<?php

namespace App\Http\Controllers;

use App\Models\MealDeal;
use App\Models\MenuCategory;
use App\Models\MenuIngredient;
use App\Models\MenuItem;
use App\Models\MenuItemExtra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('menu.index', [
            'items'       => MenuItem::with('extras')->orderBy('category')->orderBy('sort_order')->get(),
            'categories'  => MenuCategory::orderBy('sort_order')->get(),
            'ingredients' => MenuIngredient::orderBy('type')->orderBy('sort_order')->get(),
            'deals'       => MealDeal::with('side')->get(),
            'sideItems'   => MenuItem::active()->category('side')->orderBy('sort_order')->get(),
        ]);
    }

    public function storeCategory(Request $r): RedirectResponse
    {
        $v = $r->validate([
            'label'      => 'required|string|max:100',
            'sort_order' => 'integer|min:0',
        ]);

        MenuCategory::create([
            'key'        => Str::slug($v['label'], '_'),
            'label'      => $v['label'],
            'sort_order' => $v['sort_order'] ?? MenuCategory::max('sort_order') + 1,
        ]);

        return redirect()->route('menu.index')->with('success', 'Category added.');
    }

    public function destroyCategory(MenuCategory $category): RedirectResponse
    {
        abort_if($category->items()->exists(), 422, 'Remove all items in this category first.');

        $category->delete();

        return redirect()->route('menu.index')->with('success', 'Category deleted.');
    }

    public function storeItem(Request $r): RedirectResponse
    {
        $validKeys = MenuCategory::pluck('key')->toArray();

        $validated = $r->validate([
            'category'   => ['required', 'string', 'in:' . implode(',', $validKeys)],
            'name'       => 'required|string|max:100',
            'price'      => 'required|numeric|min:0',
            'sort_order' => 'integer|min:0',
            'active'     => 'boolean',
        ]);

        MenuItem::create([
            'category'    => $validated['category'],
            'key'         => Str::slug($validated['name'], '_'),
            'name'        => $validated['name'],
            'price_pence' => (int) round($validated['price'] * 100),
            'sort_order'  => $validated['sort_order'] ?? 0,
            'active'      => $validated['active'] ?? true,
        ]);

        return redirect()->back()->with('success', 'Item added.')->withFragment('items');
    }

    public function updateItem(Request $r, MenuItem $item): RedirectResponse
    {
        $validated = $r->validate([
            'name'       => 'required|string|max:100',
            'price'      => 'required|numeric|min:0',
            'sort_order' => 'integer|min:0',
            'active'     => 'boolean',
        ]);

        $item->update([
            'name'        => $validated['name'],
            'price_pence' => (int) round($validated['price'] * 100),
            'sort_order'  => $validated['sort_order'] ?? $item->sort_order,
            'active'      => isset($validated['active']) ? $validated['active'] : false,
        ]);

        return redirect()->back()->with('success', 'Item updated.');
    }

    public function destroyItem(MenuItem $item): RedirectResponse
    {
        $item->delete();

        return redirect()->back()->with('success', 'Item deleted.');
    }

    public function storeExtra(Request $r, MenuItem $item): RedirectResponse
    {
        $validated = $r->validate([
            'name'  => 'required|string|max:100',
            'price' => 'nullable|numeric|min:0',
        ]);

        $item->extras()->create([
            'key'         => Str::slug($validated['name'], '_'),
            'name'        => $validated['name'],
            'price_pence' => isset($validated['price']) ? (int) round($validated['price'] * 100) : 0,
        ]);

        return redirect()->back()->with('success', 'Extra added.')->withFragment('items');
    }

    public function destroyExtra(MenuItemExtra $extra): RedirectResponse
    {
        $extra->delete();

        return redirect()->back()->with('success', 'Extra deleted.');
    }

    public function storeIngredient(Request $r): RedirectResponse
    {
        $validated = $r->validate([
            'name'  => 'required|string|max:100',
            'type'  => 'required|in:standard,paid',
            'price' => 'nullable|numeric|min:0',
        ]);

        MenuIngredient::create([
            'name'        => $validated['name'],
            'type'        => $validated['type'],
            'price_pence' => isset($validated['price']) ? (int) round($validated['price'] * 100) : 0,
            'sort_order'  => MenuIngredient::where('type', $validated['type'])->max('sort_order') + 1,
            'active'      => true,
        ]);

        return redirect()->back()->with('success', 'Ingredient added.')->withFragment('ingredients');
    }

    public function destroyIngredient(MenuIngredient $ingredient): RedirectResponse
    {
        $ingredient->delete();

        return redirect()->back()->with('success', 'Ingredient deleted.');
    }

    public function storeDeal(Request $r): RedirectResponse
    {
        $validated = $r->validate([
            'label'              => 'required|string|max:100',
            'side_menu_item_id'  => 'required|exists:menu_items,id',
            'discount'           => 'required|numeric|min:0',
        ]);

        MealDeal::create([
            'label'             => $validated['label'],
            'side_menu_item_id' => $validated['side_menu_item_id'],
            'discount_pence'    => (int) round($validated['discount'] * 100),
        ]);

        return redirect()->back()->with('success', 'Meal deal added.')->withFragment('deals');
    }

    public function destroyDeal(MealDeal $deal): RedirectResponse
    {
        $deal->delete();

        return redirect()->back()->with('success', 'Meal deal deleted.');
    }
}
