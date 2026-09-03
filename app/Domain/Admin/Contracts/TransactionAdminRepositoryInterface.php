<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\ProviderTransactionDataDTO;
use App\Domain\Admin\Results\AdminTransactionResult;

interface TransactionAdminRepositoryInterface
{
    /** @return AdminTransactionResult[] */
    public function all(string $statusFilter = 'all'): array;

    /**
     * @param  ProviderTransactionDataDTO[]  $transactions
     * @return int Number of new rows inserted
     */
    public function insertMissing(array $transactions): int;

    public function upsert(ProviderTransactionDataDTO $transaction): void;
}
