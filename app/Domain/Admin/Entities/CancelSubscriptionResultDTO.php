<?php

namespace App\Domain\Admin\Entities;

readonly class CancelSubscriptionResultDTO
{
    public function __construct(
        public bool                $immediate,
        public ?\DateTimeImmutable $scheduledEndsAt,
    ) {}
}
