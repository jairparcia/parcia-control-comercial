<?php

namespace App\Infrastructure\Notifications\Channels;

use App\Domain\Notifications\Contracts\NotificationChannelInterface;
use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationChannel;

class EmailNotificationChannel implements NotificationChannelInterface
{
    public function channel(): NotificationChannel
    {
        return NotificationChannel::Email;
    }

    public function send(CreateNotificationInputDTO $input): void
    {
        // Email delivery is not implemented yet. NotificationType::channels()
        // already routes here for the types that should email subscribers —
        // wire a Mailable in this method when that work starts.
    }
}
