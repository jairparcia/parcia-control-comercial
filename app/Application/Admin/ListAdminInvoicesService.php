<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\InvoiceAdminRepositoryInterface;

class ListAdminInvoicesService
{
    public function __construct(
        private readonly InvoiceAdminRepositoryInterface $repository,
    ) {}

    public function execute(string $statusFilter = 'paid'): array
    {
        return $this->repository->all($statusFilter);
    }
}
