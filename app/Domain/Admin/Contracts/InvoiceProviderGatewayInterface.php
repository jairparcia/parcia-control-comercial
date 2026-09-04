<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\ProviderInvoiceDataDTO;

interface InvoiceProviderGatewayInterface
{
    /** @return ProviderInvoiceDataDTO[] */
    public function listAll(int $limit = 100): array;
}
