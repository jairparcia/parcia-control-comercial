<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Contracts\SubscriptionRepository;
use App\Domain\Subscription\Results\SubscriptionStatusResult;

class GetSubscriptionStatusService
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptions,
    ) {}

    public function execute(int $userId): SubscriptionStatusResult
    {
        return $this->subscriptions->getStatus($userId);
    }
}
