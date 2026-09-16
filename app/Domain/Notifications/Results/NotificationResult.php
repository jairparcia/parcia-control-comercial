<?php

namespace App\Domain\Notifications\Results;

use App\Domain\Notifications\Enums\NotificationType;

class NotificationResult
{
    public function __construct(
        public readonly int              $id,
        public readonly NotificationType $type,
        public readonly string           $title,
        public readonly string           $body,
        public readonly bool             $isRead,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
