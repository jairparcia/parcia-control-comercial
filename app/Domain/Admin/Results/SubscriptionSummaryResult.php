<?php

namespace App\Domain\Admin\Results;

readonly class SubscriptionSummaryResult
{
    public function __construct(
        public string              $stripeSubscriptionId,
        public string              $planName,
        public string              $interval,
        public int                 $unitAmountCents,
        public string              $currency,
        public ?\DateTimeImmutable $nextBillingDate,
        public int                 $nextBillingAmountCents,
    ) {}
}
