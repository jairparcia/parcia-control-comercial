<?php

namespace App\Listeners;

use App\Events\PlanPriceChanged;
use App\Jobs\NotifySubscribersJob;

class NotifySubscribersOfPriceChange
{
    public function handle(PlanPriceChanged $event): void
    {
        NotifySubscribersJob::dispatch(
            $event->planId,
            $event->planName,
            $event->newUnitAmountCents,
            $event->currency,
            $event->interval,
        );
    }
}
