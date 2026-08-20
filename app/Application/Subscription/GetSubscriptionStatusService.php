<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Contracts\SubscriptionRepositoryInterface;
use App\Domain\Subscription\Results\SubscriptionStatusResult;

class GetSubscriptionStatusService
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptions,
    ) {}

    public function execute(int $userId): SubscriptionStatusResult
    {
        return $this->subscriptions->getStatus($userId);
    }
}
