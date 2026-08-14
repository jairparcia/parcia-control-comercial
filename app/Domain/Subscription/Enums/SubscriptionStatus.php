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
            self::Active    => 'Activo',
            self::Trialing  => 'En periodo de prueba',
            self::PastDue   => 'Pago pendiente',
            self::Cancelled => 'Cancelado',
            self::None      => 'Sin plan',
        };
    }
}
