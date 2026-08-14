<?php

namespace App\Domain\Subscription\Enums;

enum BillingEvent: string
{
    case SubscriptionActivated = 'subscription.activated';
    case SubscriptionCancelled = 'subscription.cancelled';
    case SubscriptionUpdated   = 'subscription.updated';
    case PaymentFailed         = 'payment.failed';
}
