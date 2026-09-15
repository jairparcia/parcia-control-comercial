<?php

namespace App\Domain\Admin\Entities;

readonly class StripeCustomerDataDTO
{
    public function __construct(
        public string             $providerCustomerId,
        public string             $email,
        public ?string            $name,
        public ?string            $description,
        public ?string            $country,
        public \DateTimeImmutable $createdAt,
    ) {}
}
