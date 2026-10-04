<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ParticipationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;

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
    // イベントのCRUD（index, create, store, show, edit, update, destroy）
    Route::resource('events', EventController::class);
    
    // 参加する / やめる / 支払い済みの切り替え
    Route::post('events/{event}/join', [ParticipationController::class, 'store'])->name('events.join');
    Route::delete('events/{event}/join', [ParticipationController::class, 'destroy'])->name('events.leave');
    Route::patch('events/{event}/participants/{user}/paid', [ParticipationController::class, 'togglePaid'])->name('events.paid');

    // コメント
    Route::post('events/{event}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('events/{event}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});



require __DIR__.'/auth.php';
