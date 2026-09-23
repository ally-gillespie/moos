<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-50 min-h-screen flex items-center justify-center">
<div class="text-center p-8 bg-white rounded-xl shadow-sm border max-w-md">
    <p class="text-xl font-semibold mb-2">Payment wasn't completed</p>
    <p class="text-neutral-600 mb-6">Your order hasn't been placed. Please try again.</p>
    <a href="{{ route('order.create') }}" class="inline-block bg-black text-white px-5 py-2.5 rounded-lg">
        Back to order
    </a>
</div>
</body>
</html>
