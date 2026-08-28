<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\StripeCustomerDataDTO;
use App\Domain\Admin\Results\PaymentHistoryItemResult;
use App\Domain\Admin\Results\SubscriptionInvoiceItemResult;
use App\Domain\Admin\Results\UpcomingInvoiceResult;

interface CustomerProviderGatewayInterface
{
    /** @return StripeCustomerDataDTO[] */
    public function listAll(): array;

    public function findByEmail(string $email): ?StripeCustomerDataDTO;

    /** @return PaymentHistoryItemResult[] */
    public function getInvoiceHistory(string $stripeCustomerId): array;

    public function getUpcomingInvoice(string $stripeCustomerId, string $stripeSubscriptionId): ?UpcomingInvoiceResult;

    /** @return SubscriptionInvoiceItemResult[] */
    public function getSubscriptionInvoices(string $stripeSubscriptionId): array;
}
