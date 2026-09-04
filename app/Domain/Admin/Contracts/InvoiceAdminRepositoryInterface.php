<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\ProviderInvoiceDataDTO;
use App\Domain\Admin\Results\AdminInvoiceResult;

interface InvoiceAdminRepositoryInterface
{
    /** @return AdminInvoiceResult[] */
    public function all(string $statusFilter = 'paid'): array;

    /** @param ProviderInvoiceDataDTO[] $invoices */
    public function insertMissing(array $invoices): int;

    public function upsert(ProviderInvoiceDataDTO $invoice): void;
}
