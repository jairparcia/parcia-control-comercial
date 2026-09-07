<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\PlanAdminRepositoryInterface;
use App\Domain\Admin\Contracts\PlanProviderGatewayInterface;
use App\Domain\Admin\Entities\CreateAdminPlanInputDTO;
use App\Domain\Admin\Entities\UpdateAdminPlanInputDTO;
use App\Domain\Admin\Results\AdminPlanResult;
use App\Domain\Admin\Results\PlanPriceResult;

class AdminPlanService
{
    public function __construct(
        private readonly PlanAdminRepositoryInterface $plans,
        private readonly PlanProviderGatewayInterface $provider,
    ) {}

    /** @return AdminPlanResult[] */
    public function list(): array
    {
        return $this->plans->all();
    }

    public function create(
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
            $ids             = $this->provider->createPlan($input->name, $input->unitAmount, $input->currency, $input->interval);
            $stripeProductId = $ids->productId;
            $stripePriceId   = $ids->priceId;
        }

        return $this->plans->create($input, $stripeProductId, $stripePriceId);
    }

    public function update(
        int    $planId,
        string $name,
        string $description,
        array  $features,
        int    $quota,
        int    $sortOrder,
    ): AdminPlanResult {
        $input   = new UpdateAdminPlanInputDTO(
            name:        $name,
            description: $description,
            features:    $features,
            quota:       $quota,
            sortOrder:   $sortOrder,
        );

        $current = $this->plans->findById($planId);

        if ($current->stripeProductId !== null && $current->name !== $input->name) {
            $this->provider->updatePlanName($current->stripeProductId, $input->name);
        }

        return $this->plans->update($planId, $input);
    }

    public function toggle(int $planId): bool
    {
        $plan      = $this->plans->findById($planId);
        $newActive = $this->plans->toggle($planId);

        if (! $newActive && $plan->stripePriceId !== null) {
            $this->provider->deactivatePlan($plan->stripePriceId);
        }

        return $newActive;
    }

    // ── Prices ────────────────────────────────────────────────────────────────

    /** @return PlanPriceResult[] */
    public function listPrices(int $planId): array
    {
        $plan = $this->plans->findById($planId);

        if (! $plan->stripeProductId) {
            return [];
        }

        $rawPrices = $this->provider->listProductPrices($plan->stripeProductId);

        return array_map(function (array $price) use ($plan) {
            return new PlanPriceResult(
                stripeId:                 $price['id'],
                unitAmountCents:          (int) ($price['unit_amount'] ?? 0),
                currency:                 strtoupper($price['currency']),
                interval:                 $price['recurring']['interval'] ?? null,
                intervalCount:            (int) ($price['recurring']['interval_count'] ?? 1),
                isActive:                 (bool) $price['active'],
                isDefault:                $price['id'] === $plan->stripePriceId,
                activeSubscriptionsCount: $this->plans->countActiveSubscriptionsForPrice($price['id']),
                createdAt:                new \DateTimeImmutable('@' . $price['created']),
            );
        }, $rawPrices);
    }

    public function addPrice(int $planId, int $unitAmount, string $currency, string $interval): void
    {
        $plan = $this->plans->findById($planId);

        if ($plan->stripeProductId === null) {
            $ids = $this->provider->createPlan($plan->name, $unitAmount, $currency, $interval);
            $this->plans->setStripeIds($planId, $ids->productId, $ids->priceId, $unitAmount, $currency, $interval);
        } else {
            $priceId = $this->provider->addPriceToProduct($plan->stripeProductId, $unitAmount, $currency, $interval);
            $this->plans->updateDefaultPrice($planId, $priceId, $unitAmount, $currency, $interval);
        }
    }

    public function setDefaultPrice(int $planId, string $stripePriceId): void
    {
        $plan  = $this->plans->findById($planId);
        $price = $this->provider->retrievePrice($stripePriceId);

        $this->provider->setDefaultPrice($plan->stripeProductId, $stripePriceId);

        $this->plans->updateDefaultPrice(
            id:              $planId,
            stripePriceId:   $stripePriceId,
            unitAmountCents: (int) ($price['unit_amount'] ?? 0),
            currency:        strtoupper($price['currency']),
            interval:        $price['recurring']['interval'],
        );
    }

    public function archivePrice(int $planId, string $stripePriceId): void
    {
        $plan = $this->plans->findById($planId);

        if ($plan->stripePriceId === $stripePriceId) {
            throw new \RuntimeException('The default price cannot be archived. Set a different price as default first.');
        }

        $this->provider->archivePrice($stripePriceId);
    }
}
