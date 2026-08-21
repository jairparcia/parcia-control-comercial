<?php

namespace App\Domain\Subscription\Entities;

use App\Domain\Subscription\Enums\Plan;

readonly class CreateCheckoutSessionInput
{
    public function __construct(
        public int $userId,
        public Plan $plan,
        public string $successUrl,
        public string $cancelUrl,
    ) {}
}
