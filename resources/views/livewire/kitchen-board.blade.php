<div wire:poll.3000ms class="p-6">

    {{-- Nav --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Orders</h1>
        <div class="flex items-center gap-4">
            @if(session('staff_role') === 'manager')
                <a wire:navigate href="{{ route('pos.create') }}" class="text-sm underline">Go to POS</a>
                <a wire:navigate href="{{ route('archive.index') }}" class="text-sm underline">Archive</a>
                <a wire:navigate href="{{ route('menu.index') }}" class="text-sm underline">Menu</a>
                <a wire:navigate href="{{ route('admin.index') }}" class="text-sm underline">Admin</a>
            @endif
            <form method="POST" action="{{ route('kitchen.logout') }}" class="flex items-center">
                @csrf
                <button class="text-sm text-neutral-500 underline">Log out ({{ $staffName }})</button>
            </form>
        </div>
    </div>

    {{-- Three-column board --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Queued --}}
        <div>
            <h2 class="font-semibold text-neutral-600 mb-3">Queued</h2>
            <div class="space-y-3">
                @foreach($orders->where('status', 'queued') as $order)
                    <div class="bg-white rounded-lg border p-4 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-2xl font-bold">#{{ $order->order_number }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs uppercase text-neutral-400">{{ $order->source }}</span>
                                <button
                                    wire:click="cancel({{ $order->id }})"
                                    wire:confirm="Cancel order #{{ $order->order_number }}?"
                                    wire:loading.attr="disabled"
                                    class="text-neutral-300 hover:text-red-500 text-lg leading-none"
                                    title="Cancel order">
                                    &times;
                                </button>
                            </div>
                        </div>
                        @if($order->customer_name)
                            <p class="text-sm text-neutral-600 mb-1">{{ $order->customer_name }}</p>
                        @endif
                        <div class="space-y-1 mb-3">
                            @foreach($order->items as $item)
                                <div class="text-sm">
                                    <span class="font-medium">{{ $item->quantity }}× {{ $item->item_name }}</span>
                                    @if($item->customizations->isNotEmpty())
                                        <div class="text-xs text-neutral-500">
                                            @foreach($item->customizations as $c)
                                                <span class="{{ $c->action === 'removed' ? 'text-red-600' : 'text-green-700' }}">
                                                    {{ $c->action === 'removed' ? 'No ' : '+' }}{{ $c->ingredient }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <button
                            wire:click="bump({{ $order->id }})"
                            wire:loading.attr="disabled"
                            class="w-full bg-black text-white text-sm py-2 rounded-lg disabled:opacity-60">
                            Start preparing
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Preparing --}}
        <div>
            <h2 class="font-semibold text-neutral-600 mb-3">Preparing</h2>
            <div class="space-y-3">
                @foreach($orders->where('status', 'preparing') as $order)
                    <div class="bg-white rounded-lg border p-4 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-2xl font-bold">#{{ $order->order_number }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs uppercase text-neutral-400">{{ $order->source }}</span>
                                <button
                                    wire:click="cancel({{ $order->id }})"
                                    wire:confirm="Cancel order #{{ $order->order_number }}?"
                                    wire:loading.attr="disabled"
                                    class="text-neutral-300 hover:text-red-500 text-lg leading-none"
                                    title="Cancel order">
                                    &times;
                                </button>
                            </div>
                        </div>
                        @if($order->customer_name)
                            <p class="text-sm text-neutral-600 mb-1">{{ $order->customer_name }}</p>
                        @endif
                        <div class="space-y-1 mb-3">
                            @foreach($order->items as $item)
                                <div class="text-sm">
                                    <span class="font-medium">{{ $item->quantity }}× {{ $item->item_name }}</span>
                                    @if($item->customizations->isNotEmpty())
                                        <div class="text-xs text-neutral-500">
                                            @foreach($item->customizations as $c)
                                                <span class="{{ $c->action === 'removed' ? 'text-red-600' : 'text-green-700' }}">
                                                    {{ $c->action === 'removed' ? 'No ' : '+' }}{{ $c->ingredient }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <button
                            wire:click="bump({{ $order->id }})"
                            wire:loading.attr="disabled"
                            class="w-full bg-black text-white text-sm py-2 rounded-lg disabled:opacity-60">
                            Mark ready
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Ready --}}
        <div>
            <h2 class="font-semibold text-neutral-600 mb-3">Ready</h2>
            <div class="space-y-3">
                @foreach($orders->where('status', 'ready') as $order)
                    <div class="bg-white rounded-lg border p-4 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-2xl font-bold">#{{ $order->order_number }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs uppercase text-neutral-400">{{ $order->source }}</span>
                                <button
                                    wire:click="cancel({{ $order->id }})"
                                    wire:confirm="Cancel order #{{ $order->order_number }}?"
                                    wire:loading.attr="disabled"
                                    class="text-neutral-300 hover:text-red-500 text-lg leading-none"
                                    title="Cancel order">
                                    &times;
                                </button>
                            </div>
                        </div>
                        @if($order->customer_name)
                            <p class="text-sm text-neutral-600 mb-1">{{ $order->customer_name }}</p>
                        @endif
                        <div class="space-y-1 mb-3">
                            @foreach($order->items as $item)
                                <div class="text-sm">
                                    <span class="font-medium">{{ $item->quantity }}× {{ $item->item_name }}</span>
                                    @if($item->customizations->isNotEmpty())
                                        <div class="text-xs text-neutral-500">
                                            @foreach($item->customizations as $c)
                                                <span class="{{ $c->action === 'removed' ? 'text-red-600' : 'text-green-700' }}">
                                                    {{ $c->action === 'removed' ? 'No ' : '+' }}{{ $c->ingredient }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <button
                            wire:click="bump({{ $order->id }})"
                            wire:loading.attr="disabled"
                            class="w-full bg-black text-white text-sm py-2 rounded-lg disabled:opacity-60">
                            Bump (collected)
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
