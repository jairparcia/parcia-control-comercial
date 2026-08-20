<?php

namespace App\Http\Presenters\Admin;

readonly class AdminSubscriptionViewModel
{
    public function __construct(
        public int     $id,
        public string  $stripeId,
        public string  $statusLabel,
        public string  $statusBadgeClass,
        public string  $statusDotClass,
        public string  $userName,
        public string  $userEmail,
        public string  $paymentMethod,
        public string  $planName,
        public string  $formattedMonthlyAverage,
        public string  $formattedAnnualAverage,
        public string  $subscribedAt,
        public ?string $endsAt,
    ) {}
}
