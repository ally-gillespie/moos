<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive — Moo's</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] summary .chevron { transform: rotate(90deg); }
        .chevron { transition: transform 0.15s; display: inline-block; }
    </style>
</head>
<body class="bg-neutral-100 min-h-screen">
<div class="max-w-3xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Sales Archive</h1>
        <a href="{{ route('kitchen.index') }}" class="text-sm underline text-neutral-500">Back to kitchen</a>
    </div>

    @if($days->isEmpty())
        <p class="text-neutral-500">No completed orders yet.</p>
    @else
        <div class="space-y-3">
        @foreach($days as $day)
            @php
                $items = $itemsByDate->get($day->trading_date, collect());
                $total = '£' . number_format($day->total_pence / 100, 2);
                $label = \Carbon\Carbon::parse($day->trading_date)->format('l j F Y');
            @endphp

            <details class="bg-white rounded-lg border shadow-sm">
                <summary class="flex justify-between items-center px-5 py-4 cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <span class="chevron text-neutral-400">&#8250;</span>
                        <span class="font-semibold">{{ $label }}</span>
                        <span class="text-sm text-neutral-400">{{ $day->order_count }} {{ Str::plural('order', $day->order_count) }}</span>
                    </div>
                    <span class="text-lg font-bold text-green-700">{{ $total }}</span>
                </summary>

                <div class="border-t px-5 py-4">
                    @if($items->isEmpty())
                        <p class="text-sm text-neutral-400">No item data available.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-neutral-400 text-left border-b">
                                    <th class="pb-2 font-medium">Item</th>
                                    <th class="pb-2 font-medium text-right">Qty</th>
                                    <th class="pb-2 font-medium text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @foreach($items as $item)
                                    <tr>
                                        <td class="py-2">{{ $item->item_name }}</td>
                                        <td class="py-2 text-right text-neutral-600">{{ $item->qty }}</td>
                                        <td class="py-2 text-right font-medium">£{{ number_format($item->item_total_pence / 100, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-neutral-200">
                                    <td class="pt-3 font-semibold">Total</td>
                                    <td class="pt-3 text-right text-neutral-600 font-semibold">{{ $items->sum('qty') }}</td>
                                    <td class="pt-3 text-right font-bold text-green-700">{{ $total }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    @endif

                    <div class="mt-4 flex justify-end">
                        <form method="POST"
                              action="{{ route('archive.destroy-day', $day->trading_date) }}"
                              onsubmit="return confirm('Delete all data for {{ $label }}? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-red-600 hover:text-red-800 underline">
                                Clear this day
                            </button>
                        </form>
                    </div>
                </div>
            </details>
        @endforeach
        </div>

        <div class="mt-6 bg-white rounded-lg border shadow-sm px-5 py-4 flex justify-between items-center">
            <span class="font-semibold text-neutral-600">All-time total</span>
            <span class="text-xl font-bold text-green-700">
                £{{ number_format($days->sum('total_pence') / 100, 2) }}
            </span>
        </div>

        <div class="mt-4 flex justify-end">
            <form method="POST"
                  action="{{ route('archive.destroy-all') }}"
                  onsubmit="return confirm('Delete ALL order data? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button class="text-sm text-red-600 hover:text-red-800 underline">
                    Clear all data
                </button>
            </form>
        </div>
    @endif

</div>
</body>
</html>
