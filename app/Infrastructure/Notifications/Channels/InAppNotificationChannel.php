<?php

namespace App\Infrastructure\Notifications\Channels;

use App\Domain\Notifications\Contracts\NotificationChannelInterface;
use App\Domain\Notifications\Contracts\NotificationRepositoryInterface;
use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationChannel;

class InAppNotificationChannel implements NotificationChannelInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
    ) {}

    public function channel(): NotificationChannel
    {
        return NotificationChannel::InApp;
    }

    public function send(CreateNotificationInputDTO $input): void
    {
        $this->notifications->create($input);
    }
}
