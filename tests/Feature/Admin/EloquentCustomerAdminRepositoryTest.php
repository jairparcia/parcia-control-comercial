<?php

use App\Infrastructure\Repository\Admin\EloquentCustomerAdminRepository;
use App\Models\Subscription;
use App\Models\User;

// Helper: creates a Subscription row for the given user.
function makeSubscription(User $user, string $status = 'active'): void
{
    Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_' . uniqid(),
        'stripe_status' => $status,
        'stripe_price'  => 'price_test',
        'quantity'      => 1,
    ]);
}

// --- Status filter: active ---

it('returns only non-archived users with active subscriptions for the active filter', function () {
    $active   = User::factory()->create();
    $inactive = User::factory()->create();
    $archived = User::factory()->create(['archived' => true]);

    makeSubscription($active, 'active');
    makeSubscription($archived, 'active'); // archived with sub — must not appear

    $ids = collect((new EloquentCustomerAdminRepository())->all('active'))->pluck('id');

    expect($ids->all())
        ->toContain($active->id)
        ->not->toContain($inactive->id)
        ->not->toContain($archived->id);
});

it('includes trialing subscriptions in the active filter', function () {
    $user = User::factory()->create();
    makeSubscription($user, 'trialing');

    $ids = collect((new EloquentCustomerAdminRepository())->all('active'))->pluck('id');

    expect($ids->all())->toContain($user->id);
});

// --- Status filter: inactive ---

it('returns only non-archived users without active subscriptions for the inactive filter', function () {
    $active   = User::factory()->create();
    $inactive = User::factory()->create();
    $archived = User::factory()->create(['archived' => true]);

    makeSubscription($active, 'active');

    $ids = collect((new EloquentCustomerAdminRepository())->all('inactive'))->pluck('id');

    expect($ids->all())
        ->toContain($inactive->id)
        ->not->toContain($active->id)
        ->not->toContain($archived->id);
});

it('includes users with only cancelled subscriptions in the inactive filter', function () {
    $user = User::factory()->create();
    makeSubscription($user, 'canceled');

    $ids = collect((new EloquentCustomerAdminRepository())->all('inactive'))->pluck('id');

    expect($ids->all())->toContain($user->id);
});

// --- Status filter: archived ---

it('returns only archived users for the archived filter', function () {
    $regular  = User::factory()->create();
    $archived = User::factory()->create(['archived' => true]);

    $ids = collect((new EloquentCustomerAdminRepository())->all('archived'))->pluck('id');

    expect($ids->all())
        ->toContain($archived->id)
        ->not->toContain($regular->id);
});

// --- Status filter: all ---

it('returns all users including archived for the all filter', function () {
    $regular  = User::factory()->create();
    $archived = User::factory()->create(['archived' => true]);

    $ids = collect((new EloquentCustomerAdminRepository())->all('all'))->pluck('id');

    expect($ids->all())
        ->toContain($regular->id)
        ->toContain($archived->id);
});

// --- hasActiveSub flag ---

it('sets hasActiveSub to true for users with an active subscription', function () {
    $user = User::factory()->create();
    makeSubscription($user, 'active');

    $result = collect((new EloquentCustomerAdminRepository())->all('all'))->firstWhere('id', $user->id);

    expect($result->hasActiveSub)->toBeTrue();
});

it('sets hasActiveSub to false for users with only a cancelled subscription', function () {
    $user = User::factory()->create();
    makeSubscription($user, 'canceled');

    $result = collect((new EloquentCustomerAdminRepository())->all('all'))->firstWhere('id', $user->id);

    expect($result->hasActiveSub)->toBeFalse();
});

it('sets hasActiveSub to false for users with no subscription at all', function () {
    $user = User::factory()->create();

    $result = collect((new EloquentCustomerAdminRepository())->all('all'))->firstWhere('id', $user->id);

    expect($result->hasActiveSub)->toBeFalse();
});

// --- Archive / Restore ---

it('sets archived to true when archiving a user', function () {
    $user = User::factory()->create();

    (new EloquentCustomerAdminRepository())->archive($user->id);

    expect(User::find($user->id)->archived)->toBeTrue();
});

it('sets archived to false when restoring an archived user', function () {
    $user = User::factory()->create(['archived' => true]);

    (new EloquentCustomerAdminRepository())->restore($user->id);

    expect(User::find($user->id)->archived)->toBeFalse();
});
