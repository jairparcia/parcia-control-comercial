<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Results\ProviderPlanIds;

interface PlanProviderGateway
{
    public function createPlan(string $name, int $unitAmount, string $currency, string $interval): ProviderPlanIds;

    public function updatePlanName(string $productId, string $name): void;

    public function replacePlanPrice(string $productId, string $oldPriceId, int $unitAmount, string $currency, string $interval): string;

    public function deactivatePlan(string $priceId): void;
}
