<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\CreateAdminPlanInput;
use App\Domain\Admin\Entities\UpdateAdminPlanInput;
use App\Domain\Admin\Results\AdminPlanResult;

interface PlanAdminRepository
{
    /** @return AdminPlanResult[] */
    public function all(): array;

    public function findById(int $id): AdminPlanResult;

    public function create(
        CreateAdminPlanInput $input,
        ?string $stripeProductId,
        ?string $stripePriceId,
    ): AdminPlanResult;

    public function update(
        int $id,
        UpdateAdminPlanInput $input,
        ?string $newStripePriceId = null,
    ): AdminPlanResult;

    public function toggle(int $id): bool;

    public function appendLegacyPriceId(int $id, string $oldPriceId): void;
}
