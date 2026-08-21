<?php

use App\Domain\Subscription\Enums\SubscriptionStatus;

it('isActive returns true for active and trialing statuses', function () {
    expect(SubscriptionStatus::Active->isActive())->toBeTrue()
        ->and(SubscriptionStatus::Trialing->isActive())->toBeTrue();
});

it('isActive returns false for inactive statuses', function () {
    expect(SubscriptionStatus::PastDue->isActive())->toBeFalse()
        ->and(SubscriptionStatus::Cancelled->isActive())->toBeFalse()
        ->and(SubscriptionStatus::None->isActive())->toBeFalse();
});

it('returns correct label for each status', function () {
    expect(SubscriptionStatus::Active->label())->toBeString()->not->toBeEmpty()
        ->and(SubscriptionStatus::Trialing->label())->toBeString()->not->toBeEmpty()
        ->and(SubscriptionStatus::PastDue->label())->toBeString()->not->toBeEmpty()
        ->and(SubscriptionStatus::Cancelled->label())->toBeString()->not->toBeEmpty()
        ->and(SubscriptionStatus::None->label())->toBeString()->not->toBeEmpty();
});
