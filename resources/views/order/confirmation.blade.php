<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-50 min-h-screen flex items-center justify-center">
<div class="text-center p-8 bg-white rounded-xl shadow-sm border max-w-md">
    <p class="text-neutral-500 mb-2">Your order number is</p>
    <p class="text-7xl font-bold mb-4">{{ $order->order_number }}</p>
    <p class="text-neutral-600 mb-6">We'll call your number when it's ready. Keep an eye on the screen!</p>
    <a href="{{ route('queue.index') }}" class="text-sm underline text-neutral-500">View the queue screen</a>
</div>
</body>
</html>
