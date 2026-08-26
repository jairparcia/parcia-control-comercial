<?php

use App\Domain\Admin\Results\AdminCustomerResult;
use App\Domain\Admin\Results\CustomerDetailResult;
use App\Domain\Admin\Results\SubscriptionSummaryResult;
use App\Http\Presenters\Admin\AdminCustomerPresenter;

// --- Helpers ---

function makeAdminCustomerResult(bool $archived, bool $hasActiveSub): AdminCustomerResult
{
    return new AdminCustomerResult(
        id:           1,
        name:         'Test User',
        email:        'test@example.com',
        description:  null,
        country:      null,
        archived:     $archived,
        hasActiveSub: $hasActiveSub,
        createdAt:    new \DateTimeImmutable('2024-01-15'),
    );
}

function makeDetailResult(?SubscriptionSummaryResult $sub, int $totalSpentCents = 0, string $currency = 'MXN'): CustomerDetailResult
{
    return new CustomerDetailResult(
        id:              1,
        name:            'Test User',
        email:           'test@example.com',
        description:     null,
        country:         null,
        archived:        false,
        memberSince:     new \DateTimeImmutable('2024-01-01'),
        totalSpentCents: $totalSpentCents,
        currency:        $currency,
        subscription:    $sub,
        paymentHistory:  [],
    );
}

function makeSubResult(string $interval, int $unitAmountCents, string $currency = 'MXN'): SubscriptionSummaryResult
{
    return new SubscriptionSummaryResult(
        stripeSubscriptionId:   'sub_test',
        planName:               'Pro',
        interval:               $interval,
        unitAmountCents:        $unitAmountCents,
        currency:               $currency,
        nextBillingDate:        null,
        nextBillingAmountCents: 0,
    );
}

// --- Status label / color ---

it('returns Archived status for an archived customer', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentAll([makeAdminCustomerResult(archived: true, hasActiveSub: false)])[0];

    expect($vm->statusLabel)->toBe('Archived');
    expect($vm->statusColor)->toContain('amber');
});

it('prioritises archived over active subscription for status', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentAll([makeAdminCustomerResult(archived: true, hasActiveSub: true)])[0];

    expect($vm->statusLabel)->toBe('Archived');
});

it('returns Active status for a customer with an active subscription', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentAll([makeAdminCustomerResult(archived: false, hasActiveSub: true)])[0];

    expect($vm->statusLabel)->toBe('Active');
    expect($vm->statusColor)->toContain('emerald');
});

it('returns No subscription status for a customer without a subscription', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentAll([makeAdminCustomerResult(archived: false, hasActiveSub: false)])[0];

    expect($vm->statusLabel)->toBe('No subscription');
});

// --- MRR calculation ---

it('uses the unit amount directly for monthly plans', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentDetail(makeDetailResult(makeSubResult('month', 5000)));

    expect($vm->mrr)->toBe('$50.00/mo');
});

it('divides the annual amount by 12 for yearly plans', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentDetail(makeDetailResult(makeSubResult('year', 60000)));

    // 60000 cents / 12 = 5000 cents = $50.00/mo
    expect($vm->mrr)->toBe('$50.00/mo');
});

it('returns a dash when there is no subscription', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentDetail(makeDetailResult(null));

    expect($vm->mrr)->toBe('—');
});

// --- Total spent ---

it('formats total spent in cents as a currency string', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentDetail(makeDetailResult(null, totalSpentCents: 15050));

    expect($vm->totalSpent)->toBe('$150.50');
});

it('formats USD amounts with the US$ prefix', function () {
    $presenter = new AdminCustomerPresenter();
    $vm = $presenter->presentDetail(makeDetailResult(null, totalSpentCents: 1000, currency: 'USD'));

    expect($vm->totalSpent)->toBe('US$10.00');
});
