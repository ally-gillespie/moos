<div wire:poll.3000ms>
    <h1 class="text-3xl font-bold text-center mb-10">Order Status</h1>

    <div class="grid grid-cols-2 gap-10 max-w-4xl mx-auto">

        {{-- Preparing --}}
        <div>
            <h2 class="text-xl text-neutral-400 mb-4 text-center">Preparing</h2>
            <div class="flex flex-wrap gap-4 justify-center">
                @foreach($orders->where('status', 'preparing') as $order)
                    <div class="bg-neutral-700 rounded-xl px-6 py-4 text-center">
                        @if($order->customer_name)
                            <div class="text-4xl font-bold text-white">{{ $order->customer_name }}</div>
                            <div class="text-xl text-neutral-300 mt-1">#{{ $order->order_number }}</div>
                        @else
                            <div class="text-4xl font-bold">{{ $order->order_number }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Ready --}}
        <div>
            <h2 class="text-xl text-green-400 mb-4 text-center">Ready — Come collect!</h2>
            <div class="flex flex-wrap gap-4 justify-center">
                @foreach($orders->where('status', 'ready') as $order)
                    <div class="bg-green-600 rounded-xl px-6 py-4 text-center">
                        @if($order->customer_name)
                            <div class="text-4xl font-bold text-white">{{ $order->customer_name }}</div>
                            <div class="text-xl text-neutral-300 mt-1">#{{ $order->order_number }}</div>
                        @else
                            <div class="text-4xl font-bold">{{ $order->order_number }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
