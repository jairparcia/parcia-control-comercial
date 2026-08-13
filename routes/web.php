<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

// DEV ONLY — remove before deploy
if (app()->isLocal()) {
    Route::get('dashboard-dummy', function () {
        return view('dev.dashboard-dummy');
    })->name('dashboard-dummy');
}

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('creators', 'creators')->name('creators');
    Route::view('settings', 'settings')->name('settings');
    Route::get('billing', fn() => redirect('/'))->name('billing');
});

require __DIR__.'/auth.php';
