<?php

use App\Domain\Notifications\Enums\NotificationChannel;
use App\Domain\Notifications\Enums\NotificationType;

it('routes plan_price_changed through the in-app and email channels', function () {
    expect(NotificationType::PlanPriceChanged->channels())
        ->toBe([NotificationChannel::InApp, NotificationChannel::Email]);
});

it('can be created from its string value', function () {
    expect(NotificationType::from('plan_price_changed'))->toBe(NotificationType::PlanPriceChanged);
});
