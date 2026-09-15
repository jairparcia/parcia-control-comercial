<?php

namespace App\Http\Presenters\Admin;

readonly class AdminInvoiceViewModel
{
    public function __construct(
        public string  $stripeId,
        public string  $invoiceNumber,
        public string  $formattedTotal,
        public string  $status,
        public string  $statusLabel,
        public string  $statusBadgeClass,
        public string  $frequency,
        public string  $customerName,
        public string  $customerEmail,
        public string  $dueDate,
        public string  $date,
        public ?int    $userId = null,
        public ?string $stripeSubscriptionId = null,
    ) {}
}
