<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kitchen — Moo's</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1/dist/echo.iife.js"></script>
</head>
<body class="bg-neutral-100 min-h-screen">
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Orders</h1>
        <div class="flex items-center gap-4">
            @if(session('staff_role') === 'manager')
                <a href="{{ route('pos.create') }}" class="text-sm underline">Go to POS</a>
                <a href="{{ route('archive.index') }}" class="text-sm underline">Archive</a>
                <a href="{{ route('menu.index') }}" class="text-sm underline">Menu</a>
                <a href="{{ route('admin.index') }}" class="text-sm underline">Admin</a>
            @endif
            <form method="POST" action="{{ route('kitchen.logout') }}" class="flex items-center">
                @csrf
                <button class="text-sm text-neutral-500 underline">Log out ({{ $staffName }})</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <h2 class="font-semibold text-neutral-600 mb-3">Queued</h2>
            <div id="col-queued" class="space-y-3"></div>
        </div>
        <div>
            <h2 class="font-semibold text-neutral-600 mb-3">Preparing</h2>
            <div id="col-preparing" class="space-y-3"></div>
        </div>
        <div>
            <h2 class="font-semibold text-neutral-600 mb-3">Ready</h2>
            <div id="col-ready" class="space-y-3"></div>
        </div>
    </div>
</div>

<script>
// --- Card rendering ---------------------------------------------------
const columns = { queued: 'col-queued', preparing: 'col-preparing', ready: 'col-ready' };
const nextStatusLabel = { queued: 'Start preparing', preparing: 'Mark ready', ready: 'Bump (collected)' };

function cardHtml(order) {
    const itemsHtml = (order.items || []).map(item => {
        const customizations = (item.customizations || []).map(c =>
            `<span class="${c.action === 'removed' ? 'text-red-600' : 'text-green-700'}">
                ${c.action === 'removed' ? 'No ' : '+'}${c.ingredient}
            </span>`
        ).join(', ');
        return `<div class="text-sm">
                    <span class="font-medium">${item.quantity}× ${item.name}</span>
                    ${customizations ? `<div class="text-xs text-neutral-500">${customizations}</div>` : ''}
                </div>`;
    }).join('');

    return `
        <div class="bg-white rounded-lg border p-4 shadow-sm" data-order-id="${order.id}" data-status="${order.status}">
            <div class="flex justify-between items-center mb-2">
                <span class="text-2xl font-bold">#${order.order_number}</span>
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase text-neutral-400">${order.source}</span>
                    <button class="cancel-btn text-neutral-300 hover:text-red-500 text-lg leading-none" title="Cancel order">&times;</button>
                </div>
            </div>
            ${order.customer_name ? `<p class="text-sm text-neutral-600 mb-1">${order.customer_name}</p>` : ''}
            <div class="space-y-1 mb-3">${itemsHtml}</div>
            <button class="bump-btn w-full bg-black text-white text-sm py-2 rounded-lg">
                ${nextStatusLabel[order.status] || 'Bump'}
            </button>
        </div>`;
}

function addOrderCard(order) {
    const col = document.getElementById(columns[order.status]);
    if (!col) return;
    const el = document.createElement('div');
    el.innerHTML = cardHtml(order);
    const card = el.firstElementChild;
    wireBumpButton(card);
    col.prepend(card);
}

const nextStatus = { queued: 'preparing', preparing: 'ready', ready: 'collected' };

function moveOrderCard(orderId, newStatus, orderNumber) {
    const existing = document.querySelector(`[data-order-id="${orderId}"]`);

    if (newStatus === 'collected') {
        existing?.remove();
        return;
    }

    // Already moved by the optimistic update — skip re-render to avoid flicker.
    if (existing?.dataset.status === newStatus) return;

    existing?.remove();

    addOrderCard({
        id: orderId,
        order_number: orderNumber,
        status: newStatus,
        items: [],
        source: '',
    });
}

function wireBumpButton(card) {
    card.querySelector('.bump-btn').addEventListener('click', () => {
        const orderId   = card.dataset.orderId;
        const curStatus = card.dataset.status;
        const nxt       = nextStatus[curStatus];

        // Optimistic update: move the card immediately without waiting for the broadcast.
        if (nxt === 'collected') {
            card.remove();
        } else if (nxt && columns[nxt]) {
            card.dataset.status = nxt;
            card.querySelector('.bump-btn').textContent = nextStatusLabel[nxt] || 'Bump';
            document.getElementById(columns[nxt]).prepend(card);
        }

        fetch(`/kitchen/orders/${orderId}/bump`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        });
    });

    card.querySelector('.cancel-btn').addEventListener('click', () => {
        const orderId = card.dataset.orderId;
        const orderNum = card.querySelector('.text-2xl').textContent.trim();
        if (!confirm(`Cancel order ${orderNum}?`)) return;
        card.remove();
        fetch(`/kitchen/orders/${orderId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        });
    });
}

// --- Initial board state from the server-rendered order list ----------
const initialOrders = @json($orders);
initialOrders.forEach(order => addOrderCard({
    id: order.id,
    order_number: order.order_number,
    status: order.status,
    customer_name: order.customer_name,
    source: order.source,
    items: order.items.map(i => ({
        name: i.item_name,
        quantity: i.quantity,
        customizations: i.customizations.map(c => ({ ingredient: c.ingredient, action: c.action })),
    })),
}));

// --- Polling (primary) ------------------------------------------------
// syncBoard applies server state forward-only: never moves a card backwards
// so optimistic bump updates aren't reversed by a poll arriving mid-flight.
const statusOrder = { queued: 0, preparing: 1, ready: 2, collected: 3 };

function syncBoard(serverOrders) {
    const serverById = {};
    serverOrders.forEach(o => serverById[o.id] = o);

    // Remove cards that are gone (collected / not in today's active set)
    document.querySelectorAll('[data-order-id]').forEach(card => {
        const id = parseInt(card.dataset.orderId);
        if (!serverById[id]) card.remove();
    });

    serverOrders.forEach(serverOrder => {
        const existing = document.querySelector(`[data-order-id="${serverOrder.id}"]`);
        const serverStatusRank = statusOrder[serverOrder.status] ?? 0;

        if (!existing) {
            // New order — add it
            addOrderCard({
                id: serverOrder.id,
                order_number: serverOrder.order_number,
                status: serverOrder.status,
                customer_name: serverOrder.customer_name,
                source: serverOrder.source,
                items: (serverOrder.items || []).map(i => ({
                    name: i.item_name,
                    quantity: i.quantity,
                    customizations: (i.customizations || []).map(c => ({ ingredient: c.ingredient, action: c.action })),
                })),
            });
        } else {
            // Existing card: only advance status, never go backwards
            const currentStatusRank = statusOrder[existing.dataset.status] ?? 0;
            if (serverStatusRank > currentStatusRank) {
                moveOrderCard(serverOrder.id, serverOrder.status, serverOrder.order_number);
            }
        }
    });
}

function pollOrders() {
    fetch('/api/kitchen/orders', {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    })
        .then(r => r.ok ? r.json() : null)
        .then(data => { if (data) syncBoard(data); })
        .catch(() => {});
}

setInterval(pollOrders, 3000);

// --- Reverb (fast path, best-effort) ----------------------------------
const echo = new Echo({
    broadcaster: 'reverb',
    key: '{{ config('broadcasting.connections.reverb.key') }}',
    wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
    wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
    wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
    forceTLS: {{ config('broadcasting.connections.reverb.options.useTLS') ? 'true' : 'false' }},
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
    },
});

echo.private('kitchen-orders')
    .listen('.OrderCreated', (order) => addOrderCard(order))
    .listen('.KitchenOrderUpdated', (data) => moveOrderCard(data.id, data.status, data.order_number));
</script>
</body>
</html>
