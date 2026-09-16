<?php

use App\Domain\Notifications\Enums\NotificationType;
use App\Domain\Notifications\Results\NotificationResult;
use App\Http\Presenters\NotificationPresenter;
use App\Http\Presenters\NotificationViewModel;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeNotificationResult(array $overrides = []): NotificationResult
{
    return new NotificationResult(
        id:        $overrides['id']        ?? 1,
        type:      $overrides['type']      ?? NotificationType::PlanPriceChanged,
        title:     $overrides['title']     ?? 'Price update: Pro',
        body:      $overrides['body']      ?? 'The Pro plan has a new price.',
        isRead:    $overrides['isRead']    ?? false,
        createdAt: $overrides['createdAt'] ?? new \DateTimeImmutable('2025-09-20'),
    );
}

function presentNotification(array $overrides = []): NotificationViewModel
{
    return (new NotificationPresenter())->presentAll([makeNotificationResult($overrides)])[0];
}

// ── ViewModel type ────────────────────────────────────────────────────────────

it('returns NotificationViewModel instances', function () {
    $result = (new NotificationPresenter())->presentAll([makeNotificationResult()]);

    expect($result[0])->toBeInstanceOf(NotificationViewModel::class);
});

// ── Field mapping ─────────────────────────────────────────────────────────────

it('carries id, title, body, and isRead through unchanged', function () {
    $vm = presentNotification(['id' => 42, 'title' => 'Custom title', 'body' => 'Custom body', 'isRead' => true]);

    expect($vm->id)->toBe(42)
        ->and($vm->title)->toBe('Custom title')
        ->and($vm->body)->toBe('Custom body')
        ->and($vm->isRead)->toBeTrue();
});

// ── Date formatting ───────────────────────────────────────────────────────────

it('formats createdAt using the current app locale', function () {
    app()->setLocale('en');

    $vm = presentNotification(['createdAt' => new \DateTimeImmutable('2025-09-20')]);

    expect($vm->createdAt)->toBe('20 Sep 2025');
});

it('formats createdAt in Spanish when the app locale is es', function () {
    app()->setLocale('es');

    $vm = presentNotification(['createdAt' => new \DateTimeImmutable('2025-09-20')]);

    expect($vm->createdAt)->toBe('20 sep. 2025');
});

// ── presentAll() ──────────────────────────────────────────────────────────────

it('presentAll() maps every notification in the array', function () {
    $results = [
        makeNotificationResult(['id' => 1]),
        makeNotificationResult(['id' => 2]),
    ];

    $viewModels = (new NotificationPresenter())->presentAll($results);

    expect($viewModels)->toHaveCount(2)
        ->and($viewModels[0]->id)->toBe(1)
        ->and($viewModels[1]->id)->toBe(2);
});
