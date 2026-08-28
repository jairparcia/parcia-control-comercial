<?php

namespace App\Domain\Admin\Results;

readonly class UpcomingInvoiceResult
{
    public function __construct(
        public \DateTimeImmutable $periodStart,
        public \DateTimeImmutable $nextBillingDate,
        public string             $description,
        public int                $quantity,
        public int                $unitAmountCents,
        public int                $amountDueCents,
        public string             $currency,
        public int                $subtotalCents,
        public int                $taxCents,
        public int                $totalCents,
        public int                $amountPaidCents,
        public int                $amountRemainingCents,
    ) {}
}
