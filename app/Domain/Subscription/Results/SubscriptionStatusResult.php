<?php

namespace App\Domain\Subscription\Results;

use App\Domain\Subscription\Enums\Plan;
use App\Domain\Subscription\Enums\SubscriptionStatus;

readonly class SubscriptionStatusResult
{
    public function __construct(
        public ?Plan $plan,
        public SubscriptionStatus $status,
        public ?string $renewsAt,
        public ?string $cancelledAt,
        public ?string $pmType,
        public ?string $pmLastFour,
    ) {}

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
