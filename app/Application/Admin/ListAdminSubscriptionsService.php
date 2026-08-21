<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Results\AdminSubscriptionResult;

class ListAdminSubscriptionsService
{
    public function __construct(
        private readonly SubscriptionAdminRepositoryInterface $repository,
    ) {}

    /** @return AdminSubscriptionResult[] */
    public function execute(string $statusFilter = 'active'): array
    {
        return $this->repository->all($statusFilter);
    }
}
