<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Contracts\PaymentGateway;
use App\Domain\Subscription\Results\PlanInfo;

class GetAvailablePlansService
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    /** @return PlanInfo[] */
    public function execute(): array
    {
        return $this->gateway->getAvailablePlans();
    }
}
