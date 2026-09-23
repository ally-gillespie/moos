<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu Management — Moo's</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-100 min-h-screen">
<div class="max-w-5xl mx-auto p-6">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Menu Management</h1>
        <a href="{{ route('kitchen.index') }}" class="text-sm underline text-neutral-600">Back to kitchen</a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- Section 1: Menu Items --}}
    {{-- ============================================================ --}}
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
                    <tr class="hover:bg-neutral-50">
                        <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                        <td class="px-4 py-3">
                            @if($item->category === 'burger')
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-neutral-200 text-neutral-700">burger</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">side</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">£{{ number_format($item->price_pence / 100, 2) }}</td>
                        <td class="px-4 py-3 text-neutral-500">{{ $item->sort_order }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('menu.items.update', $item) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="name" value="{{ $item->name }}">
                                <input type="hidden" name="price" value="{{ number_format($item->price_pence / 100, 2) }}">
                                <input type="hidden" name="sort_order" value="{{ $item->sort_order }}">
                                <input type="checkbox" name="active" value="1"
                                    {{ $item->active ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                    class="rounded">
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <details class="inline-block">
                                <summary class="cursor-pointer text-neutral-600 underline text-xs">Edit</summary>
                                <div class="absolute z-10 mt-1 bg-white border rounded-lg shadow-lg p-4 w-72">
                                    <form method="POST" action="{{ route('menu.items.update', $item) }}" class="space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-xs text-neutral-500 mb-0.5">Name</label>
                                            <input type="text" name="name" value="{{ $item->name }}"
                                                class="w-full border rounded px-2 py-1 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-neutral-500 mb-0.5">Price (£)</label>
                                            <input type="number" name="price" value="{{ number_format($item->price_pence / 100, 2) }}"
                                                step="0.01" min="0"
                                                class="w-full border rounded px-2 py-1 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-neutral-500 mb-0.5">Sort order</label>
                                            <input type="number" name="sort_order" value="{{ $item->sort_order }}"
                                                min="0"
                                                class="w-full border rounded px-2 py-1 text-sm">
                                        </div>
                                        <button type="submit"
                                            class="w-full bg-black text-white text-sm py-1.5 rounded">Save</button>
                                    </form>
                                </div>
                            </details>
                            <form method="POST" action="{{ route('menu.items.destroy', $item) }}" class="inline"
                                onsubmit="return confirm('Delete {{ addslashes($item->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                    {{-- Extras for side items --}}
                    @if($item->category === 'side')
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
                                    <form method="POST" action="{{ route('menu.extras.destroy', $extra) }}"
                                        onsubmit="return confirm('Delete {{ addslashes($extra->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 text-xs underline">Remove</button>
                                    </form>
                                </div>
                                @endforeach
                                <form method="POST" action="{{ route('menu.extras.store', $item) }}" class="flex items-center gap-2 mt-2">
                                    @csrf
                                    <input type="text" name="name" placeholder="Extra name"
                                        class="border rounded px-2 py-1 text-xs w-36">
                                    <span class="text-xs text-neutral-400">£</span>
                                    <input type="number" name="price" placeholder="0.00" step="0.01" min="0"
                                        class="border rounded px-2 py-1 text-xs w-20">
                                    <button type="submit" class="bg-neutral-800 text-white text-xs px-3 py-1 rounded">Add</button>
                                </form>
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
            <form method="POST" action="{{ route('menu.items.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Category</label>
                    <select name="category" class="border rounded px-2 py-1.5 text-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->key }}">{{ $cat->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Name</label>
                    <input type="text" name="name" placeholder="e.g. Spicy Burger" required
                        class="border rounded px-2 py-1.5 text-sm w-44">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Price (£)</label>
                    <input type="number" name="price" placeholder="0.00" step="0.01" min="0" required
                        class="border rounded px-2 py-1.5 text-sm w-24">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Sort order</label>
                    <input type="number" name="sort_order" placeholder="0" min="0" value="0"
                        class="border rounded px-2 py-1.5 text-sm w-20">
                </div>
                <button type="submit" class="bg-black text-white text-sm px-4 py-1.5 rounded">Add Item</button>
            </form>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Section 2: Categories --}}
    {{-- ============================================================ --}}
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
                            <form method="POST" action="{{ route('menu.categories.destroy', $cat) }}"
                                onsubmit="return confirm('Delete category {{ addslashes($cat->label) }}? All items must be removed first.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 bg-white rounded-lg border shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Add Category</h3>
            <form method="POST" action="{{ route('menu.categories.store') }}" class="flex items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Label</label>
                    <input type="text" name="label" placeholder="e.g. Drinks" required
                        class="border rounded px-2 py-1.5 text-sm w-40">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Sort order</label>
                    <input type="number" name="sort_order" placeholder="0" min="0"
                        class="border rounded px-2 py-1.5 text-sm w-20">
                </div>
                <button class="bg-black text-white text-sm px-4 py-1.5 rounded">Add Category</button>
            </form>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Section 3: Ingredients --}}
    {{-- ============================================================ --}}
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
                        <form method="POST" action="{{ route('menu.ingredients.destroy', $ingredient) }}"
                            onsubmit="return confirm('Remove {{ addslashes($ingredient->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 text-xs underline">Remove</button>
                        </form>
                    </li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('menu.ingredients.store') }}" class="flex gap-2">
                    @csrf
                    <input type="hidden" name="type" value="standard">
                    <input type="text" name="name" placeholder="Ingredient name" required
                        class="border rounded px-2 py-1 text-sm flex-1">
                    <button type="submit" class="bg-black text-white text-sm px-3 py-1 rounded">Add</button>
                </form>
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
                            <form method="POST" action="{{ route('menu.ingredients.destroy', $ingredient) }}"
                                onsubmit="return confirm('Remove {{ addslashes($ingredient->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 text-xs underline">Remove</button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('menu.ingredients.store') }}" class="flex gap-2">
                    @csrf
                    <input type="hidden" name="type" value="paid">
                    <input type="text" name="name" placeholder="Extra name" required
                        class="border rounded px-2 py-1 text-sm flex-1">
                    <span class="self-center text-neutral-400 text-sm">£</span>
                    <input type="number" name="price" placeholder="0.00" step="0.01" min="0"
                        class="border rounded px-2 py-1 text-sm w-20">
                    <button type="submit" class="bg-black text-white text-sm px-3 py-1 rounded">Add</button>
                </form>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Section 3: Meal Deals --}}
    {{-- ============================================================ --}}
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
                            <form method="POST" action="{{ route('menu.deals.destroy', $deal) }}"
                                onsubmit="return confirm('Delete this deal?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @if($deals->isEmpty())
                    <tr><td colspan="4" class="px-4 py-3 text-neutral-400 text-center">No meal deals yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Add deal form --}}
        <div class="mt-4 bg-white rounded-lg border shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Add Meal Deal</h3>
            <form method="POST" action="{{ route('menu.deals.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Label</label>
                    <input type="text" name="label" placeholder="e.g. Meal Deal" required
                        class="border rounded px-2 py-1.5 text-sm w-40">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Side item</label>
                    <select name="side_menu_item_id" required class="border rounded px-2 py-1.5 text-sm">
                        <option value="">Select side...</option>
                        @foreach($sideItems as $side)
                            <option value="{{ $side->id }}">{{ $side->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Discount (£)</label>
                    <input type="number" name="discount" placeholder="1.00" step="0.01" min="0" required
                        class="border rounded px-2 py-1.5 text-sm w-24">
                </div>
                <button type="submit" class="bg-black text-white text-sm px-4 py-1.5 rounded">Add Deal</button>
            </form>
        </div>
    </section>

</div>
</body>
</html>
