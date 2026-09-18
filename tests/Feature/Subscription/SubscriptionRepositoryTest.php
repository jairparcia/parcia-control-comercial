<?php

use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Infrastructure\Repository\Subscription\CashierSubscriptionRepository;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\PlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PlansSeeder::class);
    $this->repo = app(CashierSubscriptionRepository::class);
});

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeSubscriptionRepoTestPlan(array $attrs = []): SubscriptionPlan
{
    return SubscriptionPlan::create(array_merge([
        'key'               => 'pro',
        'name'              => 'Pro',
        'unit_amount'       => 80000,
        'currency'          => 'USD',
        'interval'          => 'year',
        'quota'             => 1000,
        'sort_order'        => 1,
        'active'            => true,
        'stripe_product_id' => 'prod_TEST',
    ], $attrs));
}

function attachSubscriptionToProduct(User $user, string $status, string $stripeProduct = 'prod_TEST'): void
{
    $sub = Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_' . uniqid(),
        'stripe_status' => $status,
        'stripe_price'  => 'price_TEST',
        'quantity'      => 1,
    ]);

    DB::table('subscription_items')->insert([
        'subscription_id' => $sub->id,
        'stripe_id'       => 'si_' . uniqid(),
        'stripe_product'  => $stripeProduct,
        'stripe_price'    => 'price_TEST',
        'quantity'        => 1,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);
}

// ── Plan gratuito (sin suscripción Stripe) ─────────────────────────────────

it('returns free plan for user without a stripe subscription', function () {
    $user = User::factory()->create();

    $result = $this->repo->getStatus($user->id);

    expect($result->plan->key)->toBe('free')
        ->and($result->status)->toBe(SubscriptionStatus::Active)
        ->and($result->renewsAt)->toBeNull();
});

it('isActive returns true for users without a stripe subscription', function () {
    $user = User::factory()->create();

    expect($this->repo->isActive($user->id))->toBeTrue();
});

// ── Plan internal ─────────────────────────────────────────────────────────

it('returns internal plan for internal users regardless of stripe', function () {
    $user = User::factory()->internal()->create();

    $result = $this->repo->getStatus($user->id);

    expect($result->plan->key)->toBe('internal')
        ->and($result->status)->toBe(SubscriptionStatus::Active);
});

it('isActive returns true for internal users', function () {
    $user = User::factory()->internal()->create();

    expect($this->repo->isActive($user->id))->toBeTrue();
});

// ── findActiveSubscriberUserIdsByPlan() ──────────────────────────────────────

it('returns an empty array when the plan does not exist', function () {
    expect($this->repo->findActiveSubscriberUserIdsByPlan(999999))->toBe([]);
});

it('returns an empty array when the plan has no stripe_product_id', function () {
    $plan = makeSubscriptionRepoTestPlan(['stripe_product_id' => null]);

    expect($this->repo->findActiveSubscriberUserIdsByPlan($plan->id))->toBe([]);
});

it('returns user ids with an active or trialing subscription to the plan product', function () {
    $plan = makeSubscriptionRepoTestPlan();

    $activeUser   = User::factory()->create();
    $trialingUser = User::factory()->create();

    attachSubscriptionToProduct($activeUser, 'active');
    attachSubscriptionToProduct($trialingUser, 'trialing');

    expect($this->repo->findActiveSubscriberUserIdsByPlan($plan->id))
        ->toEqualCanonicalizing([$activeUser->id, $trialingUser->id]);
});

it('excludes canceled subscriptions', function () {
    $plan = makeSubscriptionRepoTestPlan();

    $activeUser   = User::factory()->create();
    $canceledUser = User::factory()->create();

    attachSubscriptionToProduct($activeUser, 'active');
    attachSubscriptionToProduct($canceledUser, 'canceled');

    expect($this->repo->findActiveSubscriberUserIdsByPlan($plan->id))->toBe([$activeUser->id]);
});

it('excludes subscribers of a different plan product', function () {
    $plan      = makeSubscriptionRepoTestPlan(['stripe_product_id' => 'prod_TARGET']);
    $otherUser = User::factory()->create();

    attachSubscriptionToProduct($otherUser, 'active', 'prod_OTHER');

    expect($this->repo->findActiveSubscriberUserIdsByPlan($plan->id))->toBe([]);
});

it('returns unique user ids even when the user has multiple items on the plan product', function () {
    $plan = makeSubscriptionRepoTestPlan();
    $user = User::factory()->create();

    $sub = Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_' . uniqid(),
        'stripe_status' => 'active',
        'stripe_price'  => 'price_TEST',
        'quantity'      => 1,
    ]);

    DB::table('subscription_items')->insert([
        ['subscription_id' => $sub->id, 'stripe_id' => 'si_' . uniqid(), 'stripe_product' => 'prod_TEST', 'stripe_price' => 'price_A', 'quantity' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['subscription_id' => $sub->id, 'stripe_id' => 'si_' . uniqid(), 'stripe_product' => 'prod_TEST', 'stripe_price' => 'price_B', 'quantity' => 1, 'created_at' => now(), 'updated_at' => now()],
    ]);

    expect($this->repo->findActiveSubscriberUserIdsByPlan($plan->id))->toBe([$user->id]);
});
