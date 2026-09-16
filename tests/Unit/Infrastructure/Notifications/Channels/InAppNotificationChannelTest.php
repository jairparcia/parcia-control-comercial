<?php

use App\Domain\Notifications\Contracts\NotificationRepositoryInterface;
use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationChannel;
use App\Domain\Notifications\Enums\NotificationType;
use App\Infrastructure\Notifications\Channels\InAppNotificationChannel;

it('identifies itself as the in_app channel', function () {
    $channel = new InAppNotificationChannel(Mockery::mock(NotificationRepositoryInterface::class));

    expect($channel->channel())->toBe(NotificationChannel::InApp);
});

it('send() persists the notification via the repository', function () {
    $input = new CreateNotificationInputDTO(1, NotificationType::PlanPriceChanged, 'Title', 'Body');

    $repo = Mockery::mock(NotificationRepositoryInterface::class);
    $repo->expects('create')->once()->with($input);

    (new InAppNotificationChannel($repo))->send($input);
});
