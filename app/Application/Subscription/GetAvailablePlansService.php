<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Contracts\SubscriptionPlanRepository;
use App\Domain\Subscription\Results\PlanInfo;

class GetAvailablePlansService
{
    public function __construct(private readonly SubscriptionPlanRepository $plans) {}

    /** @return PlanInfo[] */
    public function execute(): array
    {
        return $this->plans->findAllActive();
    }
}
