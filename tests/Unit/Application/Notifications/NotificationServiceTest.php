<?php

use App\Application\Notifications\NotificationService;
use App\Domain\Notifications\Contracts\NotificationChannelInterface;
use App\Domain\Notifications\Contracts\NotificationRepositoryInterface;
use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationChannel;
use App\Domain\Notifications\Enums\NotificationType;

// ── Helpers ───────────────────────────────────────────────────────────────────

function stubNotificationChannel(NotificationChannel $channel): \Mockery\MockInterface
{
    $mock = Mockery::mock(NotificationChannelInterface::class);
    $mock->allows('channel')->andReturn($channel);

    return $mock;
}

// ── create() ──────────────────────────────────────────────────────────────────

it('create() sends the notification through every channel the type declares', function () {
    $inApp = stubNotificationChannel(NotificationChannel::InApp);
    $inApp->expects('send')->once()->with(Mockery::on(
        fn (CreateNotificationInputDTO $dto) => $dto->userId === 42
            && $dto->type === NotificationType::PlanPriceChanged
            && $dto->title === 'Title'
            && $dto->body === 'Body'
    ));

    $email = stubNotificationChannel(NotificationChannel::Email);
    $email->expects('send')->once()->with(Mockery::on(
        fn (CreateNotificationInputDTO $dto) => $dto->userId === 42
            && $dto->type === NotificationType::PlanPriceChanged
    ));

    $service = new NotificationService(
        Mockery::mock(NotificationRepositoryInterface::class),
        [$inApp, $email],
    );

    $service->create(42, NotificationType::PlanPriceChanged, 'Title', 'Body');
});

it('create() never touches the repository directly — persistence is the in-app channel\'s job', function () {
    // No expectations set on the repository mock: any call to it fails the test.
    $repo = Mockery::mock(NotificationRepositoryInterface::class);

    $inApp = stubNotificationChannel(NotificationChannel::InApp);
    $inApp->allows('send');
    $email = stubNotificationChannel(NotificationChannel::Email);
    $email->allows('send');

    $service = new NotificationService($repo, [$inApp, $email]);

    $service->create(1, NotificationType::PlanPriceChanged, 'T', 'B');

    expect(true)->toBeTrue();
});

// ── listForUser() ─────────────────────────────────────────────────────────────

it('listForUser() delegates to the repository with the given limit', function () {
    $results = ['first', 'second'];

    $repo = Mockery::mock(NotificationRepositoryInterface::class);
    $repo->expects('listForUser')->with(7, 5)->once()->andReturn($results);

    $service = new NotificationService($repo, []);

    expect($service->listForUser(7, 5))->toBe($results);
});

it('listForUser() defaults the limit to 20', function () {
    $repo = Mockery::mock(NotificationRepositoryInterface::class);
    $repo->expects('listForUser')->with(7, 20)->once()->andReturn([]);

    (new NotificationService($repo, []))->listForUser(7);
});

// ── unreadCount() ─────────────────────────────────────────────────────────────

it('unreadCount() delegates to the repository', function () {
    $repo = Mockery::mock(NotificationRepositoryInterface::class);
    $repo->expects('unreadCount')->with(7)->once()->andReturn(3);

    expect((new NotificationService($repo, []))->unreadCount(7))->toBe(3);
});

// ── markAsRead() ──────────────────────────────────────────────────────────────

it('markAsRead() delegates to the repository', function () {
    $repo = Mockery::mock(NotificationRepositoryInterface::class);
    $repo->expects('markAsRead')->with(99, 7)->once();

    (new NotificationService($repo, []))->markAsRead(99, 7);
});

// ── markAllAsRead() ───────────────────────────────────────────────────────────

it('markAllAsRead() delegates to the repository', function () {
    $repo = Mockery::mock(NotificationRepositoryInterface::class);
    $repo->expects('markAllAsRead')->with(7)->once();

    (new NotificationService($repo, []))->markAllAsRead(7);
});
