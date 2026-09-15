<?php

use App\Application\Notifications\NotificationService;
use App\Domain\Notifications\Enums\NotificationType;
use App\Domain\Subscription\Contracts\SubscriptionRepositoryInterface;
use App\Jobs\NotifySubscribersJob;

it('does nothing when the plan has no active subscribers', function () {
    $subscriptions = Mockery::mock(SubscriptionRepositoryInterface::class);
    $subscriptions->expects('findActiveSubscriberUserIdsByPlan')->with(5)->once()->andReturn([]);

    $notificationService = Mockery::mock(NotificationService::class);
    $notificationService->expects('create')->never();

    (new NotifySubscribersJob(5, 'Pro', 80000, 'USD', 'year'))
        ->handle($notificationService, $subscriptions);
});

it('notifies every active subscriber with the formatted title and body', function () {
    $subscriptions = Mockery::mock(SubscriptionRepositoryInterface::class);
    $subscriptions->allows('findActiveSubscriberUserIdsByPlan')->with(5)->andReturn([1, 2, 3]);

    $title = __('notifications.plan_price_changed_title', ['plan' => 'Pro']);
    $body  = __('notifications.plan_price_changed_body', [
        'plan'     => 'Pro',
        'amount'   => 'US$800.00',
        'interval' => __('common.interval_annual'),
    ]);

    $notificationService = Mockery::mock(NotificationService::class);
    foreach ([1, 2, 3] as $userId) {
        $notificationService->expects('create')
            ->once()
            ->with($userId, NotificationType::PlanPriceChanged, $title, $body);
    }

    (new NotifySubscribersJob(5, 'Pro', 80000, 'USD', 'year'))
        ->handle($notificationService, $subscriptions);
});

it('formats non-USD prices with the MX$ symbol and a monthly interval label', function () {
    $subscriptions = Mockery::mock(SubscriptionRepositoryInterface::class);
    $subscriptions->allows('findActiveSubscriberUserIdsByPlan')->with(9)->andReturn([1]);

    $body = __('notifications.plan_price_changed_body', [
        'plan'     => 'Starter',
        'amount'   => 'MX$300.00',
        'interval' => __('common.interval_monthly'),
    ]);

    $notificationService = Mockery::mock(NotificationService::class);
    $notificationService->expects('create')
        ->once()
        ->with(1, NotificationType::PlanPriceChanged, Mockery::type('string'), $body);

    (new NotifySubscribersJob(9, 'Starter', 30000, 'MXN', 'month'))
        ->handle($notificationService, $subscriptions);
});
