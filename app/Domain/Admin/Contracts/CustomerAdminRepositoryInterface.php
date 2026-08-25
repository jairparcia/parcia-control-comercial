<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\StripeCustomerDataDTO;
use App\Domain\Admin\Results\AdminCustomerResult;

interface CustomerAdminRepositoryInterface
{
    /** @return AdminCustomerResult[] */
    public function all(): array;

    /** @param StripeCustomerDataDTO[] $customers */
    public function insertMissing(array $customers): int;
}
