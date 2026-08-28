<?php

namespace App\Http\Presenters\Admin;

readonly class SubscriptionDetailViewModel
{
    public function __construct(
        // Section 1 — Overview
        public string  $stripeId,
        public string  $userName,
        public string  $userEmail,
        public string  $statusLabel,
        public string  $statusBadgeClass,
        public string  $subscribedAt,
        public string  $planName,
        public string  $interval,
        public string  $formattedAmount,
        public string  $currentPeriod,

        // Section 2 — Upcoming invoice (null if no upcoming invoice)
        public ?array  $upcomingInvoice,

        // Section 3 — Invoice history
        // Each item: ['number', 'status', 'statusLabel', 'statusBadgeClass', 'amount', 'interval', 'email', 'date']
        public array   $invoices,
    ) {}
}
