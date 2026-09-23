<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class PinLoginController extends Controller
{
    /**
     * Staff enter a PIN on the kitchen/POS keypad screen. On success we
     * log them into the session (so Blade + Echo private-channel auth
     * work normally) rather than issuing an API token, since these are
     * shared devices staying logged in for a shift, not separate API clients.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|integer|exists:staff,id',
            'pin'      => 'required|string|min:4|max:6',
        ]);

        $staff = Staff::where('id', $validated['staff_id'])->where('active', true)->first();

        if (! $staff || ! $staff->checkPin($validated['pin'])) {
            return back()->with('selected_staff_id', $validated['staff_id'])
                         ->withErrors(['pin' => 'Incorrect PIN.']);
        }

        $request->session()->put('staff_id', $staff->id);
        $request->session()->put('staff_name', $staff->name);
        $request->session()->put('staff_role', $staff->role);

        $destination = match ($staff->role) {
            'cashier' => route('pos.create'),
            default   => route('kitchen.index'),
        };

        return redirect()->intended($destination);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['staff_id', 'staff_name', 'staff_role']);

        return redirect()->route('kitchen.login');
    }
}
