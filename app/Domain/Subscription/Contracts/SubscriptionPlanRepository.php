<?php

namespace App\Domain\Subscription\Contracts;

use App\Domain\Subscription\Results\PlanInfo;

interface SubscriptionPlanRepository
{
    /** @return PlanInfo[] */
    public function findAllActive(): array;

    public function findStripePriceId(string $planKey): string;
}
