<?php

namespace App\Domain\Admin\Results;

readonly class UpcomingInvoiceResult
{
    public function __construct(
        public \DateTimeImmutable $nextBillingDate,
        public int                $amountDueCents,
        public string             $currency,
    ) {}
}
