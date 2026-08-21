<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\PlanAdminRepositoryInterface;
use App\Domain\Admin\Contracts\PlanProviderGatewayInterface;

class ToggleAdminPlanService
{
    public function __construct(
        private readonly PlanAdminRepositoryInterface $plans,
        private readonly PlanProviderGatewayInterface $provider,
    ) {}

    public function execute(int $planId): bool
    {
        $plan      = $this->plans->findById($planId);
        $newActive = $this->plans->toggle($planId);

        if (! $newActive && $plan->stripePriceId !== null) {
            $this->provider->deactivatePlan($plan->stripePriceId);
        }

        return $newActive;
    }
}
