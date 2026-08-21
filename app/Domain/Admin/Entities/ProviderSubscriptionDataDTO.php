<?php

namespace App\Domain\Admin\Entities;

readonly class ProviderSubscriptionDataDTO
{
    public function __construct(
        public string              $providerSubscriptionId,
        public string              $providerCustomerId,
        public ?string             $customerEmail,
        public ?string             $customerName,
        public string              $status,
        public ?string             $priceId,
        public string              $type,
        public ?\DateTimeImmutable $trialEndsAt,
        public ?\DateTimeImmutable $endsAt,
        public \DateTimeImmutable  $createdAt,
    ) {}
}
