<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\StripeCustomerDataDTO;

interface CustomerProviderGatewayInterface
{
    /** @return StripeCustomerDataDTO[] */
    public function listAll(): array;
}
