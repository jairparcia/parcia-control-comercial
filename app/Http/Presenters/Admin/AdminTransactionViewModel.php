<?php

namespace App\Http\Presenters\Admin;

readonly class AdminTransactionViewModel
{
    public function __construct(
        public string  $stripeId,
        public string  $formattedAmount,
        public string  $formattedAmountRefunded,
        public string  $status,
        public string  $statusLabel,
        public string  $statusBadgeClass,
        public string  $paymentMethod,
        public string  $description,
        public string  $customerName,
        public string  $customerEmail,
        public string  $date,
    ) {}
}
