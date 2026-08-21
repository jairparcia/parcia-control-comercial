<?php

namespace App\Domain\Admin\Results;

readonly class AdminSubscriptionResult
{
    public function __construct(
        public int               $id,
        public string            $stripeId,
        public string            $status,
        public string            $userName,
        public string            $userEmail,
        public ?string           $pmType,
        public ?string           $pmLastFour,
        public ?string           $planName,
        public ?string           $planKey,
        public ?int              $unitAmount,
        public ?string           $currency,
        public ?string           $interval,
        public \DateTimeImmutable  $subscribedAt,
        public ?\DateTimeImmutable $endsAt,
    ) {}
}
