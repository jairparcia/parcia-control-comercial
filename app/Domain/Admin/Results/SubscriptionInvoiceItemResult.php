<?php

namespace App\Domain\Admin\Results;

readonly class SubscriptionInvoiceItemResult
{
    public function __construct(
        public string             $invoiceNumber,
        public int                $amountCents,
        public string             $currency,
        public string             $interval,
        public string             $status,
        public \DateTimeImmutable $createdAt,
    ) {}
}
