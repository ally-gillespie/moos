<div class="max-w-2xl mx-auto p-4">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Menu</h1>
        <a wire:navigate href="{{ route('kitchen.index') }}" class="text-sm underline text-neutral-600">Back to kitchen</a>
    </div>

    @if($flash)
        <div class="mb-4 px-4 py-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            {{ $flash }}
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- Section 1: Menu Items --}}
    {{-- ================================================================ --}}
    <section class="mb-8">
        <h2 class="text-base font-semibold mb-3">Menu Items</h2>

        <div class="space-y-2">
            @foreach($items as $item)
            <div class="bg-white rounded-lg border p-4">

                @if($editItem === $item->id)
                    {{-- Edit mode --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-neutral-500 mb-0.5">Name</label>
                            <input type="text" wire:model="editValues.name"
                                class="border rounded-lg px-3 py-2 text-sm w-full">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-neutral-500 mb-0.5">Price (£)</label>
                                <input type="number" wire:model="editValues.price" step="0.01" min="0"
                                    class="border rounded-lg px-3 py-2 text-sm w-full">
                            </div>
                            <div>
                                <label class="block text-xs text-neutral-500 mb-0.5">Sort order</label>
                                <input type="number" wire:model="editValues.sort_order" min="0"
                                    class="border rounded-lg px-3 py-2 text-sm w-full">
                            </div>
                        </div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="editValues.active" class="rounded">
                            Active
                        </label>
                        <div class="flex gap-3 pt-1">
                            <button type="button" wire:click="updateItem({{ $item->id }})"
                                class="flex-1 bg-black text-white text-sm py-2 rounded-lg">Save</button>
                            <button type="button" wire:click="cancelEdit"
                                class="flex-1 border text-sm py-2 rounded-lg text-neutral-600">Cancel</button>
                        </div>
                    </div>

                @else
                    {{-- View mode --}}
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0">
                            <div class="font-medium text-sm truncate">{{ $item->name }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs {{ $item->category === 'burger' ? 'bg-neutral-200 text-neutral-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $item->category }}
                                </span>
                                <span class="text-sm text-neutral-600">£{{ number_format($item->price_pence / 100, 2) }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <input type="checkbox"
                                wire:click="toggleItemActive({{ $item->id }})"
                                {{ $item->active ? 'checked' : '' }}
                                class="rounded w-5 h-5">
                            <button type="button" wire:click="startEdit({{ $item->id }})"
                                class="text-sm text-neutral-600 underline">Edit</button>
                            <button type="button"
                                wire:click="destroyItem({{ $item->id }})"
                                wire:confirm="Delete {{ addslashes($item->name) }}?"
                                class="text-red-600 text-sm underline">Del</button>
                        </div>
                    </div>

                    {{-- Extras for non-burger items --}}
                    @if($item->category !== 'burger')
                        <div class="mt-3 pt-3 border-t">
                            <div class="text-xs text-neutral-400 uppercase tracking-wide mb-2">Extras</div>
                            <div class="space-y-1.5">
                                @foreach($item->extras as $extra)
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ $extra->name }}
                                        @if($extra->price_pence > 0)
                                            <span class="text-neutral-400 text-xs">+£{{ number_format($extra->price_pence / 100, 2) }}</span>
                                        @else
                                            <span class="text-neutral-400 text-xs">free</span>
                                        @endif
                                    </span>
                                    <button type="button"
                                        wire:click="destroyExtra({{ $extra->id }})"
                                        wire:confirm="Delete {{ addslashes($extra->name) }}?"
                                        class="text-red-500 text-xs underline">Remove</button>
                                </div>
                                @endforeach
                            </div>

                            @if($newExtraItemId === $item->id)
                                <div class="mt-2 space-y-2">
                                    <input type="text" wire:model="newExtraName" placeholder="Extra name"
                                        class="border rounded-lg px-3 py-2 text-sm w-full">
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm">£</span>
                                            <input type="number" wire:model="newExtraPrice" placeholder="0.00" step="0.01" min="0"
                                                class="border rounded-lg pl-7 pr-3 py-2 text-sm w-full">
                                        </div>
                                        <button type="button" wire:click="storeExtra({{ $item->id }})"
                                            class="bg-black text-white text-sm px-4 py-2 rounded-lg">Add</button>
                                        <button type="button" wire:click="$set('newExtraItemId', null)"
                                            class="border text-sm px-3 py-2 rounded-lg text-neutral-600">✕</button>
                                    </div>
                                </div>
                            @else
                                <button type="button" wire:click="$set('newExtraItemId', {{ $item->id }})"
                                    class="mt-2 text-xs text-neutral-500 underline">+ Add extra</button>
                            @endif
                        </div>
                    @endif
                @endif

            </div>
            @endforeach
        </div>

        {{-- Add item form --}}
        <div class="mt-3 bg-white rounded-lg border p-4">
            <h3 class="text-sm font-semibold mb-3">Add Item</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Category</label>
                    <select wire:model="newItemCategory" class="border rounded-lg px-3 py-2 text-sm w-full">
                        <option value="">Select...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->key }}">{{ $cat->label }}</option>
                        @endforeach
                    </select>
                    @error('newItemCategory') <p class="mt-0.5 text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Name</label>
                    <input type="text" wire:model="newItemName" placeholder="e.g. Spicy Burger"
                        class="border rounded-lg px-3 py-2 text-sm w-full">
                    @error('newItemName') <p class="mt-0.5 text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-neutral-500 mb-0.5">Price (£)</label>
                        <input type="number" wire:model="newItemPrice" placeholder="0.00" step="0.01" min="0"
                            class="border rounded-lg px-3 py-2 text-sm w-full">
                        @error('newItemPrice') <p class="mt-0.5 text-red-600 text-xs">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-neutral-500 mb-0.5">Sort order</label>
                        <input type="number" wire:model="newItemSortOrder" placeholder="0" min="0"
                            class="border rounded-lg px-3 py-2 text-sm w-full">
                    </div>
                </div>
                <button type="button" wire:click="storeItem"
                    class="w-full bg-black text-white text-sm py-2.5 rounded-lg">Add Item</button>
            </div>
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- Section 2: Categories --}}
    {{-- ================================================================ --}}
    <section class="mb-8">
        <h2 class="text-base font-semibold mb-3">Categories</h2>

        <div class="bg-white rounded-lg border divide-y">
            @foreach($categories as $cat)
            <div class="flex items-center justify-between px-4 py-3">
                <div>
                    <span class="text-sm font-medium">{{ $cat->label }}</span>
                    <span class="ml-2 text-xs text-neutral-400 font-mono">{{ $cat->key }}</span>
                </div>
                <button type="button"
                    wire:click="destroyCategory({{ $cat->id }})"
                    wire:confirm="Delete category {{ addslashes($cat->label) }}? All items must be removed first."
                    class="text-red-600 text-sm underline">Delete</button>
            </div>
            @endforeach
        </div>

        <div class="mt-3 bg-white rounded-lg border p-4">
            <h3 class="text-sm font-semibold mb-3">Add Category</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Label</label>
                    <input type="text" wire:model="newCategoryLabel" placeholder="e.g. Drinks"
                        class="border rounded-lg px-3 py-2 text-sm w-full">
                    @error('newCategoryLabel') <p class="mt-0.5 text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Sort order</label>
                    <input type="number" wire:model="newCategorySortOrder" placeholder="0" min="0"
                        class="border rounded-lg px-3 py-2 text-sm w-full">
                </div>
                <button type="button" wire:click="storeCategory"
                    class="w-full bg-black text-white text-sm py-2.5 rounded-lg">Add Category</button>
            </div>
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- Section 3: Ingredients --}}
    {{-- ================================================================ --}}
    <section class="mb-8">
        <h2 class="text-base font-semibold mb-3">Burger Ingredients</h2>

        <div class="space-y-4">
            {{-- Standard --}}
            <div class="bg-white rounded-lg border p-4">
                <h3 class="text-sm font-semibold mb-3">Standard (removable, free)</h3>
                <div class="space-y-2 mb-3">
                    @foreach($ingredients->where('type', 'standard') as $ingredient)
                    <div class="flex items-center justify-between text-sm">
                        <span>{{ $ingredient->name }}</span>
                        <button type="button"
                            wire:click="destroyIngredient({{ $ingredient->id }})"
                            wire:confirm="Remove {{ addslashes($ingredient->name) }}?"
                            class="text-red-500 text-sm underline">Remove</button>
                    </div>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <input type="text" wire:model="newIngredientName" placeholder="Ingredient name"
                        class="border rounded-lg px-3 py-2 text-sm flex-1">
                    <button type="button" wire:click="storeStandardIngredient"
                        class="bg-black text-white text-sm px-4 py-2 rounded-lg">Add</button>
                </div>
                @error('newIngredientName') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- Paid extras --}}
            <div class="bg-white rounded-lg border p-4">
                <h3 class="text-sm font-semibold mb-3">Paid extras (add-ons)</h3>
                <div class="space-y-2 mb-3">
                    @foreach($ingredients->where('type', 'paid') as $ingredient)
                    <div class="flex items-center justify-between text-sm">
                        <span>{{ $ingredient->name }} <span class="text-neutral-400 text-xs">£{{ number_format($ingredient->price_pence / 100, 2) }}</span></span>
                        <button type="button"
                            wire:click="destroyIngredient({{ $ingredient->id }})"
                            wire:confirm="Remove {{ addslashes($ingredient->name) }}?"
                            class="text-red-500 text-sm underline">Remove</button>
                    </div>
                    @endforeach
                </div>
                <div class="space-y-2">
                    <input type="text" wire:model="newIngredientName" placeholder="Extra name"
                        class="border rounded-lg px-3 py-2 text-sm w-full">
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm">£</span>
                            <input type="number" wire:model="newIngredientPrice" placeholder="0.00" step="0.01" min="0"
                                class="border rounded-lg pl-7 pr-3 py-2 text-sm w-full">
                        </div>
                        <button type="button" wire:click="storePaidIngredient"
                            class="bg-black text-white text-sm px-4 py-2 rounded-lg">Add</button>
                    </div>
                </div>
                @error('newIngredientName') <p class="mt-1 text-red-600 text-xs">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- Section 4: Meal Deals --}}
    {{-- ================================================================ --}}
    <section class="mb-8">
        <h2 class="text-base font-semibold mb-3">Meal Deals</h2>

        <div class="bg-white rounded-lg border divide-y">
            @forelse($deals as $deal)
            <div class="flex items-center justify-between px-4 py-3">
                <div>
                    <div class="text-sm font-medium">{{ $deal->label }}</div>
                    <div class="text-xs text-neutral-500 mt-0.5">{{ $deal->side?->name ?? '(deleted side)' }} · £{{ number_format($deal->discount_pence / 100, 2) }} off</div>
                </div>
                <button type="button"
                    wire:click="destroyDeal({{ $deal->id }})"
                    wire:confirm="Delete this deal?"
                    class="text-red-600 text-sm underline">Delete</button>
            </div>
            @empty
            <div class="px-4 py-3 text-neutral-400 text-sm text-center">No meal deals yet.</div>
            @endforelse
        </div>

        <div class="mt-3 bg-white rounded-lg border p-4">
            <h3 class="text-sm font-semibold mb-3">Add Meal Deal</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Label</label>
                    <input type="text" wire:model="newDealLabel" placeholder="e.g. Meal Deal"
                        class="border rounded-lg px-3 py-2 text-sm w-full">
                    @error('newDealLabel') <p class="mt-0.5 text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Side item</label>
                    <select wire:model="newDealSideId" class="border rounded-lg px-3 py-2 text-sm w-full">
                        <option value="">Select side...</option>
                        @foreach($sideItems as $side)
                            <option value="{{ $side->id }}">{{ $side->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Discount (£)</label>
                    <input type="number" wire:model="newDealDiscount" placeholder="1.00" step="0.01" min="0"
                        class="border rounded-lg px-3 py-2 text-sm w-full">
                </div>
                <button type="button" wire:click="storeDeal"
                    class="w-full bg-black text-white text-sm py-2.5 rounded-lg">Add Deal</button>
            </div>
        </div>
    </section>

</div>
