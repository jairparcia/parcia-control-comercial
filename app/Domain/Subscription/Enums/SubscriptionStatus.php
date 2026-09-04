<?php

namespace App\Domain\Subscription\Enums;

enum SubscriptionStatus: string
{
    case Active    = 'active';
    case Trialing  = 'trialing';
    case PastDue   = 'past_due';
    case Cancelled = 'cancelled';
    case None      = 'none';

    public function isActive(): bool
    {
        return in_array($this, [self::Active, self::Trialing]);
    }

    public function label(): string
    {
        return match ($this) {
            self::Active    => __('common.status_active'),
            self::Trialing  => __('common.status_trialing'),
            self::PastDue   => __('common.status_past_due'),
            self::Cancelled => __('common.status_canceled'),
            self::None      => __('common.status_no_plan'),
        };
    }
}
