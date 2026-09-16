<?php

use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationChannel;
use App\Domain\Notifications\Enums\NotificationType;
use App\Infrastructure\Notifications\Channels\EmailNotificationChannel;

it('identifies itself as the email channel', function () {
    expect((new EmailNotificationChannel())->channel())->toBe(NotificationChannel::Email);
});

it('send() is a no-op stub — it does not send anything or throw yet', function () {
    $input = new CreateNotificationInputDTO(1, NotificationType::PlanPriceChanged, 'Title', 'Body');

    (new EmailNotificationChannel())->send($input);

    expect(true)->toBeTrue();
});
