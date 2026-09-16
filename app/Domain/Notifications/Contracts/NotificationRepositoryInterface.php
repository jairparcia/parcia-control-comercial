<?php

namespace App\Domain\Notifications\Contracts;

use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Results\NotificationResult;

interface NotificationRepositoryInterface
{
    public function create(CreateNotificationInputDTO $input): void;

    /** @return NotificationResult[] */
    public function listForUser(int $userId, int $limit = 20): array;

    public function unreadCount(int $userId): int;

    public function markAsRead(int $notificationId, int $userId): void;

    public function markAllAsRead(int $userId): void;
}
