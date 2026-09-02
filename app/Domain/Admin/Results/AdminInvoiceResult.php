<?php

namespace App\Domain\Admin\Results;

readonly class AdminInvoiceResult
{
    public function __construct(
        public string             $stripeId,
        public ?string            $invoiceNumber,
        public int                $totalCents,
        public string             $currency,
        public string             $status,
        public ?string            $interval,
        public int                $intervalCount,
        public ?string            $customerName,
        public ?string            $customerEmail,
        public ?string            $stripeCustomerId,
        public ?\DateTimeImmutable $dueDate,
        public \DateTimeImmutable  $createdAt,
        public ?int               $id = null,
        public ?int               $userId = null,
        public ?string            $stripeSubscriptionId = null,
    ) {}
}
