<?php

namespace App\Http\Presenters\Admin;

readonly class TransactionDetailViewModel
{
    public function __construct(
        // Header
        public string  $stripeId,
        public string  $formattedAmount,
        public string  $status,
        public string  $statusLabel,
        public string  $statusBadgeClass,
        public ?string $customerName,
        public ?string $customerEmail,

        // Payment breakdown
        public string  $formattedFees,
        public string  $formattedNet,

        // Payment method
        public ?string $paymentMethodId,
        public ?string $cardDisplay,
        public ?string $cardExpiry,
        public ?string $cardFingerprint,
        public ?string $cardType,
        public ?string $cardIssuer,
        public ?string $cardCountry,
        public ?string $cvcCheckLabel,
        public ?string $billingName,
        public ?string $billingEmail,
        public ?string $billingCountry,

        // Purchase summary
        public ?string $subscriptionId,
        public ?string $planName,
        public ?string $priceId,
        public ?string $invoiceNumber,
        public ?string $paymentIntentId,

        // Timeline — array of ['description' => string, 'time' => string]
        public array   $events,
        public string  $date,

        // Fee breakdown — array of ['description' => string, 'amount' => string]
        public array   $feeDetails = [],
    ) {}
}
