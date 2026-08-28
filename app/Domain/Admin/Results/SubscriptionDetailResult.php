<?php

namespace App\Domain\Admin\Results;

readonly class SubscriptionDetailResult
{
    public function __construct(
        public string              $stripeId,
        public string              $stripeCustomerId,
        public int                 $userId,
        public string              $userName,
        public string              $userEmail,
        public string              $status,
        public string              $planName,
        public string              $interval,
        public int                 $unitAmountCents,
        public string              $currency,
        public \DateTimeImmutable  $subscribedAt,
        public ?UpcomingInvoiceResult $upcomingInvoice,
        /** @var SubscriptionInvoiceItemResult[] */
        public array               $invoices,
    ) {}
}
