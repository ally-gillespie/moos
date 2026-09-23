<div class="max-w-3xl mx-auto p-6">

    {{-- Nav --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">New Order</h1>
        <div class="flex items-center gap-4">
            @if(session('staff_role') === 'manager')
                <a href="{{ route('kitchen.index') }}" class="text-sm underline">Go to kitchen</a>
            @endif
            <form method="POST" action="{{ route('kitchen.logout') }}" class="flex items-center">
                @csrf
                <button class="text-sm text-neutral-500 underline">Log out ({{ session('staff_name') }})</button>
            </form>
        </div>
    </div>

    {{-- Confirmed order number banner --}}
    @if($confirmedOrderNumber !== null)
        <div class="mb-6 p-6 bg-white border rounded-xl text-center">
            <p class="text-neutral-500">Order number</p>
            <p class="text-6xl font-bold">{{ $confirmedOrderNumber }}</p>
        </div>
    @endif

    <div class="space-y-6">

        {{-- Cart items --}}
        <div class="space-y-4">
            @foreach($this->cart as $index => $item)
                <div class="border rounded-lg p-4 bg-white space-y-3">

                    <div class="flex justify-between items-start">
                        <select
                            @change="$wire.changeItem({{ $index }}, $event.target.value)"
                            class="border rounded px-2 py-1">
                            @foreach($menu['categories'] as $cat)
                                <optgroup label="{{ $cat['label'] }}">
                                    @foreach($cat['items'] as $menuItem)
                                        <option value="{{ $menuItem['key'] }}"
                                            {{ $menuItem['key'] === $item['key'] ? 'selected' : '' }}>
                                            {{ $menuItem['name'] }} — £{{ number_format($menuItem['price_pence'] / 100, 2) }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <button type="button"
                            wire:click="removeItem({{ $index }})"
                            class="text-red-600 text-sm">
                            Remove
                        </button>
                    </div>

                    {{-- Burger customisations --}}
                    @if($item['category'] === 'burger')
                        <div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($menu['standard_ingredients'] as $ingredient)
                                    @php $isRemoved = in_array($ingredient, $item['removed'] ?? []); @endphp
                                    <button type="button"
                                        wire:click="toggleRemoved({{ $index }}, '{{ $ingredient }}')"
                                        class="text-sm border rounded-full px-3 py-1 cursor-pointer transition-colors {{ $isRemoved ? 'bg-red-100 border-red-400 text-red-700' : 'hover:bg-neutral-50' }}">
                                        No {{ $ingredient }}
                                    </button>
                                @endforeach
                            </div>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($menu['paid_extras'] as $extra)
                                    @php
                                        $isAdded = collect($item['added'] ?? [])->contains('ingredient', $extra['key']);
                                    @endphp
                                    <button type="button"
                                        wire:click="toggleExtra({{ $index }}, '{{ $extra['key'] }}', {{ $extra['price_pence'] }})"
                                        class="text-sm border rounded-full px-3 py-1 cursor-pointer transition-colors {{ $isAdded ? 'bg-green-100 border-green-400 text-green-700' : 'hover:bg-neutral-50' }}">
                                        + {{ $extra['name'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @php $itemExtras = $extrasMap[$item['key']] ?? []; @endphp
                        @if(count($itemExtras) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($itemExtras as $extra)
                                    @php
                                        $isAdded = collect($item['added'] ?? [])->contains('ingredient', $extra['key']);
                                    @endphp
                                    <button type="button"
                                        wire:click="toggleExtra({{ $index }}, '{{ $extra['key'] }}', {{ $extra['price_pence'] }})"
                                        class="text-sm border rounded-full px-3 py-1 cursor-pointer transition-colors {{ $isAdded ? 'bg-green-100 border-green-400 text-green-700' : 'hover:bg-neutral-50' }}">
                                        + {{ $extra['name'] }}{{ $extra['price_pence'] > 0 ? ' (+£' . number_format($extra['price_pence'] / 100, 2) . ')' : '' }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    {{-- Quantity --}}
                    <div class="flex items-center gap-2">
                        <label class="text-sm">Qty</label>
                        <input type="number"
                            wire:model.live="cart.{{ $index }}.quantity"
                            min="1" max="20"
                            class="w-16 border rounded px-2 py-1">
                    </div>

                </div>
            @endforeach
        </div>

        {{-- Add item --}}
        <button type="button"
            wire:click="addItem"
            class="w-full border-2 border-dashed border-neutral-300 rounded-lg py-3 text-neutral-600 hover:border-neutral-400">
            + Add an item
        </button>

        {{-- Customer name --}}
        <div>
            <label class="block text-sm font-medium mb-1">Customer name (optional)</label>
            <input type="text"
                wire:model.live="customerName"
                maxlength="60"
                class="w-full border rounded-lg px-3 py-2">
        </div>

        {{-- SMS notification --}}
        <div class="space-y-2">
            <div>
                <label class="block text-sm font-medium mb-1">Mobile number (optional)</label>
                <input type="tel"
                    wire:model.live="phoneNumber"
                    maxlength="20"
                    placeholder="e.g. 07700 900123"
                    class="w-full border rounded-lg px-3 py-2">
            </div>
            @if($phoneNumber)
                <label class="flex items-center gap-2 text-sm cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="smsOptIn" class="rounded">
                    Text me when my order is ready
                </label>
            @endif
        </div>

        {{-- Payment method --}}
        <div>
            <p class="text-sm font-medium mb-2">Payment method</p>
            <div class="flex gap-4">
                <label class="flex items-center gap-2">
                    <input type="radio" wire:model.live="paymentMethod" value="card"> Card (SumUp reader)
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" wire:model.live="paymentMethod" value="cash"> Cash
                </label>
            </div>
            <p class="text-xs text-neutral-500 mt-1">
                For card, take payment on the SumUp reader first, then confirm the order below.
            </p>
        </div>

        {{-- Meal deal banner --}}
        @if($this->getMealDealSaving() > 0)
            <div class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                Meal Deal — £{{ number_format($this->getMealDealSaving() / 100, 2) }} off
            </div>
        @endif

        {{-- Total + submit --}}
        <div class="flex items-center justify-between border-t pt-4">
            <span class="text-xl font-semibold">
                Total: £{{ number_format($this->getTotal() / 100, 2) }}
            </span>
            <button type="button"
                wire:click="submit"
                wire:loading.attr="disabled"
                class="bg-black text-white px-6 py-3 rounded-lg font-medium hover:bg-neutral-800 disabled:opacity-60">
                Confirm Order
            </button>
        </div>

    </div>
</div>
