<?php

namespace App\Http\Presenters\Admin;

readonly class AdminCustomerDetailViewModel
{
    public function __construct(
        // Personal info
        public string  $name,
        public string  $email,
        public string  $memberSince,
        public string  $totalSpent,
        public string  $mrr,
        // Raw editable values (pre-fill the form)
        public ?string $description,
        public ?string $country,
        // Archive state
        public bool    $archived,
        // Subscription section
        public bool    $hasSub,
        public string  $subStripeId,
        public string  $subPlanName,
        public string  $subInterval,
        public string  $subNextDate,
        public string  $subNextAmount,
        // Payment history — each item: ['amount', 'date', 'plan']
        public array   $payments,
    ) {}
}
