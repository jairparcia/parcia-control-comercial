<?php

namespace App\Domain\Subscription\Contracts;

use App\Domain\Subscription\Entities\CreateCheckoutSessionInput;
use App\Domain\Subscription\Results\PlanInfo;

interface PaymentGateway
{
    public function createCheckoutUrl(CreateCheckoutSessionInput $input): string;

    public function billingPortalUrl(int $userId, string $returnUrl): string;

    /** @return PlanInfo[] */
    public function getAvailablePlans(): array;
}
