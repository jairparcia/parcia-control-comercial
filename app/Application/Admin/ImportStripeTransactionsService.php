<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\TransactionAdminRepositoryInterface;
use App\Domain\Admin\Contracts\TransactionProviderGatewayInterface;

class ImportStripeTransactionsService
{
    public function __construct(
        private readonly TransactionProviderGatewayInterface  $gateway,
        private readonly TransactionAdminRepositoryInterface  $repository,
    ) {}

    public function execute(): int
    {
        $transactions = $this->gateway->listAll();

        return $this->repository->insertMissing($transactions);
    }
}
