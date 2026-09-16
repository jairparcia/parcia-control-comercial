<?php

use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationType;
use App\Infrastructure\Repository\Notifications\EloquentNotificationRepository;
use App\Models\Notification;
use App\Models\User;

// ── Helper ────────────────────────────────────────────────────────────────────

function notificationInputFor(User $user, array $overrides = []): CreateNotificationInputDTO
{
    return new CreateNotificationInputDTO(
        $overrides['userId'] ?? $user->id,
        $overrides['type'] ?? NotificationType::PlanPriceChanged,
        $overrides['title'] ?? 'Title',
        $overrides['body'] ?? 'Body',
    );
}

// ── create() ──────────────────────────────────────────────────────────────────

it('create() persists an unread notification for the user', function () {
    $user = User::factory()->create();

    (new EloquentNotificationRepository())->create(notificationInputFor($user));

    expect(Notification::where('user_id', $user->id)->count())->toBe(1);

    $row = Notification::where('user_id', $user->id)->first();
    expect($row->type)->toBe(NotificationType::PlanPriceChanged->value)
        ->and($row->title)->toBe('Title')
        ->and($row->body)->toBe('Body')
        ->and($row->read_at)->toBeNull();
});

// ── listForUser() ─────────────────────────────────────────────────────────────

it('listForUser() returns only that user\'s notifications, most recent first', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    $repo = new EloquentNotificationRepository();
    $repo->create(notificationInputFor($user, ['title' => 'First']));
    $repo->create(notificationInputFor($user, ['title' => 'Second']));
    $repo->create(notificationInputFor($other, ['title' => 'Not mine']));

    $results = $repo->listForUser($user->id);

    expect($results)->toHaveCount(2)
        ->and($results[0]->title)->toBe('Second')
        ->and($results[1]->title)->toBe('First');
});

it('listForUser() respects the limit', function () {
    $user = User::factory()->create();
    $repo = new EloquentNotificationRepository();

    foreach (range(1, 5) as $i) {
        $repo->create(notificationInputFor($user, ['title' => "N{$i}"]));
    }

    expect($repo->listForUser($user->id, 2))->toHaveCount(2);
});

// ── unreadCount() ─────────────────────────────────────────────────────────────

it('unreadCount() only counts notifications without read_at', function () {
    $user = User::factory()->create();
    $repo = new EloquentNotificationRepository();

    $repo->create(notificationInputFor($user));
    $repo->create(notificationInputFor($user));

    expect($repo->unreadCount($user->id))->toBe(2);

    Notification::where('user_id', $user->id)->first()->update(['read_at' => now()]);

    expect($repo->unreadCount($user->id))->toBe(1);
});

// ── markAsRead() ──────────────────────────────────────────────────────────────

it('markAsRead() marks the given notification as read', function () {
    $user = User::factory()->create();
    $repo = new EloquentNotificationRepository();

    $repo->create(notificationInputFor($user));
    $notification = Notification::where('user_id', $user->id)->first();

    $repo->markAsRead($notification->id, $user->id);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('markAsRead() does not mark a notification belonging to another user', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $repo  = new EloquentNotificationRepository();

    $repo->create(notificationInputFor($other));
    $theirs = Notification::where('user_id', $other->id)->first();

    $repo->markAsRead($theirs->id, $user->id);

    expect($theirs->fresh()->read_at)->toBeNull();
});

// ── markAllAsRead() ───────────────────────────────────────────────────────────

it('markAllAsRead() marks every unread notification for the user only', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();
    $repo  = new EloquentNotificationRepository();

    $repo->create(notificationInputFor($user));
    $repo->create(notificationInputFor($user));
    $repo->create(notificationInputFor($other));

    $repo->markAllAsRead($user->id);

    expect(Notification::where('user_id', $user->id)->whereNull('read_at')->count())->toBe(0)
        ->and(Notification::where('user_id', $other->id)->whereNull('read_at')->count())->toBe(1);
});
