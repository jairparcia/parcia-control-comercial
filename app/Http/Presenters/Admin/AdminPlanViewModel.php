<?php

namespace App\Http\Presenters\Admin;

readonly class AdminPlanViewModel
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
        public bool    $isFree,
        public string  $formattedPrice,
        public string  $formattedQuota,
        public string  $formattedInterval,
        public string  $statusLabel,
        public string  $statusButtonClass,
        public string  $statusDotClass,
    ) {}
}
