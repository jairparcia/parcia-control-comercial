<?php

namespace App\Domain\Subscription\Results;

readonly class CheckoutSessionResult
{
    public function __construct(
        public string $checkoutUrl,
    ) {}
}
