<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\TransactionProviderGatewayInterface;
use App\Domain\Admin\Results\AdminTransactionResult;

class ListAdminTransactionsService
{
    public function __construct(
        private readonly TransactionProviderGatewayInterface $gateway,
    ) {}

    /** @return AdminTransactionResult[] */
    public function execute(string $statusFilter = 'all'): array
    {
        $transactions = $this->gateway->listRecent();

        if ($statusFilter === 'all') {
            return $transactions;
        }

        return array_values(
            array_filter($transactions, fn (AdminTransactionResult $t) => $t->status === $statusFilter),
        );
    }
}
