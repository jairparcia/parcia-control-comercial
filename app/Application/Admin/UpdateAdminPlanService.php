<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\PlanAdminRepository;
use App\Domain\Admin\Contracts\PlanProviderGateway;
use App\Domain\Admin\Entities\UpdateAdminPlanInput;
use App\Domain\Admin\Results\AdminPlanResult;

class UpdateAdminPlanService
{
    public function __construct(
        private readonly PlanAdminRepository $plans,
        private readonly PlanProviderGateway $provider,
    ) {}

    public function execute(
        int    $planId,
        string $name,
        string $description,
        array  $features,
        int    $quota,
        int    $unitAmount,
        string $currency,
        string $interval,
        int    $sortOrder,
    ): AdminPlanResult {
        $input   = new UpdateAdminPlanInput(
            name:        $name,
            description: $description,
            features:    $features,
            quota:       $quota,
            unitAmount:  $unitAmount,
            currency:    $currency,
            interval:    $interval,
            sortOrder:   $sortOrder,
        );

        $current    = $this->plans->findById($planId);
        $newPriceId = null;

        if ($current->stripeProductId !== null) {
            if ($current->name !== $input->name) {
                $this->provider->updatePlanName($current->stripeProductId, $input->name);
            }

            $priceChanged = $current->unitAmount !== $input->unitAmount
                || $current->currency !== $input->currency
                || $current->interval !== $input->interval;

            if ($priceChanged && $current->stripePriceId !== null) {
                $newPriceId = $this->provider->replacePlanPrice(
                    $current->stripeProductId,
                    $current->stripePriceId,
                    $input->unitAmount,
                    $input->currency,
                    $input->interval,
                );

                // Preserve the old price ID so existing subscribers can still be resolved.
                $this->plans->appendLegacyPriceId($planId, $current->stripePriceId);
            }
        }

        return $this->plans->update($planId, $input, $newPriceId);
    }
}
