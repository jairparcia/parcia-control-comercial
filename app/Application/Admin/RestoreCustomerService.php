<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;

class RestoreCustomerService
{
    public function __construct(
        private readonly CustomerAdminRepositoryInterface $customers,
    ) {}

    public function execute(int $userId): void
    {
        $this->customers->restore($userId);
    }
}
