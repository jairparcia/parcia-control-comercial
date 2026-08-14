<?php

namespace App\Domain\Subscription\Enums;

enum Plan: string
{
    case Free     = 'free';
    case Starter  = 'starter';
    case Pro      = 'pro';
    case Agency   = 'agency';
    case Internal = 'internal';

    public function label(): string
    {
        return match ($this) {
            Plan::Free     => 'Gratuito',
            Plan::Starter  => 'Starter',
            Plan::Pro      => 'Pro',
            Plan::Agency   => 'Agency',
            Plan::Internal => 'Internal',
        };
    }

    public function isPaid(): bool
    {
        return ! in_array($this, [Plan::Free, Plan::Internal]);
    }

    public function supportsWebhooks(): bool
    {
        return in_array($this, [Plan::Pro, Plan::Agency, Plan::Internal]);
    }
}
