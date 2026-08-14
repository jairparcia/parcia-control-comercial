<?php

use App\Http\Controllers\Subscription\BillingPortalController;
use App\Http\Controllers\Webhook\StripeWebhookController;
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
    Route::view('onboarding', 'onboarding')->name('onboarding');

    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('creators', 'creators')->name('creators');
    Route::view('settings', 'settings')->name('settings');
    Route::view('billing', 'billing')->name('billing');
    Route::get('billing/portal', BillingPortalController::class)->name('billing.portal');
});

// Stripe webhooks — sin middleware auth, firmados por Stripe
Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

require __DIR__.'/auth.php';
