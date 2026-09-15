<?php

namespace App\Domain\Subscription\Results;

readonly class PlanInfo
{
    public function __construct(
        public string $key,            // Plan enum value (starter, pro, agency…)
        public string $name,           // Product name from payment provider
        public string $formattedPrice, // e.g. "MX$100" or "$29 USD"
        public string $interval,       // month | year
        public string $currency,       // MXN, USD…
        public int    $quota = 0,      // max scans per billing period
        public bool   $isFree = false, // true for plans with no charge
        public array  $features = [],  // marketing feature list for billing/onboarding UI
    ) {}
}
