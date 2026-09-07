<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\CreateAdminPlanInputDTO;
use App\Domain\Admin\Entities\UpdateAdminPlanInputDTO;
use App\Domain\Admin\Results\AdminPlanResult;

interface PlanAdminRepositoryInterface
{
    /** @return AdminPlanResult[] */
    public function all(): array;

    public function findById(int $id): AdminPlanResult;

    public function create(
        CreateAdminPlanInputDTO $input,
        ?string $stripeProductId,
        ?string $stripePriceId,
    ): AdminPlanResult;

    public function update(int $id, UpdateAdminPlanInputDTO $input): AdminPlanResult;

    public function toggle(int $id): bool;

    public function appendLegacyPriceId(int $id, string $oldPriceId): void;

    public function countActiveSubscriptionsForPrice(string $stripePriceId): int;

    public function updateDefaultPrice(
        int $id,
        string $stripePriceId,
        int $unitAmountCents,
        string $currency,
        string $interval,
    ): void;

    public function setStripeIds(
        int $id,
        string $productId,
        string $priceId,
        int $unitAmountCents,
        string $currency,
        string $interval,
    ): void;
}
