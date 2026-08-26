<?php

namespace App\Domain\Admin\Results;

readonly class PaymentHistoryItemResult
{
    public function __construct(
        public int                $amountCents,
        public string             $currency,
        public \DateTimeImmutable $paidAt,
        public ?string            $planDescription,
    ) {}
}
