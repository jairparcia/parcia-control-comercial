<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Contracts\PaymentGatewayInterface;
use App\Domain\Subscription\Entities\CreateCheckoutSessionInputDTO;
use App\Domain\Subscription\Results\CheckoutSessionResult;

class CreateCheckoutSessionService
{
    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
    ) {}

    public function execute(
        int $userId,
        string $planKey,
        string $successUrl,
        string $cancelUrl,
    ): CheckoutSessionResult {
        $input = new CreateCheckoutSessionInputDTO(
            userId:     $userId,
            planKey:    $planKey,
            successUrl: $successUrl,
            cancelUrl:  $cancelUrl,
        );

        $url = $this->gateway->createCheckoutUrl($input);

        return new CheckoutSessionResult(checkoutUrl: $url);
    }
}
