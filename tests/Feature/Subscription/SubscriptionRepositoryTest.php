<?php

use App\Domain\Subscription\Enums\Plan;
use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Infrastructure\Repository\Subscription\CashierSubscriptionRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repo = app(CashierSubscriptionRepository::class);
});

// ── Plan gratuito (sin suscripción Stripe) ─────────────────────────────────

it('returns free plan for user without a stripe subscription', function () {
    $user = User::factory()->create();

    $result = $this->repo->getStatus($user->id);

    expect($result->plan)->toBe(Plan::Free)
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

    expect($result->plan)->toBe(Plan::Internal)
        ->and($result->status)->toBe(SubscriptionStatus::Active);
});

it('isActive returns true for internal users', function () {
    $user = User::factory()->internal()->create();

    expect($this->repo->isActive($user->id))->toBeTrue();
});
