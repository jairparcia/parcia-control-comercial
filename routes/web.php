<?php

use App\Http\Controllers\Subscription\BillingPortalController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

// Dev-only route — remove before deploy
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

// Admin — internal users only (enforced in backend middleware, not just UI)
Route::middleware(['auth', 'requires.internal'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('plans', 'admin.plans')->name('plans');
    Route::view('subscriptions', 'admin.subscriptions')->name('subscriptions');
    Route::view('customers', 'admin.customers')->name('customers');
});

// Stripe webhooks — no auth middleware, verified by Stripe signature
Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

require __DIR__.'/auth.php';
