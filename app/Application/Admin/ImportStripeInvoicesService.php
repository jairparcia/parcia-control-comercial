<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\InvoiceAdminRepositoryInterface;
use App\Domain\Admin\Contracts\InvoiceProviderGatewayInterface;

class ImportStripeInvoicesService
{
    public function __construct(
        private readonly InvoiceProviderGatewayInterface $gateway,
        private readonly InvoiceAdminRepositoryInterface $repository,
    ) {}

    public function execute(): int
    {
        $invoices = $this->gateway->listAll();

        if (empty($invoices)) {
            return 0;
        }

        return $this->repository->insertMissing($invoices);
    }
}
