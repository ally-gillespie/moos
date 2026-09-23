<?php

namespace App\Livewire;

use App\Models\Setting;
use App\Models\Staff;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title("Admin — Moo's")]
class Admin extends Component
{
    // Add staff form
    public string $staffName = '';
    public string $staffPin = '';
    public string $staffRole = 'kitchen';

    // Settings form
    public string $sumupApiKey = '';
    public string $sumupMerchantCode = '';
    public string $twilioAccountSid = '';
    public string $twilioAuthToken = '';
    public string $twilioFromNumber = '';

    // Edit state
    public ?int $editStaffId = null;
    public string $editStaffName = '';
    public string $editStaffRole = 'kitchen';
    public bool $editStaffActive = true;

    // PIN edit state
    public ?int $pinEditStaffId = null;
    public string $newPin = '';

    // Flash messages (per section)
    public string $staffFlash = '';
    public string $sumupFlash = '';
    public string $twilioFlash = '';

    public function mount(): void
    {
        $this->sumupApiKey       = Setting::get('sumup_api_key', '');
        $this->sumupMerchantCode = Setting::get('sumup_merchant_code', '');
        $this->twilioAccountSid = Setting::get('twilio_account_sid', '');
        $this->twilioAuthToken  = Setting::get('twilio_auth_token', '');
        $this->twilioFromNumber = Setting::get('twilio_from_number', '');
    }

    public function storeStaff(): void
    {
        $this->validate([
            'staffName' => 'required|string|max:100',
            'staffPin'  => 'required|string|min:4|max:6|regex:/^\d+$/',
            'staffRole' => 'required|in:cashier,kitchen,manager',
        ]);

        Staff::create([
            'name' => $this->staffName,
            'pin'  => $this->staffPin,
            'role' => $this->staffRole,
        ]);

        $this->staffName = '';
        $this->staffPin  = '';
        $this->staffRole = 'kitchen';
        $this->staffFlash = 'Staff member added.';
    }

    public function startEditStaff(int $id): void
    {
        $staff = Staff::findOrFail($id);
        $this->editStaffId     = $id;
        $this->editStaffName   = $staff->name;
        $this->editStaffRole   = $staff->role;
        $this->editStaffActive = $staff->active;
    }

    public function cancelEditStaff(): void
    {
        $this->editStaffId = null;
    }

    public function updateStaff(int $id): void
    {
        $this->validate([
            'editStaffName' => 'required|string|max:100',
            'editStaffRole' => 'required|in:cashier,kitchen,manager',
        ]);

        $staff = Staff::findOrFail($id);
        $staff->update([
            'name'   => $this->editStaffName,
            'role'   => $this->editStaffRole,
            'active' => $this->editStaffActive,
        ]);

        $this->editStaffId = null;
        $this->staffFlash = 'Staff updated.';
    }

    public function toggleStaffActive(int $id): void
    {
        $staff = Staff::findOrFail($id);
        $staff->update(['active' => ! $staff->active]);
    }

    public function startPinEdit(int $id): void
    {
        $this->pinEditStaffId = $id;
        $this->newPin = '';
    }

    public function cancelPinEdit(): void
    {
        $this->pinEditStaffId = null;
        $this->newPin = '';
    }

    public function updatePin(int $id): void
    {
        $this->validate([
            'newPin' => 'required|string|min:4|max:6|regex:/^\d+$/',
        ]);

        $staff = Staff::findOrFail($id);
        $staff->update(['pin' => $this->newPin]);

        $this->pinEditStaffId = null;
        $this->newPin = '';
        $this->staffFlash = 'PIN updated for ' . $staff->name . '.';
    }

    public function destroyStaff(int $id): void
    {
        Staff::findOrFail($id)->delete();
        $this->staffFlash = 'Staff member removed.';
    }

    public function updateSettings(): void
    {
        $this->validate([
            'sumupApiKey'       => 'required|string|max:200',
            'sumupMerchantCode' => 'required|string|max:100',
        ]);

        Setting::set('sumup_api_key',       $this->sumupApiKey);
        Setting::set('sumup_merchant_code', $this->sumupMerchantCode);

        $this->sumupFlash = 'Settings saved.';
    }

    public function testTwilio(): void
    {
        $accountSid = Setting::get('twilio_account_sid');
        $authToken  = Setting::get('twilio_auth_token');

        if (! $accountSid || ! $authToken) {
            $this->twilioFlash = 'Error: credentials not saved yet.';
            return;
        }

        try {
            $client  = new \Twilio\Rest\Client($accountSid, $authToken);
            $account = $client->api->v2010->accounts($accountSid)->fetch();
            $this->twilioFlash = 'Connection OK — account: ' . $account->friendlyName;
        } catch (\Throwable $e) {
            $this->twilioFlash = 'Error: ' . $e->getMessage();
        }
    }

    public function updateTwilioSettings(): void
    {
        $this->validate([
            'twilioAccountSid' => 'nullable|string|max:100',
            'twilioAuthToken'  => 'nullable|string|max:100',
            'twilioFromNumber' => 'nullable|string|max:20',
        ]);

        Setting::set('twilio_account_sid', $this->twilioAccountSid);
        Setting::set('twilio_auth_token',  $this->twilioAuthToken);
        Setting::set('twilio_from_number', $this->twilioFromNumber);

        $this->twilioFlash = 'Twilio settings saved.';
    }

    public function render()
    {
        return view('livewire.admin', [
            'staff' => Staff::orderBy('name')->get(),
        ]);
    }
}
