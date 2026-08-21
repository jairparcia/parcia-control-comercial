<?php

namespace App\Domain\Admin\Entities;

readonly class CancelSubscriptionInputDTO
{
    public function __construct(
        public string $stripeSubscriptionId,
        public bool   $immediately,
        public string $refundType, // none | full | prorated
    ) {}
}
