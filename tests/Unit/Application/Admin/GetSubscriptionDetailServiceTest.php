<?php

use App\Application\Admin\GetSubscriptionDetailService;
use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Results\AdminSubscriptionResult;
use App\Domain\Admin\Results\SubscriptionDetailResult;
use App\Domain\Admin\Results\SubscriptionInvoiceItemResult;
use App\Domain\Admin\Results\UpcomingInvoiceResult;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeDetailSubResult(array $overrides = []): AdminSubscriptionResult
{
    return new AdminSubscriptionResult(
        id:               $overrides['id']               ?? 1,
        userId:           $overrides['userId']           ?? 42,
        stripeId:         $overrides['stripeId']         ?? 'sub_TEST',
        stripeCustomerId: array_key_exists('stripeCustomerId', $overrides)
            ? $overrides['stripeCustomerId']
            : 'cus_TEST',
        status:           $overrides['status']           ?? 'active',
        userName:         $overrides['userName']         ?? 'Jane Doe',
        userEmail:        $overrides['userEmail']        ?? 'jane@example.com',
        pmType:           null,
        pmLastFour:       null,
        planName:         array_key_exists('planName', $overrides)   ? $overrides['planName']   : 'Pro',
        planKey:          $overrides['planKey']          ?? 'pro',
        unitAmount:       array_key_exists('unitAmount', $overrides)  ? $overrides['unitAmount']  : 99900,
        currency:         array_key_exists('currency', $overrides)    ? $overrides['currency']    : 'MXN',
        interval:         $overrides['interval']         ?? 'month',
        subscribedAt:     new \DateTimeImmutable('2025-01-15'),
        endsAt:           null,
    );
}

function makeUpcomingInvoice(): UpcomingInvoiceResult
{
    return new UpcomingInvoiceResult(
        periodStart:          new \DateTimeImmutable('2025-09-20'),
        nextBillingDate:      new \DateTimeImmutable('2025-10-20'),
        description:          'Pro Plan',
        quantity:             1,
        unitAmountCents:      99900,
        amountDueCents:       99900,
        currency:             'MXN',
        subtotalCents:        99900,
        taxCents:             0,
        totalCents:           99900,
        amountPaidCents:      0,
        amountRemainingCents: 99900,
    );
}

function makeInvoiceItem(string $number = 'INV-001'): SubscriptionInvoiceItemResult
{
    return new SubscriptionInvoiceItemResult(
        invoiceNumber: $number,
        amountCents:   99900,
        currency:      'MXN',
        interval:      'month',
        status:        'paid',
        createdAt:     new \DateTimeImmutable('2025-09-20'),
    );
}

function makeService(
    SubscriptionAdminRepositoryInterface $repo,
    CustomerProviderGatewayInterface     $gateway,
): GetSubscriptionDetailService {
    return new GetSubscriptionDetailService($repo, $gateway);
}

// ── Returns null when not found ───────────────────────────────────────────────

it('returns null when the subscription is not found', function () {
    $repo    = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);

    $repo->expects('findByStripeId')->with('sub_MISSING')->andReturn(null);
    $gateway->shouldNotReceive('getUpcomingInvoice');
    $gateway->shouldNotReceive('getSubscriptionInvoices');

    $result = makeService($repo, $gateway)->execute('sub_MISSING');

    expect($result)->toBeNull();
});

// ── Assembles result fields ───────────────────────────────────────────────────

it('assembles the SubscriptionDetailResult with correct fields', function () {
    $sub     = makeDetailSubResult();
    $invoices = [makeInvoiceItem()];
    $upcoming = makeUpcomingInvoice();

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('findByStripeId')->with('sub_TEST')->andReturn($sub);

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->expects('getUpcomingInvoice')->with('cus_TEST', 'sub_TEST')->andReturn($upcoming);
    $gateway->expects('getSubscriptionInvoices')->with('sub_TEST')->andReturn($invoices);

    $result = makeService($repo, $gateway)->execute('sub_TEST');

    expect($result)->toBeInstanceOf(SubscriptionDetailResult::class)
        ->and($result->stripeId)->toBe('sub_TEST')
        ->and($result->stripeCustomerId)->toBe('cus_TEST')
        ->and($result->userId)->toBe(42)
        ->and($result->userName)->toBe('Jane Doe')
        ->and($result->userEmail)->toBe('jane@example.com')
        ->and($result->status)->toBe('active')
        ->and($result->planName)->toBe('Pro')
        ->and($result->interval)->toBe('month')
        ->and($result->unitAmountCents)->toBe(99900)
        ->and($result->currency)->toBe('MXN')
        ->and($result->upcomingInvoice)->toBe($upcoming)
        ->and($result->invoices)->toBe($invoices);
});

// ── Upcoming invoice skipped when no stripeCustomerId ─────────────────────────

it('sets upcomingInvoice to null when stripeCustomerId is null', function () {
    $sub = makeDetailSubResult(['stripeCustomerId' => null]);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('findByStripeId')->andReturn($sub);

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->shouldNotReceive('getUpcomingInvoice');
    $gateway->expects('getSubscriptionInvoices')->andReturn([]);

    $result = makeService($repo, $gateway)->execute('sub_TEST');

    expect($result->upcomingInvoice)->toBeNull();
});

// ── Invoice history always fetched ────────────────────────────────────────────

it('always fetches the invoice history regardless of upcoming invoice', function () {
    $sub      = makeDetailSubResult(['stripeCustomerId' => null]);
    $invoices = [makeInvoiceItem('INV-001'), makeInvoiceItem('INV-002')];

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('findByStripeId')->andReturn($sub);

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->shouldNotReceive('getUpcomingInvoice');
    $gateway->expects('getSubscriptionInvoices')->with('sub_TEST')->andReturn($invoices);

    $result = makeService($repo, $gateway)->execute('sub_TEST');

    expect($result->invoices)->toHaveCount(2);
});

// ── Empty invoice history ─────────────────────────────────────────────────────

it('returns an empty invoices array when the gateway returns none', function () {
    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('findByStripeId')->andReturn(makeDetailSubResult());

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->allows('getUpcomingInvoice')->andReturn(null);
    $gateway->expects('getSubscriptionInvoices')->andReturn([]);

    $result = makeService($repo, $gateway)->execute('sub_TEST');

    expect($result->invoices)->toBeEmpty();
});

// ── Fallbacks for nullable plan fields ───────────────────────────────────────

it('falls back to empty string for planName when null', function () {
    $sub = makeDetailSubResult(['planName' => null]);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('findByStripeId')->andReturn($sub);

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->allows('getUpcomingInvoice')->andReturn(null);
    $gateway->allows('getSubscriptionInvoices')->andReturn([]);

    $result = makeService($repo, $gateway)->execute('sub_TEST');

    expect($result->planName)->toBe('');
});

it('falls back to month for interval when null', function () {
    $sub = makeDetailSubResult(['interval' => null]);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('findByStripeId')->andReturn($sub);

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->allows('getUpcomingInvoice')->andReturn(null);
    $gateway->allows('getSubscriptionInvoices')->andReturn([]);

    $result = makeService($repo, $gateway)->execute('sub_TEST');

    expect($result->interval)->toBe('month');
});

it('falls back to 0 for unitAmountCents when null', function () {
    $sub = makeDetailSubResult(['unitAmount' => null]);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('findByStripeId')->andReturn($sub);

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->allows('getUpcomingInvoice')->andReturn(null);
    $gateway->allows('getSubscriptionInvoices')->andReturn([]);

    $result = makeService($repo, $gateway)->execute('sub_TEST');

    expect($result->unitAmountCents)->toBe(0);
});

it('falls back to MXN for currency when null', function () {
    $sub = makeDetailSubResult(['currency' => null]);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('findByStripeId')->andReturn($sub);

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->allows('getUpcomingInvoice')->andReturn(null);
    $gateway->allows('getSubscriptionInvoices')->andReturn([]);

    $result = makeService($repo, $gateway)->execute('sub_TEST');

    expect($result->currency)->toBe('MXN');
});
