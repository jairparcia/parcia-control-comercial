<?php

namespace App\Domain\Subscription\Entities;

use App\Domain\Subscription\Enums\BillingEvent;

readonly class BillingEventInputDTO
{
    public function __construct(
        public BillingEvent $event,
        public ?int $userId,
        public ?string $planKey,
    ) {}
}
