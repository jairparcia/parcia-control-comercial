<?php

// Stripe price IDs live here — infrastructure config, not in the Plan enum.
// To swap providers: replace price_id values and update StripePaymentGateway.

return [

    'free' => [
        'price_id' => null,
        'quota'    => 10,
        'label'    => 'Gratuito',
    ],

    'starter' => [
        'price_id' => env('PLAN_STARTER_PRICE_ID'),
        'quota'    => 500,
        'label'    => 'Starter',
    ],

    'pro' => [
        'price_id' => env('PLAN_PRO_PRICE_ID'),
        'quota'    => 2000,
        'label'    => 'Pro',
    ],

    'agency' => [
        'price_id' => env('PLAN_AGENCY_PRICE_ID'),
        'quota'    => 10000,
        'label'    => 'Agency',
    ],

    'internal' => [
        'price_id' => null,
        'quota'    => PHP_INT_MAX,
        'label'    => 'Internal',
    ],

];
