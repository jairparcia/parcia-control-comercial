<?php

namespace App\Domain\Subscription\Contracts;

use App\Domain\Subscription\Results\SubscriptionStatusResult;

interface SubscriptionRepository
{
    public function getStatus(int $userId): SubscriptionStatusResult;

    public function isActive(int $userId): bool;
}
