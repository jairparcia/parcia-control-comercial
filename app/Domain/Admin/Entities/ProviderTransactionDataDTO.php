<?php

namespace App\Domain\Admin\Entities;

readonly class ProviderTransactionDataDTO
{
    public function __construct(
        public string             $stripeId,
        public int                $amountCents,
        public int                $amountRefundedCents,
        public string             $currency,
        public string             $status,
        public ?string            $paymentMethodType,
        public ?string            $cardBrand,
        public ?string            $cardLast4,
        public ?string            $description,
        public ?string            $customerName,
        public ?string            $customerEmail,
        public ?string            $stripeCustomerId,
        public \DateTimeImmutable $createdAt,
    ) {}
}
