<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;

interface SubscriptionProviderGatewayInterface
{
    /** @return ProviderSubscriptionDataDTO[] */
    public function listAll(): array;
}
