<?php

namespace App\Domain\Admin\Entities;

readonly class UpdateAdminPlanInput
{
    public function __construct(
        public string $name,
        public string $description,
        public array  $features,
        public int    $quota,
        public int    $unitAmount,
        public string $currency,
        public string $interval,
        public int    $sortOrder,
    ) {}
}
