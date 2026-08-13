<?php

use App\Http\Controllers\Auth\GoogleCallbackController;
use App\Http\Controllers\Auth\GoogleRedirectController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('login', 'auth.login')->name('login');
    Route::get('auth/google', GoogleRedirectController::class)->name('auth.google');
    Route::get('auth/google/callback', GoogleCallbackController::class)->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', App\Livewire\Actions\Logout::class)->name('logout');
});
