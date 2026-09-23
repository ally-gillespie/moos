<div class="max-w-5xl mx-auto p-6">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Menu Management</h1>
        <a href="{{ route('kitchen.index') }}" class="text-sm underline text-neutral-600">Back to kitchen</a>
    </div>

    @if($flash)
        <div class="mb-4 px-4 py-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded">
            {{ $flash }}
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- Section 1: Menu Items --}}
    {{-- ================================================================ --}}
    <section id="items" class="mb-10">
        <h2 class="text-lg font-semibold mb-4">Menu Items</h2>

        <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Name</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Category</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Price</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Order</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Active</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($items as $item)
                    <tr class="hover:bg-neutral-50 align-top">
                        <td class="px-4 py-3 font-medium">
                            @if($editItem === $item->id)
                                <input type="text" wire:model="editValues.name"
                                    class="border rounded px-2 py-1 text-sm w-full">
                            @else
                                {{ $item->name }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($item->category === 'burger')
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-neutral-200 text-neutral-700">burger</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ $item->category }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($editItem === $item->id)
                                <input type="number" wire:model="editValues.price" step="0.01" min="0"
                                    class="border rounded px-2 py-1 text-sm w-24">
                            @else
                                £{{ number_format($item->price_pence / 100, 2) }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-neutral-500">
                            @if($editItem === $item->id)
                                <input type="number" wire:model="editValues.sort_order" min="0"
                                    class="border rounded px-2 py-1 text-sm w-16">
                            @else
                                {{ $item->sort_order }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($editItem === $item->id)
                                <input type="checkbox" wire:model="editValues.active" class="rounded"
                                    {{ $this->editValues['active'] ? 'checked' : '' }}>
                            @else
                                <input type="checkbox"
                                    wire:click="toggleItemActive({{ $item->id }})"
                                    {{ $item->active ? 'checked' : '' }}
                                    class="rounded">
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            @if($editItem === $item->id)
                                <button type="button" wire:click="updateItem({{ $item->id }})"
                                    class="text-xs bg-black text-white px-2 py-1 rounded">Save</button>
                                <button type="button" wire:click="cancelEdit"
                                    class="text-xs text-neutral-500 underline">Cancel</button>
                            @else
                                <button type="button" wire:click="startEdit({{ $item->id }})"
                                    class="text-xs text-neutral-600 underline">Edit</button>
                                <button type="button"
                                    wire:click="destroyItem({{ $item->id }})"
                                    wire:confirm="Delete {{ addslashes($item->name) }}?"
                                    class="text-red-600 underline text-xs">Delete</button>
                            @endif
                        </td>
                    </tr>

                    {{-- Extras for side/non-burger items --}}
                    @if($item->category !== 'burger')
                    <tr class="bg-blue-50">
                        <td colspan="6" class="px-8 py-3">
                            <div class="text-xs text-neutral-500 mb-2 font-medium uppercase tracking-wide">Extras</div>
                            <div class="space-y-1">
                                @foreach($item->extras as $extra)
                                <div class="flex items-center gap-4">
                                    <span class="text-sm">{{ $extra->name }}</span>
                                    @if($extra->price_pence > 0)
                                        <span class="text-xs text-neutral-500">+£{{ number_format($extra->price_pence / 100, 2) }}</span>
                                    @else
                                        <span class="text-xs text-neutral-400">free</span>
                                    @endif
                                    <button type="button"
                                        wire:click="destroyExtra({{ $extra->id }})"
                                        wire:confirm="Delete {{ addslashes($extra->name) }}?"
                                        class="text-red-500 text-xs underline">Remove</button>
                                </div>
                                @endforeach

                                {{-- Add extra inline form --}}
                                @if($newExtraItemId === $item->id)
                                    <div class="flex items-center gap-2 mt-2">
                                        <input type="text" wire:model="newExtraName" placeholder="Extra name"
                                            class="border rounded px-2 py-1 text-xs w-36">
                                        <span class="text-xs text-neutral-400">£</span>
                                        <input type="number" wire:model="newExtraPrice" placeholder="0.00" step="0.01" min="0"
                                            class="border rounded px-2 py-1 text-xs w-20">
                                        <button type="button" wire:click="storeExtra({{ $item->id }})"
                                            class="bg-neutral-800 text-white text-xs px-3 py-1 rounded">Add</button>
                                        <button type="button" wire:click="$set('newExtraItemId', null)"
                                            class="text-xs text-neutral-500 underline">Cancel</button>
                                    </div>
                                @else
                                    <button type="button" wire:click="$set('newExtraItemId', {{ $item->id }})"
                                        class="text-xs text-neutral-500 underline mt-1">+ Add extra</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif

                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Add item form --}}
        <div class="mt-4 bg-white rounded-lg border shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Add Item</h3>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Category</label>
                    <select wire:model="newItemCategory" class="border rounded px-2 py-1.5 text-sm">
                        <option value="">Select...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->key }}">{{ $cat->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Name</label>
                    <input type="text" wire:model="newItemName" placeholder="e.g. Spicy Burger"
                        class="border rounded px-2 py-1.5 text-sm w-44">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Price (£)</label>
                    <input type="number" wire:model="newItemPrice" placeholder="0.00" step="0.01" min="0"
                        class="border rounded px-2 py-1.5 text-sm w-24">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Sort order</label>
                    <input type="number" wire:model="newItemSortOrder" placeholder="0" min="0"
                        class="border rounded px-2 py-1.5 text-sm w-20">
                </div>
                <button type="button" wire:click="storeItem"
                    class="bg-black text-white text-sm px-4 py-1.5 rounded">Add Item</button>
            </div>
            @error('newItemName') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            @error('newItemPrice') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            @error('newItemCategory') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- Section 2: Categories --}}
    {{-- ================================================================ --}}
    <section id="categories" class="mb-10">
        <h2 class="text-lg font-semibold mb-4">Item Categories</h2>

        <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Label</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Key</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Order</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($categories as $cat)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-4 py-3 font-medium">{{ $cat->label }}</td>
                        <td class="px-4 py-3 text-neutral-400 font-mono text-xs">{{ $cat->key }}</td>
                        <td class="px-4 py-3 text-neutral-500">{{ $cat->sort_order }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                wire:click="destroyCategory({{ $cat->id }})"
                                wire:confirm="Delete category {{ addslashes($cat->label) }}? All items must be removed first."
                                class="text-red-600 underline text-xs">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 bg-white rounded-lg border shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Add Category</h3>
            <div class="flex items-end gap-3">
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Label</label>
                    <input type="text" wire:model="newCategoryLabel" placeholder="e.g. Drinks"
                        class="border rounded px-2 py-1.5 text-sm w-40">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Sort order</label>
                    <input type="number" wire:model="newCategorySortOrder" placeholder="0" min="0"
                        class="border rounded px-2 py-1.5 text-sm w-20">
                </div>
                <button type="button" wire:click="storeCategory"
                    class="bg-black text-white text-sm px-4 py-1.5 rounded">Add Category</button>
            </div>
            @error('newCategoryLabel') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- Section 3: Ingredients --}}
    {{-- ================================================================ --}}
    <section id="ingredients" class="mb-10">
        <h2 class="text-lg font-semibold mb-4">Ingredients</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Standard --}}
            <div class="bg-white rounded-lg border shadow-sm p-4">
                <h3 class="text-sm font-medium mb-3">Standard (removable, free)</h3>
                <ul class="space-y-1 mb-4">
                    @foreach($ingredients->where('type', 'standard') as $ingredient)
                    <li class="flex items-center justify-between text-sm">
                        <span>{{ $ingredient->name }}</span>
                        <button type="button"
                            wire:click="destroyIngredient({{ $ingredient->id }})"
                            wire:confirm="Remove {{ addslashes($ingredient->name) }}?"
                            class="text-red-500 text-xs underline">Remove</button>
                    </li>
                    @endforeach
                </ul>
                <div class="flex gap-2">
                    <input type="text" wire:model="newIngredientName" placeholder="Ingredient name"
                        class="border rounded px-2 py-1 text-sm flex-1">
                    <button type="button" wire:click="storeStandardIngredient"
                        class="bg-black text-white text-sm px-3 py-1 rounded">Add</button>
                </div>
                @error('newIngredientName') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- Paid extras --}}
            <div class="bg-white rounded-lg border shadow-sm p-4">
                <h3 class="text-sm font-medium mb-3">Paid extras (add-ons)</h3>
                <ul class="space-y-1 mb-4">
                    @foreach($ingredients->where('type', 'paid') as $ingredient)
                    <li class="flex items-center justify-between text-sm">
                        <span>{{ $ingredient->name }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-neutral-500 text-xs">£{{ number_format($ingredient->price_pence / 100, 2) }}</span>
                            <button type="button"
                                wire:click="destroyIngredient({{ $ingredient->id }})"
                                wire:confirm="Remove {{ addslashes($ingredient->name) }}?"
                                class="text-red-500 text-xs underline">Remove</button>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <div class="flex gap-2 items-center">
                    <input type="text" wire:model="newIngredientName" placeholder="Extra name"
                        class="border rounded px-2 py-1 text-sm flex-1">
                    <span class="self-center text-neutral-400 text-sm">£</span>
                    <input type="number" wire:model="newIngredientPrice" placeholder="0.00" step="0.01" min="0"
                        class="border rounded px-2 py-1 text-sm w-20">
                    <button type="button" wire:click="storePaidIngredient"
                        class="bg-black text-white text-sm px-3 py-1 rounded">Add</button>
                </div>
                @error('newIngredientName') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- Section 4: Meal Deals --}}
    {{-- ================================================================ --}}
    <section id="deals" class="mb-10">
        <h2 class="text-lg font-semibold mb-4">Meal Deals</h2>

        <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Label</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Side item</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Discount</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($deals as $deal)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-4 py-3">{{ $deal->label }}</td>
                        <td class="px-4 py-3">{{ $deal->side?->name ?? '(deleted side)' }}</td>
                        <td class="px-4 py-3">£{{ number_format($deal->discount_pence / 100, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                wire:click="destroyDeal({{ $deal->id }})"
                                wire:confirm="Delete this deal?"
                                class="text-red-600 underline text-xs">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                    @if($deals->isEmpty())
                    <tr><td colspan="4" class="px-4 py-3 text-neutral-400 text-center">No meal deals yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="mt-4 bg-white rounded-lg border shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Add Meal Deal</h3>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Label</label>
                    <input type="text" wire:model="newDealLabel" placeholder="e.g. Meal Deal"
                        class="border rounded px-2 py-1.5 text-sm w-40">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Side item</label>
                    <select wire:model="newDealSideId" class="border rounded px-2 py-1.5 text-sm">
                        <option value="">Select side...</option>
                        @foreach($sideItems as $side)
                            <option value="{{ $side->id }}">{{ $side->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Discount (£)</label>
                    <input type="number" wire:model="newDealDiscount" placeholder="1.00" step="0.01" min="0"
                        class="border rounded px-2 py-1.5 text-sm w-24">
                </div>
                <button type="button" wire:click="storeDeal"
                    class="bg-black text-white text-sm px-4 py-1.5 rounded">Add Deal</button>
            </div>
            @error('newDealLabel') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
        </div>
    </section>

</div>
