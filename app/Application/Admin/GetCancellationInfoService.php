<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\SubscriptionProviderGatewayInterface;
use App\Domain\Admin\Results\SubscriptionCancellationInfoResult;

class GetCancellationInfoService
{
    public function __construct(
        private readonly SubscriptionProviderGatewayInterface $gateway,
    ) {}

    public function execute(string $stripeSubscriptionId): SubscriptionCancellationInfoResult
    {
        return $this->gateway->getCancellationInfo($stripeSubscriptionId);
    }
}
