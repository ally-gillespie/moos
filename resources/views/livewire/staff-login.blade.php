<div class="bg-white rounded-2xl p-8 w-80 text-center">

    {{-- Step 1: pick a name --}}
    @if($selectedStaffId === null)
        <h1 class="text-lg font-semibold mb-6">Who are you?</h1>
        <div class="space-y-2">
            @forelse($staff as $member)
                <button type="button"
                    wire:click="selectStaff({{ $member->id }}, '{{ addslashes($member->name) }}')"
                    class="w-full border rounded-lg py-3 text-base font-medium hover:bg-neutral-50 active:bg-neutral-100 transition-colors">
                    {{ $member->name }}
                </button>
            @empty
                <p class="text-neutral-400 text-sm">No staff accounts found.</p>
            @endforelse
        </div>

    {{-- Step 2: enter PIN --}}
    @else
        <button type="button"
            wire:click="selectStaff(null, '')"
            class="text-sm text-neutral-400 hover:text-neutral-600 mb-4 block mx-auto">
            ← Back
        </button>

        <h1 class="text-lg font-semibold mb-1">Enter PIN</h1>
        <p class="text-neutral-500 text-sm mb-4">{{ $selectedName }}</p>

        @if($error)
            <p class="text-red-600 text-sm mb-3">{{ $error }}</p>
        @endif

        {{-- PIN dots display --}}
        <div class="w-full text-center text-3xl tracking-widest border rounded-lg py-3 mb-4 min-h-[3.5rem]">
            {{ str_repeat('● ', strlen($pin)) }}
        </div>

        {{-- Keypad --}}
        <div class="grid grid-cols-3 gap-2 mb-4">
            @foreach([1,2,3,4,5,6,7,8,9] as $n)
                <button type="button"
                    wire:click="appendDigit('{{ $n }}')"
                    class="border rounded-lg py-3 text-xl font-medium hover:bg-neutral-50">
                    {{ $n }}
                </button>
            @endforeach
            <button type="button"
                wire:click="clearPin"
                class="border rounded-lg py-3 text-sm hover:bg-neutral-50">
                Clear
            </button>
            <button type="button"
                wire:click="appendDigit('0')"
                class="border rounded-lg py-3 text-xl font-medium hover:bg-neutral-50">
                0
            </button>
            <button type="button"
                wire:click="backspace"
                class="border rounded-lg py-3 text-sm hover:bg-neutral-50">
                ⌫
            </button>
        </div>

        <button type="button"
            wire:click="login"
            wire:loading.attr="disabled"
            class="w-full bg-black text-white py-3 rounded-lg font-medium disabled:opacity-60">
            Log in
        </button>
    @endif

</div>
