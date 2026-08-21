<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Contracts\PaymentGateway;

class GetBillingPortalUrlService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
    ) {}

    public function execute(int $userId, string $returnUrl): string
    {
        return $this->gateway->billingPortalUrl($userId, $returnUrl);
    }
}
