<?php

namespace App\Domain\Subscription\Contracts;

use App\Domain\Subscription\Entities\CreateCheckoutSessionInputDTO;

interface PaymentGatewayInterface
{
    public function createCheckoutUrl(CreateCheckoutSessionInputDTO $input): string;

    public function billingPortalUrl(int $userId, string $returnUrl): string;
}
