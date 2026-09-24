<?php

namespace App\Livewire;

use App\Models\Staff;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', params: ['bodyClass' => 'bg-neutral-900 min-h-screen flex items-center justify-center'])]
class StaffLogin extends Component
{
    public $staff;
    public ?int $selectedStaffId = null;
    public string $selectedName = '';
    public string $pin = '';
    public string $error = '';

    public function mount(): void
    {
        $this->staff = Staff::where('active', true)->orderBy('name')->get(['id', 'name']);
    }

    public function selectStaff(?int $id, string $name): void
    {
        $this->selectedStaffId = $id;
        $this->selectedName = $name;
        $this->pin = '';
        $this->error = '';
    }

    public function appendDigit(string $digit): void
    {
        if (strlen($this->pin) < 6) {
            $this->pin .= $digit;
        }
    }

    public function backspace(): void
    {
        $this->pin = substr($this->pin, 0, -1);
    }

    public function clearPin(): void
    {
        $this->pin = '';
    }

    public function loginWithPin(string $pin): mixed
    {
        $this->pin = $pin;
        return $this->login();
    }

    public function login(): mixed
    {
        $staff = Staff::where('id', $this->selectedStaffId)->where('active', true)->first();

        if (! $staff || ! $staff->checkPin($this->pin)) {
            $this->error = 'Incorrect PIN.';
            $this->pin = '';
            $this->dispatch('pin-error');
            return null;
        }

        session([
            'staff_id'   => $staff->id,
            'staff_name' => $staff->name,
            'staff_role' => $staff->role,
        ]);

        $destination = $staff->role === 'cashier' ? '/pos' : '/kitchen';

        return redirect()->intended($destination);
    }

    public function render()
    {
        return view('livewire.staff-login');
    }
}
