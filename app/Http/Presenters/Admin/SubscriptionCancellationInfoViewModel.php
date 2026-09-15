<?php

namespace App\Http\Presenters\Admin;

readonly class SubscriptionCancellationInfoViewModel
{
    public function __construct(
        public string $stripeSubscriptionId,
        public string $periodEndFormatted,
        public string $lastPaymentFormatted,
        public string $proratedAmountFormatted,
        public int    $proratedDays,
    ) {}
}
