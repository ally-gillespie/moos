<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Staff;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index', [
            'staff'    => Staff::orderBy('name')->get(),
            'settings' => [
                'sumup_api_key'      => Setting::get('sumup_api_key'),
                'sumup_merchant_code' => Setting::get('sumup_merchant_code'),
            ],
        ]);
    }

    public function storeStaff(Request $r)
    {
        $v = $r->validate([
            'name' => 'required|string|max:100',
            'pin'  => 'required|string|min:4|max:6|regex:/^\d+$/',
            'role' => 'required|in:cashier,kitchen,manager',
        ]);

        Staff::create($v);

        return redirect()->route('admin.index')->with('success', 'Staff member added.');
    }

    public function updateStaff(Request $r, Staff $staff)
    {
        $v = $r->validate([
            'name'   => 'required|string|max:100',
            'role'   => 'required|in:cashier,kitchen,manager',
            'active' => 'boolean',
        ]);

        $staff->update([
            'name'   => $v['name'],
            'role'   => $v['role'],
            'active' => isset($v['active']) ? $v['active'] : false,
        ]);

        return redirect()->route('admin.index')->with('success', 'Staff updated.');
    }

    public function updatePin(Request $r, Staff $staff)
    {
        $r->validate([
            'pin' => 'required|string|min:4|max:6|regex:/^\d+$/',
        ]);

        $staff->update(['pin' => $r->pin]);

        return redirect()->route('admin.index')->with('success', 'PIN updated for ' . $staff->name . '.');
    }

    public function destroyStaff(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('admin.index')->with('success', 'Staff member removed.');
    }

    public function updateSettings(Request $r)
    {
        $v = $r->validate([
            'sumup_api_key'      => 'required|string|max:200',
            'sumup_merchant_code' => 'required|string|max:100',
        ]);

        Setting::set('sumup_api_key',      $v['sumup_api_key']);
        Setting::set('sumup_merchant_code', $v['sumup_merchant_code']);

        return redirect()->route('admin.index')->with('success', 'Settings saved.');
    }
}
