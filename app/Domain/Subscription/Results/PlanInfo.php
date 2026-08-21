<?php

namespace App\Domain\Subscription\Results;

readonly class PlanInfo
{
    public function __construct(
        public string $key,             // Plan enum value (starter, pro, agency…)
        public string $name,            // Product name from payment provider
        public string $formattedPrice,  // e.g. "MX$100" or "$29 USD"
        public string $interval,        // month | year
        public string $currency,        // MXN, USD…
    ) {}
}
