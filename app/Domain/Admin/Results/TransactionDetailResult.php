<?php

namespace App\Domain\Admin\Results;

readonly class TransactionDetailResult
{
    public function __construct(
        // Header
        public string             $stripeId,
        public int                $amountCents,
        public string             $currency,
        public string             $status,
        public ?string            $customerName,
        public ?string            $customerEmail,

        // Payment breakdown
        public int                $stripeFeesCents,
        public int                $netAmountCents,

        // Payment method (card details)
        public ?string            $paymentMethodId,
        public ?string            $cardLast4,
        public ?string            $cardFingerprint,
        public ?string            $cardExpMonth,
        public ?string            $cardExpYear,
        public ?string            $cardFunding,
        public ?string            $cardBrand,
        public ?string            $cardIssuer,
        public ?string            $cardCountry,
        public ?string            $cvcCheck,

        // Billing details
        public ?string            $billingName,
        public ?string            $billingEmail,
        public ?string            $billingCountry,

        // Purchase summary
        public ?string            $subscriptionId,
        public ?string            $planName,
        public ?string            $priceId,
        public ?string            $invoiceNumber,
        public ?string            $paymentIntentId,

        // Timeline
        /** @var TransactionEventResult[] */
        public array              $events,
        public \DateTimeImmutable $createdAt,

        // Fee breakdown from balance_transaction.fee_details
        /** @var TransactionFeeDetailResult[] */
        public array              $feeDetails = [],
    ) {}
}
