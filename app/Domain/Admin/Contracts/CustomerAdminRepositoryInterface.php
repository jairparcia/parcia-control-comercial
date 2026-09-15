<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\StripeCustomerDataDTO;
use App\Domain\Admin\Results\AdminCustomerResult;
use App\Models\User;

interface CustomerAdminRepositoryInterface
{
    /** @return AdminCustomerResult[] */
    public function all(string $statusFilter = 'all'): array;

    public function findById(int $id): ?User;

    public function update(int $id, ?string $description, ?string $country): void;

    public function archive(int $id): void;

    public function restore(int $id): void;

    /** @param StripeCustomerDataDTO[] $customers */
    public function insertMissing(array $customers): int;
}
