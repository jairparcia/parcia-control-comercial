<?php

namespace App\Domain\Admin\Results;

readonly class TransactionFeeDetailResult
{
    public function __construct(
        public string $type,
        public string $description,
        public int    $amountCents,
        public string $currency,
    ) {}
}
