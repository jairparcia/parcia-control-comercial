<?php

use App\Events\PlanPriceChanged;
use App\Jobs\NotifySubscribersJob;
use App\Listeners\NotifySubscribersOfPriceChange;
use Illuminate\Support\Facades\Bus;

it('dispatches NotifySubscribersJob with the event data', function () {
    Bus::fake();

    $event = new PlanPriceChanged(
        planId: 7,
        planName: 'Pro',
        newUnitAmountCents: 80000,
        currency: 'USD',
        interval: 'year',
    );

    (new NotifySubscribersOfPriceChange())->handle($event);

    Bus::assertDispatched(NotifySubscribersJob::class, fn (NotifySubscribersJob $job) => $job->planId === 7
        && $job->planName === 'Pro'
        && $job->newUnitAmountCents === 80000
        && $job->currency === 'USD'
        && $job->interval === 'year');
});
