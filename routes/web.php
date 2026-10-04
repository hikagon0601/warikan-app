<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettlementController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth'])->group(function () {
    // 割り勘グループのCRUD
    Route::resource('groups', GroupController::class);

    // メンバーの追加 / 外す
    Route::post('groups/{group}/members', [MemberController::class, 'store'])->name('groups.members.store');
    Route::delete('groups/{group}/members/{user}', [MemberController::class, 'destroy'])->name('groups.members.destroy');

    // 支払いの記録
    Route::get('groups/{group}/payments/create', [PaymentController::class, 'create'])->name('groups.payments.create');
    Route::post('groups/{group}/payments', [PaymentController::class, 'store'])->name('groups.payments.store');
    Route::delete('groups/{group}/payments/{payment}', [PaymentController::class, 'destroy'])->name('groups.payments.destroy');

    // 精算した記録
    Route::post('groups/{group}/settlements', [SettlementController::class, 'store'])->name('groups.settlements.store');
});

require __DIR__.'/auth.php';