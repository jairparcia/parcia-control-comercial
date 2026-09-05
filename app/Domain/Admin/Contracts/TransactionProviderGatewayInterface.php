<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Results\AdminTransactionResult;
use App\Domain\Admin\Results\TransactionDetailResult;

interface TransactionProviderGatewayInterface
{
    /** @return AdminTransactionResult[] */
    public function listRecent(int $limit = 100): array;

    public function getTransactionDetail(string $chargeId): ?TransactionDetailResult;
}
