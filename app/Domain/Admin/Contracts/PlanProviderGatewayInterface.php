<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Results\ProviderPlanIds;

interface PlanProviderGatewayInterface
{
    public function createPlan(string $name, int $unitAmount, string $currency, string $interval): ProviderPlanIds;

    public function updatePlanName(string $productId, string $name): void;

    public function replacePlanPrice(string $productId, string $oldPriceId, int $unitAmount, string $currency, string $interval): string;

    public function deactivatePlan(string $priceId): void;

    public function addPriceToProduct(string $productId, int $unitAmount, string $currency, string $interval): string;

    /** @return array[] */
    public function listProductPrices(string $productId): array;

    public function retrievePrice(string $priceId): array;

    public function archivePrice(string $priceId): void;

    public function setDefaultPrice(string $productId, string $priceId): void;
}
