<?php

namespace App\Domain\Subscription\Entities;

readonly class CreateCheckoutSessionInputDTO
{
    public function __construct(
        public int    $userId,
        public string $planKey,
        public string $successUrl,
        public string $cancelUrl,
    ) {}
}
