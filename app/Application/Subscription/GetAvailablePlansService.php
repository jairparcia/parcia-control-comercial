<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Contracts\SubscriptionPlanRepositoryInterface;
use App\Domain\Subscription\Results\PlanInfo;

class GetAvailablePlansService
{
    public function __construct(private readonly SubscriptionPlanRepositoryInterface $plans) {}

    /** @return PlanInfo[] */
    public function execute(): array
    {
        return $this->plans->findAllActive();
    }
}
