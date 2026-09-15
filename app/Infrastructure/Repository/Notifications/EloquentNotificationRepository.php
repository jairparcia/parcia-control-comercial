<?php

namespace App\Infrastructure\Repository\Notifications;

use App\Domain\Notifications\Contracts\NotificationRepositoryInterface;
use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationType;
use App\Domain\Notifications\Results\NotificationResult;
use App\Models\Notification;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function create(CreateNotificationInputDTO $input): void
    {
        Notification::create([
            'user_id' => $input->userId,
            'type'    => $input->type->value,
            'title'   => $input->title,
            'body'    => $input->body,
        ]);
    }

    public function listForUser(int $userId, int $limit = 20): array
    {
        return Notification::where('user_id', $userId)
            ->latest()
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Notification $n) => new NotificationResult(
                id:        $n->id,
                type:      NotificationType::from($n->type),
                title:     $n->title,
                body:      $n->body,
                isRead:    $n->read_at !== null,
                createdAt: new \DateTimeImmutable($n->created_at->toDateTimeString()),
            ))
            ->all();
    }

    public function unreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(int $notificationId, int $userId): void
    {
        Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
