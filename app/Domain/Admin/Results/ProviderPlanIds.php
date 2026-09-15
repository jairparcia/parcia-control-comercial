<?php

namespace App\Domain\Admin\Results;

readonly class ProviderPlanIds
{
    public function __construct(
        public string $productId,
        public string $priceId,
    ) {}
}
