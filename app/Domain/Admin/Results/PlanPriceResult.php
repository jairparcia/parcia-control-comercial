<?php

namespace App\Domain\Admin\Results;

readonly class PlanPriceResult
{
    public function __construct(
        public string             $stripeId,
        public int                $unitAmountCents,
        public string             $currency,
        public ?string            $interval,
        public int                $intervalCount,
        public bool               $isActive,
        public bool               $isDefault,
        public int                $activeSubscriptionsCount,
        public \DateTimeImmutable $createdAt,
    ) {}
}
