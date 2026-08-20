<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Contracts\PaymentGatewayInterface;

class GetBillingPortalUrlService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
    ) {}

    public function execute(int $userId, string $returnUrl): string
    {
        return $this->gateway->billingPortalUrl($userId, $returnUrl);
    }
}
