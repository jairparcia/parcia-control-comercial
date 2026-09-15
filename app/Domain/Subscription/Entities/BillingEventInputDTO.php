<?php

namespace App\Domain\Subscription\Entities;

use App\Domain\Subscription\Enums\BillingEvent;
use App\Domain\Subscription\Enums\Plan;

readonly class BillingEventInputDTO
{
    public function __construct(
        public BillingEvent $event,
        public ?int $userId,
        public ?Plan $plan,
    ) {}
}
