<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Moo's</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
    </style>
</head>
<body class="bg-neutral-100 min-h-screen">
<div class="max-w-3xl mx-auto p-6">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Admin</h1>
        <a href="{{ route('kitchen.index') }}" class="text-sm underline text-neutral-500">Back to kitchen</a>
    </div>

    @if(session('success'))
        <div class="mb-5 px-4 py-2 bg-green-50 border border-green-200 text-green-800 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- Staff --}}
    {{-- ============================================================ --}}
    <section class="mb-10">
        <h2 class="text-lg font-semibold mb-4">Staff</h2>

        <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Name</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Role</th>
                        <th class="text-left px-4 py-2 font-medium text-neutral-600">Active</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($staff as $member)
                    <tr class="hover:bg-neutral-50 align-top">
                        <td class="px-4 py-3 font-medium">{{ $member->name }}</td>
                        <td class="px-4 py-3 capitalize text-neutral-600">{{ $member->role }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.staff.update', $member) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="name" value="{{ $member->name }}">
                                <input type="hidden" name="role" value="{{ $member->role }}">
                                <input type="checkbox" name="active" value="1"
                                    {{ $member->active ? 'checked' : '' }}
                                    onchange="this.form.submit()" class="rounded">
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right space-y-1">
                            {{-- Edit name / role --}}
                            <details class="inline-block">
                                <summary class="cursor-pointer text-neutral-600 underline text-xs">Edit</summary>
                                <div class="absolute z-10 mt-1 right-6 bg-white border rounded-lg shadow-lg p-4 w-64">
                                    <form method="POST" action="{{ route('admin.staff.update', $member) }}" class="space-y-2">
                                        @csrf @method('PUT')
                                        <div>
                                            <label class="block text-xs text-neutral-500 mb-0.5">Name</label>
                                            <input type="text" name="name" value="{{ $member->name }}" required
                                                class="w-full border rounded px-2 py-1 text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-neutral-500 mb-0.5">Role</label>
                                            <select name="role" class="w-full border rounded px-2 py-1 text-sm">
                                                <option value="kitchen"  {{ $member->role === 'kitchen'  ? 'selected' : '' }}>Kitchen</option>
                                                <option value="cashier"  {{ $member->role === 'cashier'  ? 'selected' : '' }}>Cashier</option>
                                                <option value="manager"  {{ $member->role === 'manager'  ? 'selected' : '' }}>Manager</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="active" value="{{ $member->active ? '1' : '0' }}">
                                        <button class="w-full bg-black text-white text-sm py-1.5 rounded">Save</button>
                                    </form>
                                </div>
                            </details>

                            {{-- Change PIN --}}
                            <details class="inline-block">
                                <summary class="cursor-pointer text-neutral-600 underline text-xs">PIN</summary>
                                <div class="absolute z-10 mt-1 right-6 bg-white border rounded-lg shadow-lg p-4 w-56">
                                    <form method="POST" action="{{ route('admin.staff.pin', $member) }}" class="space-y-2">
                                        @csrf @method('PUT')
                                        <div>
                                            <label class="block text-xs text-neutral-500 mb-0.5">New PIN (4–6 digits)</label>
                                            <input type="password" name="pin" inputmode="numeric"
                                                pattern="\d{4,6}" minlength="4" maxlength="6" required
                                                placeholder="••••"
                                                class="w-full border rounded px-2 py-1 text-sm tracking-widest">
                                        </div>
                                        <button class="w-full bg-black text-white text-sm py-1.5 rounded">Update PIN</button>
                                    </form>
                                </div>
                            </details>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" class="inline"
                                onsubmit="return confirm('Remove {{ addslashes($member->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 underline text-xs">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @if($staff->isEmpty())
                        <tr><td colspan="4" class="px-4 py-3 text-neutral-400 text-center">No staff yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Add staff --}}
        <div class="mt-4 bg-white rounded-lg border shadow-sm p-4">
            <h3 class="text-sm font-medium mb-3">Add Staff Member</h3>
            <form method="POST" action="{{ route('admin.staff.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Name</label>
                    <input type="text" name="name" placeholder="Full name" required
                        class="border rounded px-2 py-1.5 text-sm w-36">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">PIN (4–6 digits)</label>
                    <input type="password" name="pin" inputmode="numeric"
                        pattern="\d{4,6}" minlength="4" maxlength="6" required
                        placeholder="••••"
                        class="border rounded px-2 py-1.5 text-sm w-24 tracking-widest">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Role</label>
                    <select name="role" class="border rounded px-2 py-1.5 text-sm">
                        <option value="kitchen">Kitchen</option>
                        <option value="cashier">Cashier</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
                <button class="bg-black text-white text-sm px-4 py-1.5 rounded">Add</button>
            </form>
            @error('pin')
                <p class="mt-2 text-red-600 text-xs">{{ $message }}</p>
            @enderror
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Settings --}}
    {{-- ============================================================ --}}
    <section>
        <h2 class="text-lg font-semibold mb-4">SumUp Settings</h2>

        <div class="bg-white rounded-lg border shadow-sm p-5">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">API Key</label>
                    <input type="password" name="sumup_api_key"
                        value="{{ $settings['sumup_api_key'] }}"
                        required autocomplete="off"
                        class="w-full border rounded px-3 py-2 text-sm font-mono">
                    <p class="text-xs text-neutral-400 mt-1">Starts with <code>sup_sk_</code></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Merchant Code</label>
                    <input type="text" name="sumup_merchant_code"
                        value="{{ $settings['sumup_merchant_code'] }}"
                        required autocomplete="off"
                        class="w-full border rounded px-3 py-2 text-sm font-mono">
                </div>
                <button class="bg-black text-white text-sm px-5 py-2 rounded">Save Settings</button>
            </form>
        </div>
    </section>

</div>
</body>
</html>
