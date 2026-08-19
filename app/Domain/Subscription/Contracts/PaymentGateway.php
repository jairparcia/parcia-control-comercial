<?php

namespace App\Domain\Subscription\Contracts;

use App\Domain\Subscription\Entities\CreateCheckoutSessionInput;

interface PaymentGateway
{
    public function createCheckoutUrl(CreateCheckoutSessionInput $input): string;

    public function billingPortalUrl(int $userId, string $returnUrl): string;
}
