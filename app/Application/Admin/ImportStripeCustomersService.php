<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;
use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;

class ImportStripeCustomersService
{
    public function __construct(
        private readonly CustomerProviderGatewayInterface $gateway,
        private readonly CustomerAdminRepositoryInterface $repository,
    ) {}

    public function execute(): int
    {
        $customers = $this->gateway->listAll();

        if (empty($customers)) {
            return 0;
        }

        return $this->repository->insertMissing($customers);
    }
}
