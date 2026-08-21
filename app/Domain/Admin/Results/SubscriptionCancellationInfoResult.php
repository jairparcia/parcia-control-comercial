<?php

namespace App\Domain\Admin\Results;

readonly class SubscriptionCancellationInfoResult
{
    public function __construct(
        public string             $stripeSubscriptionId,
        public \DateTimeImmutable $periodEnd,
        public int                $lastPaymentAmount,   // cents
        public string             $lastPaymentCurrency,
        public int                $proratedAmount,      // cents, unused portion
        public int                $proratedDays,        // unused days remaining
    ) {}
}
