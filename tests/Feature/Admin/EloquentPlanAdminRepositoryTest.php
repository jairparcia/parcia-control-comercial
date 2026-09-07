<?php

use App\Domain\Admin\Entities\UpdateAdminPlanInputDTO;
use App\Infrastructure\Repository\Admin\EloquentPlanAdminRepository;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

// ── Helper ────────────────────────────────────────────────────────────────────

function makePlan(array $attrs = []): SubscriptionPlan
{
    return SubscriptionPlan::create(array_merge([
        'key'        => 'pro',
        'name'       => 'Pro',
        'unit_amount' => 30000,
        'currency'   => 'MXN',
        'interval'   => 'month',
        'quota'      => 1000,
        'sort_order' => 1,
        'active'     => true,
    ], $attrs));
}

// ── update() ──────────────────────────────────────────────────────────────────

it('update() saves name, description, features, quota and sort_order', function () {
    $plan = makePlan(['name' => 'Old', 'quota' => 500, 'sort_order' => 1]);

    $dto = new UpdateAdminPlanInputDTO(
        name:        'New Name',
        description: 'Updated desc',
        features:    ['Feature A', 'Feature B'],
        quota:       2000,
        sortOrder:   3,
    );

    $result = (new EloquentPlanAdminRepository())->update($plan->id, $dto);

    expect($result->name)->toBe('New Name')
        ->and($result->description)->toBe('Updated desc')
        ->and($result->quota)->toBe(2000)
        ->and($result->sortOrder)->toBe(3);

    $plan->refresh();
    expect($plan->name)->toBe('New Name')
        ->and($plan->features)->toBe(['Feature A', 'Feature B'])
        ->and($plan->quota)->toBe(2000);
});

it('update() does not modify unit_amount, currency, or interval', function () {
    $plan = makePlan(['unit_amount' => 30000, 'currency' => 'MXN', 'interval' => 'month']);

    $dto = new UpdateAdminPlanInputDTO(
        name: 'Pro', description: '', features: [], quota: 1000, sortOrder: 1,
    );

    (new EloquentPlanAdminRepository())->update($plan->id, $dto);

    $plan->refresh();
    expect($plan->unit_amount)->toBe(30000)
        ->and($plan->currency)->toBe('MXN')
        ->and($plan->interval)->toBe('month');
});

it('update() does not modify stripe_price_id', function () {
    $plan = makePlan(['stripe_price_id' => 'price_KEEP']);

    $dto = new UpdateAdminPlanInputDTO(
        name: 'Pro', description: '', features: [], quota: 1000, sortOrder: 1,
    );

    (new EloquentPlanAdminRepository())->update($plan->id, $dto);

    expect($plan->fresh()->stripe_price_id)->toBe('price_KEEP');
});

// ── setStripeIds() ────────────────────────────────────────────────────────────

it('setStripeIds() stores all Stripe-related fields in the DB', function () {
    $plan = makePlan(['stripe_product_id' => null, 'stripe_price_id' => null, 'unit_amount' => 0]);

    (new EloquentPlanAdminRepository())->setStripeIds(
        id:             $plan->id,
        productId:      'prod_NEW',
        priceId:        'price_NEW',
        unitAmountCents: 50000,
        currency:       'USD',
        interval:       'year',
    );

    $plan->refresh();
    expect($plan->stripe_product_id)->toBe('prod_NEW')
        ->and($plan->stripe_price_id)->toBe('price_NEW')
        ->and($plan->unit_amount)->toBe(50000)
        ->and($plan->currency)->toBe('USD')
        ->and($plan->interval)->toBe('year');
});

// ── updateDefaultPrice() ──────────────────────────────────────────────────────

it('updateDefaultPrice() swaps the price ID and syncs amount/currency/interval', function () {
    $plan = makePlan([
        'stripe_price_id' => 'price_OLD',
        'unit_amount'     => 30000,
        'currency'        => 'MXN',
        'interval'        => 'month',
    ]);

    (new EloquentPlanAdminRepository())->updateDefaultPrice(
        id:             $plan->id,
        stripePriceId:  'price_NEW',
        unitAmountCents: 80000,
        currency:       'USD',
        interval:       'year',
    );

    $plan->refresh();
    expect($plan->stripe_price_id)->toBe('price_NEW')
        ->and($plan->unit_amount)->toBe(80000)
        ->and($plan->currency)->toBe('USD')
        ->and($plan->interval)->toBe('year');
});

// ── countActiveSubscriptionsForPrice() ───────────────────────────────────────

it('countActiveSubscriptionsForPrice() counts active and trialing subscriptions', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $userC = User::factory()->create();

    $makeSubscription = function (User $user, string $status): void {
        $sub = Subscription::create([
            'user_id'       => $user->id,
            'type'          => 'default',
            'stripe_id'     => 'sub_' . uniqid(),
            'stripe_status' => $status,
            'stripe_price'  => 'price_TEST',
            'quantity'      => 1,
        ]);

        \Illuminate\Support\Facades\DB::table('subscription_items')->insert([
            'subscription_id' => $sub->id,
            'stripe_id'       => 'si_' . uniqid(),
            'stripe_price'    => 'price_TEST',
            'quantity'        => 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    };

    $makeSubscription($userA, 'active');
    $makeSubscription($userB, 'trialing');
    $makeSubscription($userC, 'canceled');

    $count = (new EloquentPlanAdminRepository())->countActiveSubscriptionsForPrice('price_TEST');

    expect($count)->toBe(2);
});

it('countActiveSubscriptionsForPrice() returns 0 when there are no active subscriptions', function () {
    $count = (new EloquentPlanAdminRepository())->countActiveSubscriptionsForPrice('price_NONE');

    expect($count)->toBe(0);
});
