<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;

class UpdateCustomerService
{
    public function __construct(
        private readonly CustomerAdminRepositoryInterface $customers,
    ) {}

    public function execute(int $userId, ?string $description, ?string $country): void
    {
        $this->customers->update($userId, $description, $country);
    }
}
