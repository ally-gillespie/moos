<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-900 min-h-screen flex items-center justify-center">

{{-- Step 1: pick a name --}}
<div id="step-names" class="bg-white rounded-2xl p-8 w-80 text-center">
    <h1 class="text-lg font-semibold mb-6">Who are you?</h1>
    <div class="space-y-2">
        @foreach($staff as $member)
            <button type="button"
                onclick="selectStaff({{ $member->id }}, '{{ addslashes($member->name) }}')"
                class="w-full border rounded-lg py-3 text-base font-medium hover:bg-neutral-50 active:bg-neutral-100 transition-colors">
                {{ $member->name }}
            </button>
        @endforeach
        @if($staff->isEmpty())
            <p class="text-neutral-400 text-sm">No staff accounts found.</p>
        @endif
    </div>
</div>

{{-- Step 2: enter PIN --}}
<div id="step-pin" class="bg-white rounded-2xl p-8 w-80 text-center hidden">
    <button type="button" onclick="goBack()" class="text-sm text-neutral-400 hover:text-neutral-600 mb-4 block mx-auto">← Back</button>
    <h1 class="text-lg font-semibold mb-1">Enter PIN</h1>
    <p id="pin-name" class="text-neutral-500 text-sm mb-4"></p>

    @if ($errors->any())
        <p class="text-red-600 text-sm mb-3">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ route('kitchen.login.submit') }}" id="pin-form">
        @csrf
        <input type="hidden" name="staff_id" id="staff-id-input">
        <input type="password" name="pin" id="pin-display" readonly
               class="w-full text-center text-3xl tracking-widest border rounded-lg py-3 mb-4" maxlength="6">

        <div class="grid grid-cols-3 gap-2 mb-4">
            @foreach([1,2,3,4,5,6,7,8,9] as $n)
                <button type="button" class="keypad-btn border rounded-lg py-3 text-xl font-medium hover:bg-neutral-50" data-digit="{{ $n }}">{{ $n }}</button>
            @endforeach
            <button type="button" id="clear-btn" class="border rounded-lg py-3 text-sm hover:bg-neutral-50">Clear</button>
            <button type="button" class="keypad-btn border rounded-lg py-3 text-xl font-medium hover:bg-neutral-50" data-digit="0">0</button>
            <button type="button" id="backspace-btn" class="border rounded-lg py-3 text-sm hover:bg-neutral-50">⌫</button>
        </div>

        <button type="submit" class="w-full bg-black text-white py-3 rounded-lg font-medium">Log in</button>
    </form>
</div>

<script>
const stepNames = document.getElementById('step-names');
const stepPin   = document.getElementById('step-pin');
const display   = document.getElementById('pin-display');

function selectStaff(id, name) {
    document.getElementById('staff-id-input').value = id;
    document.getElementById('pin-name').textContent = name;
    display.value = '';
    stepNames.classList.add('hidden');
    stepPin.classList.remove('hidden');
}

function goBack() {
    stepPin.classList.add('hidden');
    stepNames.classList.remove('hidden');
    display.value = '';
}

document.querySelectorAll('.keypad-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (display.value.length < 6) display.value += btn.dataset.digit;
    });
});

document.getElementById('clear-btn').addEventListener('click', () => display.value = '');
document.getElementById('backspace-btn').addEventListener('click', () => {
    display.value = display.value.slice(0, -1);
});

// If returning after a PIN error, re-show the PIN step for the same staff member
@if(session('selected_staff_id') && $errors->any())
    (function() {
        const id   = {{ session('selected_staff_id') }};
        const name = document.querySelector(`[onclick*="selectStaff(${id},"]`)?.textContent.trim() ?? '';
        if (name) selectStaff(id, name);
    })();
@endif
</script>
</body>
</html>
