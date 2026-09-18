<?php

namespace App\Domain\Subscription\Results;

use App\Domain\Subscription\Enums\SubscriptionStatus;

readonly class SubscriptionStatusResult
{
    public function __construct(
        public ?PlanInfo $plan,
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

    // True once the user has actually picked a real (non-free) plan — used to
    // tell "never onboarded" apart from "already subscribed", regardless of
    // whether onboarded_at was persisted.
    public function hasSubscribedPlan(): bool
    {
        return $this->plan !== null && $this->plan->key !== 'free';
    }
}
