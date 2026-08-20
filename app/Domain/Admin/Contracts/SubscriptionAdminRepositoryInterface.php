<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;
use App\Domain\Admin\Results\AdminSubscriptionResult;

interface SubscriptionAdminRepositoryInterface
{
    /** @return AdminSubscriptionResult[] */
    public function all(): array;

    /** @param ProviderSubscriptionDataDTO[] $subscriptions */
    public function insertMissing(array $subscriptions): int;
}
