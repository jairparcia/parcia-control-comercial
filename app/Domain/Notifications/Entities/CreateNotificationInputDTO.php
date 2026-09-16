<?php

namespace App\Domain\Notifications\Entities;

use App\Domain\Notifications\Enums\NotificationType;

class CreateNotificationInputDTO
{
    public function __construct(
        public readonly int              $userId,
        public readonly NotificationType $type,
        public readonly string           $title,
        public readonly string           $body,
    ) {}
}
