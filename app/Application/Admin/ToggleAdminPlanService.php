<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\PlanAdminRepository;
use App\Domain\Admin\Contracts\PlanProviderGateway;

class ToggleAdminPlanService
{
    public function __construct(
        private readonly PlanAdminRepository $plans,
        private readonly PlanProviderGateway $provider,
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
