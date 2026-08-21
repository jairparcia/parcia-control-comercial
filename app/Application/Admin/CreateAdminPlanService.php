<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\PlanAdminRepositoryInterface;
use App\Domain\Admin\Contracts\PlanProviderGatewayInterface;
use App\Domain\Admin\Entities\CreateAdminPlanInputDTO;
use App\Domain\Admin\Results\AdminPlanResult;

class CreateAdminPlanService
{
    public function __construct(
        private readonly PlanAdminRepositoryInterface $plans,
        private readonly PlanProviderGatewayInterface $provider,
    ) {}

    public function execute(
        string $name,
        string $key,
        string $description,
        array  $features,
        int    $quota,
        int    $unitAmount,
        string $currency,
        string $interval,
        int    $sortOrder,
    ): AdminPlanResult {
        $input = new CreateAdminPlanInputDTO(
            name:        $name,
            key:         $key,
            description: $description,
            features:    $features,
            quota:       $quota,
            unitAmount:  $unitAmount,
            currency:    $currency,
            interval:    $interval,
            sortOrder:   $sortOrder,
        );

        $stripeProductId = null;
        $stripePriceId   = null;

        if ($input->unitAmount > 0) {
            $ids = $this->provider->createPlan(
                $input->name,
                $input->unitAmount,
                $input->currency,
                $input->interval,
            );
            $stripeProductId = $ids->productId;
            $stripePriceId   = $ids->priceId;
        }

        return $this->plans->create($input, $stripeProductId, $stripePriceId);
    }
}
