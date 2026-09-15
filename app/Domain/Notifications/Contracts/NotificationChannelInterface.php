<?php

namespace App\Domain\Notifications\Contracts;

use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationChannel;

interface NotificationChannelInterface
{
    public function channel(): NotificationChannel;

    public function send(CreateNotificationInputDTO $input): void;
}
