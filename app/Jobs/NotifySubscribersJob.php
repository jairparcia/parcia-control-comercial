<?php

namespace App\Jobs;

use App\Application\Notifications\NotificationService;
use App\Domain\Notifications\Enums\NotificationType;
use App\Domain\Subscription\Contracts\SubscriptionRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifySubscribersJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int    $planId,
        public readonly string $planName,
        public readonly int    $newUnitAmountCents,
        public readonly string $currency,
        public readonly string $interval,
    ) {}

    public function handle(
        NotificationService $notificationService,
        SubscriptionRepositoryInterface $subscriptions,
    ): void {
        $userIds = $subscriptions->findActiveSubscriberUserIdsByPlan($this->planId);

        if (empty($userIds)) {
            return;
        }

        $symbol   = strtoupper($this->currency) === 'USD' ? 'US$' : 'MX$';
        $amount   = $symbol . number_format($this->newUnitAmountCents / 100, 2);
        $interval = $this->interval === 'month'
            ? __('common.interval_monthly')
            : __('common.interval_annual');

        $title = __('notifications.plan_price_changed_title', ['plan' => $this->planName]);
        $body  = __('notifications.plan_price_changed_body', [
            'plan'     => $this->planName,
            'amount'   => $amount,
            'interval' => $interval,
        ]);

        foreach ($userIds as $userId) {
            $notificationService->create(
                userId: $userId,
                type:   NotificationType::PlanPriceChanged,
                title:  $title,
                body:   $body,
            );
        }
    }
}
