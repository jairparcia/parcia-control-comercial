<?php

use App\Infrastructure\Repository\Admin\EloquentPlanAdminRepository;
use App\Infrastructure\Repository\Subscription\CashierSubscriptionRepository;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;

uses(RefreshDatabase::class);

// ── appendLegacyPriceId ───────────────────────────────────────────────────────

it('stores a replaced price ID in legacy_stripe_price_ids', function () {
    $plan = SubscriptionPlan::create([
        'key' => 'pro', 'name' => 'Pro', 'unit_amount' => 30000,
        'stripe_price_id' => 'price_NEW', 'stripe_product_id' => 'prod_ABC',
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 1000,
        'sort_order' => 1, 'active' => true,
    ]);

    $repo = new EloquentPlanAdminRepository();
    $repo->appendLegacyPriceId($plan->id, 'price_OLD');

    expect($plan->fresh()->legacy_stripe_price_ids)->toBe(['price_OLD']);
});

it('does not duplicate a price ID that was already archived', function () {
    $plan = SubscriptionPlan::create([
        'key' => 'pro', 'name' => 'Pro', 'unit_amount' => 30000,
        'stripe_price_id' => 'price_NEW', 'stripe_product_id' => 'prod_ABC',
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 1000,
        'sort_order' => 1, 'active' => true,
        'legacy_stripe_price_ids' => ['price_OLD'],
    ]);

    $repo = new EloquentPlanAdminRepository();
    $repo->appendLegacyPriceId($plan->id, 'price_OLD');

    expect($plan->fresh()->legacy_stripe_price_ids)->toBe(['price_OLD']);
});

it('accumulates multiple replaced price IDs', function () {
    $plan = SubscriptionPlan::create([
        'key' => 'pro', 'name' => 'Pro', 'unit_amount' => 50000,
        'stripe_price_id' => 'price_V3', 'stripe_product_id' => 'prod_ABC',
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 1000,
        'sort_order' => 1, 'active' => true,
        'legacy_stripe_price_ids' => ['price_V1'],
    ]);

    $repo = new EloquentPlanAdminRepository();
    $repo->appendLegacyPriceId($plan->id, 'price_V2');

    expect($plan->fresh()->legacy_stripe_price_ids)->toBe(['price_V1', 'price_V2']);
});

// ── resolvePlan fallback ──────────────────────────────────────────────────────

it('resolves a plan for a subscriber on the current price ID', function () {
    $plan = SubscriptionPlan::create([
        'key' => 'pro', 'name' => 'Pro', 'unit_amount' => 30000,
        'stripe_price_id' => 'price_CURRENT', 'stripe_product_id' => 'prod_ABC',
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 1000,
        'sort_order' => 1, 'active' => true,
    ]);

    $user = User::factory()->create(['stripe_id' => 'cus_TEST']);

    Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_TEST',
        'stripe_status' => 'active',
        'stripe_price'  => 'price_CURRENT',
        'quantity'      => 1,
    ]);

    $repo   = app(CashierSubscriptionRepository::class);
    $result = $repo->getStatus($user->id);

    expect($result->plan->key)->toBe('pro');
});

it('resolves a plan for a subscriber on a legacy price ID', function () {
    SubscriptionPlan::create([
        'key' => 'pro', 'name' => 'Pro', 'unit_amount' => 50000,
        'stripe_price_id' => 'price_NEW', 'stripe_product_id' => 'prod_ABC',
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 1000,
        'sort_order' => 1, 'active' => true,
        'legacy_stripe_price_ids' => ['price_OLD'],
    ]);

    $user = User::factory()->create(['stripe_id' => 'cus_LEGACY']);

    Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_LEGACY',
        'stripe_status' => 'active',
        'stripe_price'  => 'price_OLD',
        'quantity'      => 1,
    ]);

    $repo   = app(CashierSubscriptionRepository::class);
    $result = $repo->getStatus($user->id);

    expect($result->plan->key)->toBe('pro');
});

it('returns null plan when price ID is unknown', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_UNKNOWN']);

    Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_UNKNOWN',
        'stripe_status' => 'active',
        'stripe_price'  => 'price_GHOST',
        'quantity'      => 1,
    ]);

    $repo   = app(CashierSubscriptionRepository::class);
    $result = $repo->getStatus($user->id);

    expect($result->plan)->toBeNull();
});

it('resolves a plan for a subscriber on a non-canonical, admin-created plan key', function () {
    $plan = SubscriptionPlan::create([
        'key' => 'tes', 'name' => 'Custom Test Plan', 'unit_amount' => 15000,
        'stripe_price_id' => 'price_CUSTOM', 'stripe_product_id' => 'prod_CUSTOM',
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 100,
        'sort_order' => 5, 'active' => true,
    ]);

    $user = User::factory()->create(['stripe_id' => 'cus_CUSTOM']);

    Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_CUSTOM',
        'stripe_status' => 'active',
        'stripe_price'  => 'price_CUSTOM',
        'quantity'      => 1,
    ]);

    $repo   = app(CashierSubscriptionRepository::class);
    $result = $repo->getStatus($user->id);

    expect($result->plan->key)->toBe('tes')
        ->and($result->plan->name)->toBe('Custom Test Plan');
});
