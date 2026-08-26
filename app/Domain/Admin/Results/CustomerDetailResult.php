<?php

namespace App\Domain\Admin\Results;

readonly class CustomerDetailResult
{
    public function __construct(
        public int                    $id,
        public string                 $name,
        public string                 $email,
        public ?string                $description,
        public ?string                $country,
        public bool                   $archived,
        public \DateTimeImmutable     $memberSince,
        public int                    $totalSpentCents,
        public string                 $currency,
        public ?SubscriptionSummaryResult $subscription,
        /** @var PaymentHistoryItemResult[] */
        public array                  $paymentHistory,
    ) {}
}
