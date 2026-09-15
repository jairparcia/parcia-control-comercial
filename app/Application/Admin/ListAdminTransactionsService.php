<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\TransactionAdminRepositoryInterface;
use App\Domain\Admin\Results\AdminTransactionResult;

class ListAdminTransactionsService
{
    public function __construct(
        private readonly TransactionAdminRepositoryInterface $repository,
    ) {}

    /** @return AdminTransactionResult[] */
    public function execute(string $statusFilter = 'all'): array
    {
        return $this->repository->all($statusFilter);
    }
}
