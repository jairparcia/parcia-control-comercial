<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\TransactionProviderGatewayInterface;
use App\Domain\Admin\Results\TransactionDetailResult;

class GetTransactionDetailService
{
    public function __construct(
        private readonly TransactionProviderGatewayInterface $gateway,
    ) {}

    public function execute(string $chargeId): ?TransactionDetailResult
    {
        return $this->gateway->getTransactionDetail($chargeId);
    }
}
