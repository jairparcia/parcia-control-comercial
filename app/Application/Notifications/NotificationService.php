<?php

namespace App\Application\Notifications;

use App\Domain\Notifications\Contracts\NotificationChannelInterface;
use App\Domain\Notifications\Contracts\NotificationRepositoryInterface;
use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationType;
use App\Domain\Notifications\Results\NotificationResult;

class NotificationService
{
    /** @var array<string, NotificationChannelInterface> */
    private array $channels;

    /** @param NotificationChannelInterface[] $channels */
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        array $channels,
    ) {
        foreach ($channels as $channel) {
            $this->channels[$channel->channel()->value] = $channel;
        }
    }

    public function create(int $userId, NotificationType $type, string $title, string $body): void
    {
        $input = new CreateNotificationInputDTO($userId, $type, $title, $body);

        foreach ($type->channels() as $channel) {
            $this->channels[$channel->value]->send($input);
        }
    }

    /** @return NotificationResult[] */
    public function listForUser(int $userId, int $limit = 20): array
    {
        return $this->notifications->listForUser($userId, $limit);
    }

    public function unreadCount(int $userId): int
    {
        return $this->notifications->unreadCount($userId);
    }

    public function markAsRead(int $notificationId, int $userId): void
    {
        $this->notifications->markAsRead($notificationId, $userId);
    }

    public function markAllAsRead(int $userId): void
    {
        $this->notifications->markAllAsRead($userId);
    }
}
