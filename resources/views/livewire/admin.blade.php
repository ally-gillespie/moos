<div class="max-w-3xl mx-auto p-6">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Admin</h1>
        <a wire:navigate href="{{ route('kitchen.index') }}" class="text-sm underline text-neutral-500">Back to kitchen</a>
    </div>

    {{-- ================================================================ --}}
    {{-- Staff --}}
    {{-- ================================================================ --}}
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
                        <td class="px-4 py-3 font-medium">
                            @if($editStaffId === $member->id)
                                <input type="text" wire:model="editStaffName"
                                    class="border rounded px-2 py-1 text-sm w-36">
                            @else
                                {{ $member->name }}
                            @endif
                        </td>
                        <td class="px-4 py-3 capitalize text-neutral-600">
                            @if($editStaffId === $member->id)
                                <select wire:model="editStaffRole" class="border rounded px-2 py-1 text-sm">
                                    <option value="kitchen">Kitchen</option>
                                    <option value="cashier">Cashier</option>
                                    <option value="manager">Manager</option>
                                </select>
                            @else
                                {{ $member->role }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($editStaffId === $member->id)
                                <input type="checkbox" wire:model="editStaffActive" class="rounded">
                            @else
                                <input type="checkbox"
                                    wire:click="toggleStaffActive({{ $member->id }})"
                                    {{ $member->active ? 'checked' : '' }}
                                    class="rounded">
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            @if($editStaffId === $member->id)
                                <button type="button" wire:click="updateStaff({{ $member->id }})"
                                    class="text-xs bg-black text-white px-2 py-1 rounded">Save</button>
                                <button type="button" wire:click="cancelEditStaff"
                                    class="text-xs text-neutral-500 underline">Cancel</button>
                            @elseif($pinEditStaffId === $member->id)
                                <input type="password" wire:model="newPin"
                                    inputmode="numeric" pattern="\d{4,6}" minlength="4" maxlength="6"
                                    placeholder="New PIN"
                                    class="border rounded px-2 py-1 text-sm w-24 tracking-widest">
                                <button type="button" wire:click="updatePin({{ $member->id }})"
                                    class="text-xs bg-black text-white px-2 py-1 rounded">Save PIN</button>
                                <button type="button" wire:click="cancelPinEdit"
                                    class="text-xs text-neutral-500 underline">Cancel</button>
                            @else
                                <button type="button" wire:click="startEditStaff({{ $member->id }})"
                                    class="text-neutral-600 underline text-xs">Edit</button>
                                <button type="button" wire:click="startPinEdit({{ $member->id }})"
                                    class="text-neutral-600 underline text-xs">PIN</button>
                                <button type="button"
                                    wire:click="destroyStaff({{ $member->id }})"
                                    wire:confirm="Remove {{ addslashes($member->name) }}?"
                                    class="text-red-600 underline text-xs">Remove</button>
                            @endif
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
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Name</label>
                    <input type="text" wire:model="staffName" placeholder="Full name"
                        class="border rounded px-2 py-1.5 text-sm w-36">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">PIN (4–6 digits)</label>
                    <input type="password" wire:model="staffPin" inputmode="numeric"
                        pattern="\d{4,6}" minlength="4" maxlength="6"
                        placeholder="••••"
                        class="border rounded px-2 py-1.5 text-sm w-24 tracking-widest">
                </div>
                <div>
                    <label class="block text-xs text-neutral-500 mb-0.5">Role</label>
                    <select wire:model="staffRole" class="border rounded px-2 py-1.5 text-sm">
                        <option value="kitchen">Kitchen</option>
                        <option value="cashier">Cashier</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
                <button type="button" wire:click="storeStaff"
                    class="bg-black text-white text-sm px-4 py-1.5 rounded">Add</button>
            </div>
            @error('staffPin') <p class="mt-2 text-red-600 text-xs">{{ $message }}</p> @enderror
            @error('staffName') <p class="mt-2 text-red-600 text-xs">{{ $message }}</p> @enderror
            @if($staffFlash)
                <p class="mt-3 text-sm text-green-700">{{ $staffFlash }}</p>
            @endif
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- Twilio Settings --}}
    {{-- ================================================================ --}}
    <section class="mb-10">
        <h2 class="text-lg font-semibold mb-4">Twilio SMS Settings</h2>

        <div class="bg-white rounded-lg border shadow-sm p-5">
            <p class="text-sm text-neutral-500 mb-4">Used to send customers an SMS when their order is ready. Find these on your <strong>Twilio Console dashboard home page</strong>. Leave blank to disable.</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Account SID</label>
                    <input type="text" wire:model="twilioAccountSid"
                        placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                        autocomplete="off"
                        class="w-full border rounded px-3 py-2 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Auth Token</label>
                    <input type="password" wire:model="twilioAuthToken"
                        autocomplete="off"
                        class="w-full border rounded px-3 py-2 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">From Number</label>
                    <input type="text" wire:model="twilioFromNumber"
                        placeholder="+441234567890"
                        autocomplete="off"
                        class="w-full border rounded px-3 py-2 text-sm font-mono">
                    <p class="text-xs text-neutral-400 mt-1">Your Twilio phone number in E.164 format</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <button type="button" wire:click="updateTwilioSettings"
                        class="bg-black text-white text-sm px-5 py-2 rounded">Save Twilio Settings</button>
                    <button type="button" wire:click="testTwilio"
                        class="border text-sm px-5 py-2 rounded text-neutral-600 hover:bg-neutral-50">Test connection</button>
                    @if($twilioFlash)
                        <p class="text-sm {{ str_starts_with($twilioFlash, 'Error') ? 'text-red-600' : 'text-green-700' }}">{{ $twilioFlash }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================ --}}
    {{-- Settings --}}
    {{-- ================================================================ --}}
    <section>
        <h2 class="text-lg font-semibold mb-4">SumUp Settings</h2>

        <div class="bg-white rounded-lg border shadow-sm p-5">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">API Key</label>
                    <input type="password" wire:model="sumupApiKey"
                        autocomplete="off"
                        class="w-full border rounded px-3 py-2 text-sm font-mono">
                    <p class="text-xs text-neutral-400 mt-1">Starts with <code>sup_sk_</code></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-1">Merchant Code</label>
                    <input type="text" wire:model="sumupMerchantCode"
                        autocomplete="off"
                        class="w-full border rounded px-3 py-2 text-sm font-mono">
                </div>
                <button type="button" wire:click="updateSettings"
                    class="bg-black text-white text-sm px-5 py-2 rounded">Save Settings</button>
                @if($sumupFlash)
                    <p class="text-sm text-green-700">{{ $sumupFlash }}</p>
                @endif
            </div>
            @error('sumupApiKey') <p class="mt-2 text-red-600 text-xs">{{ $message }}</p> @enderror
        </div>
    </section>

</div>
