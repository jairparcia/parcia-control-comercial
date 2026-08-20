<?php

use App\Application\Admin\ImportStripeSubscriptionsService;
use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Contracts\SubscriptionProviderGatewayInterface;
use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;
// ── Helpers ───────────────────────────────────────────────────────────────────

function makeProviderData(array $overrides = []): ProviderSubscriptionDataDTO
{
    return new ProviderSubscriptionDataDTO(
        providerSubscriptionId: $overrides['providerSubscriptionId'] ?? 'sub_STRIPE_1',
        providerCustomerId:     $overrides['providerCustomerId']     ?? 'cus_CUSTOMER_1',
        status:                 $overrides['status']                 ?? 'active',
        priceId:                $overrides['priceId']                ?? 'price_STARTER',
        type:                   $overrides['type']                   ?? 'default',
        trialEndsAt:            $overrides['trialEndsAt']            ?? null,
        endsAt:                 $overrides['endsAt']                 ?? null,
        createdAt:              $overrides['createdAt']              ?? new \DateTimeImmutable('2025-01-01'),
    );
}

function makeImportService(
    SubscriptionProviderGatewayInterface $gateway,
    SubscriptionAdminRepositoryInterface $repo,
): ImportStripeSubscriptionsService {
    return new ImportStripeSubscriptionsService($gateway, $repo);
}

// ── Empty gateway ─────────────────────────────────────────────────────────────

it('returns 0 and skips insertMissing when gateway returns no subscriptions', function () {
    $gateway = Mockery::mock(SubscriptionProviderGatewayInterface::class);
    $gateway->expects('listAll')->once()->andReturn([]);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('insertMissing')->never();

    expect(makeImportService($gateway, $repo)->execute())->toBe(0);
});

// ── Successful import ─────────────────────────────────────────────────────────

it('passes gateway subscriptions to insertMissing and returns the inserted count', function () {
    $subs = [
        makeProviderData(['providerSubscriptionId' => 'sub_A']),
        makeProviderData(['providerSubscriptionId' => 'sub_B']),
    ];

    $gateway = Mockery::mock(SubscriptionProviderGatewayInterface::class);
    $gateway->expects('listAll')->once()->andReturn($subs);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('insertMissing')->with($subs)->once()->andReturn(2);

    expect(makeImportService($gateway, $repo)->execute())->toBe(2);
});

it('returns 0 when all provider subscriptions already exist in the database', function () {
    $subs = [makeProviderData()];

    $gateway = Mockery::mock(SubscriptionProviderGatewayInterface::class);
    $gateway->allows('listAll')->andReturn($subs);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->allows('insertMissing')->andReturn(0);

    expect(makeImportService($gateway, $repo)->execute())->toBe(0);
});

it('forwards a single subscription correctly', function () {
    $sub = makeProviderData(['providerSubscriptionId' => 'sub_ONLY']);

    $gateway = Mockery::mock(SubscriptionProviderGatewayInterface::class);
    $gateway->allows('listAll')->andReturn([$sub]);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('insertMissing')
        ->with([$sub])
        ->once()
        ->andReturn(1);

    expect(makeImportService($gateway, $repo)->execute())->toBe(1);
});
