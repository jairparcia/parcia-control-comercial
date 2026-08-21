<?php

namespace App\Domain\Admin\Results;

readonly class AdminPlanResult
{
    public function __construct(
        public int     $id,
        public string  $key,
        public string  $name,
        public string  $description,
        public array   $features,
        public int     $quota,
        public int     $unitAmount,
        public string  $currency,
        public string  $interval,
        public int     $sortOrder,
        public bool    $active,
        public ?string $stripePriceId,
        public ?string $stripeProductId,
    ) {}
}
