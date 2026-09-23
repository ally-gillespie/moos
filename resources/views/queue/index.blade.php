<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1/dist/echo.iife.js"></script>
</head>
<body class="bg-neutral-900 text-white min-h-screen p-10">
<h1 class="text-3xl font-bold text-center mb-10">Order Status</h1>

<div class="grid grid-cols-2 gap-10 max-w-4xl mx-auto">
    <div>
        <h2 class="text-xl text-neutral-400 mb-4 text-center">Preparing</h2>
        <div id="col-preparing" class="flex flex-wrap gap-4 justify-center"></div>
    </div>
    <div>
        <h2 class="text-xl text-green-400 mb-4 text-center">Ready — Come collect!</h2>
        <div id="col-ready" class="flex flex-wrap gap-4 justify-center"></div>
    </div>
</div>

<script>
const columns = { queued: null, preparing: 'col-preparing', ready: 'col-ready' };

function badgeHtml(orderNumber, customerName, ready) {
    const bg = ready ? 'bg-green-600' : 'bg-neutral-700';
    if (customerName) {
        const nameColour = 'text-white';
        return `<div class="${bg} rounded-xl px-6 py-4 text-center">
                    <div class="text-4xl font-bold ${nameColour}">${customerName}</div>
                    <div class="text-m text-neutral-300 mt-1">#${orderNumber}</div>
                </div>`;
    }
    return `<div class="text-4xl font-bold rounded-xl px-6 py-4 ${bg}">${orderNumber}</div>`;
}

function render(orderNumber, status, customerName) {
    document.querySelectorAll(`[data-order-number="${orderNumber}"]`).forEach(el => el.remove());

    const colId = columns[status];
    if (!colId) return;

    const col = document.getElementById(colId);
    const el = document.createElement('div');
    el.dataset.orderNumber = orderNumber;
    el.innerHTML = badgeHtml(orderNumber, customerName || null, status === 'ready');
    col.appendChild(el);
}

// Initial state
@foreach($orders as $order)
render({{ $order->order_number }}, '{{ $order->status }}', @json($order->customer_name));
@endforeach

// --- Polling (primary) ------------------------------------------------
function syncQueue(serverOrders) {
    const serverNumbers = new Set(serverOrders.map(o => o.order_number));

    document.querySelectorAll('[data-order-number]').forEach(el => {
        if (!serverNumbers.has(parseInt(el.dataset.orderNumber))) el.remove();
    });

    serverOrders.forEach(o => render(o.order_number, o.status, o.customer_name));
}

function pollQueue() {
    fetch('/api/queue/status')
        .then(r => r.ok ? r.json() : null)
        .then(data => { if (data) syncQueue(data); })
        .catch(() => {});
}

setInterval(pollQueue, 3000);

// --- Reverb (fast path, best-effort) ----------------------------------
const echo = new Echo({
    broadcaster: 'reverb',
    key: '{{ config('broadcasting.connections.reverb.key') }}',
    wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
    wsPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
    wssPort: {{ config('broadcasting.connections.reverb.options.port', 8080) }},
    forceTLS: {{ config('broadcasting.connections.reverb.options.useTLS') ? 'true' : 'false' }},
    enabledTransports: ['ws', 'wss'],
});

echo.channel('queue-display').listen('.QueueDisplayUpdated', (data) => {
    render(data.order_number, data.status, data.customer_name || null);
});
</script>
</body>
</html>
