<?php

namespace App\Events;

class PlanPriceChanged
{
    public function __construct(
        public readonly int    $planId,
        public readonly string $planName,
        public readonly int    $newUnitAmountCents,
        public readonly string $currency,
        public readonly string $interval,
    ) {}
}
