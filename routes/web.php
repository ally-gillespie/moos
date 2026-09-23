<?php

use App\Http\Controllers\Auth\PinLoginController;
use App\Http\Controllers\PaymentController;
use App\Livewire\Admin;
use App\Livewire\Archive;
use App\Livewire\KitchenBoard;
use App\Livewire\MenuManager;
use App\Livewire\OrderForm;
use App\Livewire\PosForm;
use App\Livewire\QueueDisplay;
use App\Livewire\StaffLogin;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes();

// --- Public order form (customer facing) ---
Route::get('/order', OrderForm::class)->name('order.create');

// --- Payment (SumUp hosted checkout) ---
Route::get('/payment/{order}/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
Route::get('/payment/{order}/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// --- Public queue display (screen on the wall, no auth) ---
Route::get('/queue', QueueDisplay::class)->name('queue.index');

// --- Staff PIN auth ---
Route::get('/staff/login', StaffLogin::class)->name('kitchen.login');
Route::post('/staff/logout', [PinLoginController::class, 'logout'])->name('kitchen.logout');

// --- Kitchen (kitchen + manager) ---
Route::middleware(['staff.session', 'staff.role:kitchen,manager'])->group(function () {
    Route::get('/kitchen', KitchenBoard::class)->name('kitchen.index');
});

// --- POS (cashier + manager) ---
Route::middleware(['staff.session', 'staff.role:cashier,manager'])->group(function () {
    Route::get('/pos', PosForm::class)->name('pos.create');
});

// --- Manager only ---
Route::middleware(['staff.session', 'staff.role:manager'])->group(function () {
    Route::get('/admin', Admin::class)->name('admin.index');
    Route::get('/archive', Archive::class)->name('archive.index');
    Route::get('/menu', MenuManager::class)->name('menu.index');
});
