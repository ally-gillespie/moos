<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order — Moo's</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-50 min-h-screen">
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Build Your Order</h1>

    <form id="order-form" class="space-y-6">
        @csrf
        <div id="cart-items" class="space-y-6"></div>

        <button type="button" id="add-item-btn"
                class="w-full border-2 border-dashed border-neutral-300 rounded-lg py-3 text-neutral-600 hover:border-neutral-400">
            + Add an item
        </button>

        <div class="border-t pt-4">
            <label class="block text-sm font-medium mb-1">Name (for calling out your order)</label>
            <input type="text" name="customer_name" maxlength="60"
                   class="w-full border rounded-lg px-3 py-2" placeholder="Optional">
        </div>

        <div id="meal-deal-banner" class="hidden text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-2">
            🎉 Meal Deal applied — <span id="meal-deal-saving"></span> off!
        </div>

        <div class="flex items-center justify-between border-t pt-4">
            <span class="text-xl font-semibold">Total: <span id="total-display">£0.00</span></span>
            <button type="submit"
                    class="bg-black text-white px-6 py-3 rounded-lg font-medium hover:bg-neutral-800">
                Pay & Place Order
            </button>
        </div>
    </form>
</div>

<template id="item-template">
    <div class="cart-item border rounded-lg p-4 bg-white space-y-3">
        <div class="flex justify-between items-start">
            <select class="item-select border rounded px-2 py-1">
                @foreach($menu['categories'] as $cat)
                    <optgroup label="{{ $cat['label'] }}">
                        @foreach($cat['items'] as $item)
                            <option value="{{ $item['key'] }}"
                                    data-name="{{ $item['name'] }}"
                                    data-price="{{ $item['price_pence'] }}"
                                    data-type="{{ $cat['key'] }}"
                                    data-extras="{{ json_encode($item['extras']) }}">
                                {{ $item['name'] }} — £{{ number_format($item['price_pence'] / 100, 2) }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <button type="button" class="remove-item-btn text-red-600 text-sm">Remove</button>
        </div>

        {{-- Shown for burgers only --}}
        <div class="burger-customise">
            <p class="text-sm font-medium mb-1">Remove any of these:</p>
            <div class="flex flex-wrap gap-2 standard-ingredients">
                @foreach($menu['standard_ingredients'] as $ingredient)
                    <label class="text-sm border rounded-full px-3 py-1 cursor-pointer">
                        <input type="checkbox" value="{{ $ingredient }}" class="remove-ingredient mr-1">
                        {{ ucfirst($ingredient) }}
                    </label>
                @endforeach
            </div>
            <p class="text-sm font-medium mb-1 mt-2">Add extras:</p>
            <div class="flex flex-wrap gap-2 paid-extras">
                @foreach($menu['paid_extras'] as $extra)
                    <label class="text-sm border rounded-full px-3 py-1 cursor-pointer">
                        <input type="checkbox" value="{{ $extra['key'] }}" data-price="{{ $extra['price_pence'] }}"
                               class="add-extra mr-1">
                        {{ $extra['name'] }} (+£{{ number_format($extra['price_pence'] / 100, 2) }})
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Shown for sides with their own extras --}}
        <div class="side-extras hidden">
            <p class="text-sm font-medium mb-1">Extras:</p>
            <div class="flex flex-wrap gap-2 side-extras-list"></div>
        </div>

        <div class="flex items-center gap-2">
            <label class="text-sm">Qty</label>
            <input type="number" class="item-qty w-16 border rounded px-2 py-1" value="1" min="1" max="20">
        </div>
    </div>
</template>

<script>
const BURGER_KEYS = @json(array_column($menu['burgers'], 'key'));
const MEAL_DEALS  = @json($menu['meal_deals'] ?? []);
const cartItemsEl = document.getElementById('cart-items');
const template = document.getElementById('item-template');
const totalDisplay = document.getElementById('total-display');

function addItem() {
    const clone = template.content.cloneNode(true);
    cartItemsEl.appendChild(clone);
    wireItem(cartItemsEl.lastElementChild);
    recalcTotal();
}

function updateItemExtras(itemEl) {
    const opt = itemEl.querySelector('.item-select').selectedOptions[0];
    const isBurger = opt.dataset.type === 'burger';
    itemEl.querySelector('.burger-customise').classList.toggle('hidden', !isBurger);
    const sideExtrasEl = itemEl.querySelector('.side-extras');
    const sideListEl = itemEl.querySelector('.side-extras-list');
    if (!isBurger) {
        const extras = JSON.parse(opt.dataset.extras || '[]');
        sideListEl.innerHTML = extras.map(e => `
            <label class="text-sm border rounded-full px-3 py-1 cursor-pointer">
                <input type="checkbox" value="${e.key}" data-price="${e.price_pence}" class="add-extra mr-1">
                ${e.name}${e.price_pence > 0 ? ' (+£' + (e.price_pence / 100).toFixed(2) + ')' : ''}
            </label>`).join('');
        sideListEl.querySelectorAll('input').forEach(el => el.addEventListener('change', recalcTotal));
        sideExtrasEl.classList.toggle('hidden', extras.length === 0);
    } else {
        sideExtrasEl.classList.add('hidden');
    }
}

function wireItem(itemEl) {
    const select = itemEl.querySelector('.item-select');
    select.addEventListener('change', () => { updateItemExtras(itemEl); recalcTotal(); });
    itemEl.querySelectorAll('input').forEach(el => el.addEventListener('change', recalcTotal));
    itemEl.querySelector('.remove-item-btn').addEventListener('click', () => {
        itemEl.remove();
        recalcTotal();
    });
    updateItemExtras(itemEl);
}

function recalcTotal() {
    let total = 0;
    const selectedKeys = [];
    document.querySelectorAll('.cart-item').forEach(item => {
        const select = item.querySelector('.item-select');
        const opt = select.selectedOptions[0];
        selectedKeys.push(opt.value);
        const price = parseInt(opt.dataset.price, 10);
        const qty = parseInt(item.querySelector('.item-qty').value, 10) || 1;
        let lineTotal = price * qty;
        item.querySelectorAll('.add-extra:checked').forEach(cb => {
            lineTotal += parseInt(cb.dataset.price, 10) * qty;
        });
        total += lineTotal;
    });
    let totalDiscount = 0;
    const hasBurger = selectedKeys.some(k => BURGER_KEYS.includes(k));
    MEAL_DEALS.forEach(deal => {
        if (hasBurger && selectedKeys.includes(deal.side_key)) {
            totalDiscount += deal.discount_pence;
        }
    });
    total = Math.max(0, total - totalDiscount);
    totalDisplay.textContent = '£' + (total / 100).toFixed(2);
    const banner = document.getElementById('meal-deal-banner');
    if (totalDiscount > 0) {
        document.getElementById('meal-deal-saving').textContent = '£' + (totalDiscount / 100).toFixed(2);
        banner.classList.remove('hidden');
    } else {
        banner.classList.add('hidden');
    }
}

document.getElementById('add-item-btn').addEventListener('click', addItem);
addItem(); // start with one burger in the cart

document.getElementById('order-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const items = [...document.querySelectorAll('.cart-item')].map(item => {
        const select = item.querySelector('.item-select');
        const opt = select.selectedOptions[0];
        return {
            key: opt.value,
            name: opt.dataset.name,
            unit_price_pence: parseInt(opt.dataset.price, 10),
            quantity: parseInt(item.querySelector('.item-qty').value, 10) || 1,
            removed: [...item.querySelectorAll('.remove-ingredient:checked')].map(cb => cb.value),
            added: [...item.querySelectorAll('.add-extra:checked')].map(cb => ({
                ingredient: cb.value,
                extra_price_pence: parseInt(cb.dataset.price, 10),
            })),
        };
    });

    const customerName = document.querySelector('[name=customer_name]').value;

    const response = await fetch('{{ route('order.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('[name=csrf-token]')?.content
                || document.querySelector('input[name=_token]').value,
        },
        body: JSON.stringify({ items, customer_name: customerName }),
    });

    if (response.ok) {
        const data = await response.json();
        // Full browser navigation (not fetch) so it can follow the
        // subsequent redirect out to SumUp's hosted checkout page.
        window.location.href = data.payment_url;
    } else {
        alert('Something went wrong placing your order. Please try again.');
    }
});
</script>
</body>
</html>
