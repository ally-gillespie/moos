<?php

namespace App\Livewire;

use App\Models\MealDeal;
use App\Models\MenuCategory;
use App\Models\MenuIngredient;
use App\Models\MenuItem;
use App\Models\MenuItemExtra;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title("Menu Management — Moo's")]
class MenuManager extends Component
{
    // Category form
    public string $newCategoryLabel = '';
    public int $newCategorySortOrder = 0;

    // Item form
    public string $newItemCategory = '';
    public string $newItemName = '';
    public string $newItemPrice = '';
    public int $newItemSortOrder = 0;

    // Ingredient form
    public string $newIngredientName = '';
    public string $newIngredientType = 'standard';
    public string $newIngredientPrice = '';

    // Deal form
    public string $newDealLabel = '';
    public string $newDealSideId = '';
    public string $newDealDiscount = '';

    // Extra form
    public ?int $newExtraItemId = null;
    public string $newExtraName = '';
    public string $newExtraPrice = '';

    // Inline edit
    public ?int $editItem = null;
    public array $editValues = [];

    // Flash message
    public string $flash = '';

    public function storeCategory(): void
    {
        $this->validate([
            'newCategoryLabel' => 'required|string|max:100',
        ]);

        MenuCategory::create([
            'key'        => Str::slug($this->newCategoryLabel, '_'),
            'label'      => $this->newCategoryLabel,
            'sort_order' => $this->newCategorySortOrder ?: MenuCategory::max('sort_order') + 1,
        ]);

        $this->newCategoryLabel = '';
        $this->newCategorySortOrder = 0;
        $this->flash = 'Category added.';
    }

    public function destroyCategory(int $id): void
    {
        $category = MenuCategory::findOrFail($id);
        abort_if($category->items()->exists(), 422, 'Remove all items in this category first.');
        $category->delete();
        $this->flash = 'Category deleted.';
    }

    public function storeItem(): void
    {
        $validKeys = MenuCategory::pluck('key')->toArray();

        $this->validate([
            'newItemName'     => 'required|string|max:100',
            'newItemPrice'    => 'required|numeric|min:0',
            'newItemCategory' => 'required|string|in:' . implode(',', $validKeys),
        ]);

        MenuItem::create([
            'category'    => $this->newItemCategory,
            'key'         => Str::slug($this->newItemName, '_'),
            'name'        => $this->newItemName,
            'price_pence' => (int) round((float) $this->newItemPrice * 100),
            'sort_order'  => $this->newItemSortOrder,
            'active'      => true,
        ]);

        $this->newItemName = '';
        $this->newItemPrice = '';
        $this->newItemSortOrder = 0;
        $this->flash = 'Item added.';
    }

    public function startEdit(int $itemId): void
    {
        $item = MenuItem::findOrFail($itemId);
        $this->editItem = $itemId;
        $this->editValues = [
            'name'       => $item->name,
            'price'      => number_format($item->price_pence / 100, 2),
            'sort_order' => $item->sort_order,
            'active'     => $item->active,
        ];
    }

    public function cancelEdit(): void
    {
        $this->editItem = null;
        $this->editValues = [];
    }

    public function updateItem(int $id): void
    {
        $this->validate([
            'editValues.name'       => 'required|string|max:100',
            'editValues.price'      => 'required|numeric|min:0',
            'editValues.sort_order' => 'integer|min:0',
        ]);

        $item = MenuItem::findOrFail($id);
        $item->update([
            'name'        => $this->editValues['name'],
            'price_pence' => (int) round((float) $this->editValues['price'] * 100),
            'sort_order'  => $this->editValues['sort_order'] ?? $item->sort_order,
            'active'      => $this->editValues['active'] ?? false,
        ]);

        $this->editItem = null;
        $this->editValues = [];
        $this->flash = 'Item updated.';
    }

    public function toggleItemActive(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['active' => ! $item->active]);
    }

    public function destroyItem(int $id): void
    {
        MenuItem::findOrFail($id)->delete();
        $this->flash = 'Item deleted.';
    }

    public function storeExtra(int $itemId): void
    {
        $this->validate([
            'newExtraName'  => 'required|string|max:100',
            'newExtraPrice' => 'nullable|numeric|min:0',
        ]);

        $item = MenuItem::findOrFail($itemId);
        $item->extras()->create([
            'key'         => Str::slug($this->newExtraName, '_'),
            'name'        => $this->newExtraName,
            'price_pence' => $this->newExtraPrice !== '' ? (int) round((float) $this->newExtraPrice * 100) : 0,
        ]);

        $this->newExtraName = '';
        $this->newExtraPrice = '';
        $this->newExtraItemId = null;
        $this->flash = 'Extra added.';
    }

    public function destroyExtra(int $id): void
    {
        MenuItemExtra::findOrFail($id)->delete();
        $this->flash = 'Extra deleted.';
    }

    public function storeIngredient(): void
    {
        $this->validate([
            'newIngredientName'  => 'required|string|max:100',
            'newIngredientType'  => 'required|in:standard,paid',
            'newIngredientPrice' => 'nullable|numeric|min:0',
        ]);

        MenuIngredient::create([
            'name'        => $this->newIngredientName,
            'type'        => $this->newIngredientType,
            'price_pence' => $this->newIngredientPrice !== '' ? (int) round((float) $this->newIngredientPrice * 100) : 0,
            'sort_order'  => MenuIngredient::where('type', $this->newIngredientType)->max('sort_order') + 1,
            'active'      => true,
        ]);

        $this->newIngredientName = '';
        $this->newIngredientPrice = '';
        $this->flash = 'Ingredient added.';
    }

    public function storeStandardIngredient(): void
    {
        $this->newIngredientType = 'standard';
        $this->storeIngredient();
    }

    public function storePaidIngredient(): void
    {
        $this->newIngredientType = 'paid';
        $this->storeIngredient();
    }

    public function destroyIngredient(int $id): void
    {
        MenuIngredient::findOrFail($id)->delete();
        $this->flash = 'Ingredient deleted.';
    }

    public function storeDeal(): void
    {
        $this->validate([
            'newDealLabel'    => 'required|string|max:100',
            'newDealSideId'   => 'required|exists:menu_items,id',
            'newDealDiscount' => 'required|numeric|min:0',
        ]);

        MealDeal::create([
            'label'             => $this->newDealLabel,
            'side_menu_item_id' => (int) $this->newDealSideId,
            'discount_pence'    => (int) round((float) $this->newDealDiscount * 100),
        ]);

        $this->newDealLabel = '';
        $this->newDealSideId = '';
        $this->newDealDiscount = '';
        $this->flash = 'Meal deal added.';
    }

    public function destroyDeal(int $id): void
    {
        MealDeal::findOrFail($id)->delete();
        $this->flash = 'Meal deal deleted.';
    }

    public function render()
    {
        return view('livewire.menu-manager', [
            'items'       => MenuItem::with('extras')->orderBy('category')->orderBy('sort_order')->get(),
            'categories'  => MenuCategory::orderBy('sort_order')->get(),
            'ingredients' => MenuIngredient::orderBy('type')->orderBy('sort_order')->get(),
            'deals'       => MealDeal::with('side')->get(),
            'sideItems'   => MenuItem::active()->category('side')->orderBy('sort_order')->get(),
        ]);
    }
}
