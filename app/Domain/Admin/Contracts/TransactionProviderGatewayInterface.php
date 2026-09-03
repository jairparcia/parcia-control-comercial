<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\ProviderTransactionDataDTO;
use App\Domain\Admin\Results\TransactionDetailResult;

interface TransactionProviderGatewayInterface
{
    /** @return ProviderTransactionDataDTO[] */
    public function listAll(int $limit = 100): array;

    public function getTransactionDetail(string $chargeId): ?TransactionDetailResult;
}
