<?php

use App\Application\Admin\ImportStripeCustomersService;
use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;
use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Entities\StripeCustomerDataDTO;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeStripeCustomerData(array $overrides = []): StripeCustomerDataDTO
{
    return new StripeCustomerDataDTO(
        providerCustomerId: $overrides['providerCustomerId'] ?? 'cus_STRIPE_1',
        email:              $overrides['email']              ?? 'test@example.com',
        name:               $overrides['name']               ?? 'Test User',
        description:        $overrides['description']        ?? null,
        country:            $overrides['country']            ?? null,
        createdAt:          $overrides['createdAt']          ?? new \DateTimeImmutable('2025-01-01'),
    );
}

function makeCustomerImportService(
    CustomerProviderGatewayInterface $gateway,
    CustomerAdminRepositoryInterface $repo,
): ImportStripeCustomersService {
    return new ImportStripeCustomersService($gateway, $repo);
}

// ── Empty gateway ─────────────────────────────────────────────────────────────

it('returns 0 and skips insertMissing when gateway returns no customers', function () {
    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->expects('listAll')->once()->andReturn([]);

    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->expects('insertMissing')->never();

    expect(makeCustomerImportService($gateway, $repo)->execute())->toBe(0);
});

// ── Successful import ─────────────────────────────────────────────────────────

it('passes gateway customers to insertMissing and returns the inserted count', function () {
    $customers = [
        makeStripeCustomerData(['providerCustomerId' => 'cus_A']),
        makeStripeCustomerData(['providerCustomerId' => 'cus_B']),
    ];

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->expects('listAll')->once()->andReturn($customers);

    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->expects('insertMissing')->with($customers)->once()->andReturn(2);

    expect(makeCustomerImportService($gateway, $repo)->execute())->toBe(2);
});

it('returns 0 when all provider customers already exist in the database', function () {
    $customers = [makeStripeCustomerData()];

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->allows('listAll')->andReturn($customers);

    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->allows('insertMissing')->andReturn(0);

    expect(makeCustomerImportService($gateway, $repo)->execute())->toBe(0);
});

it('forwards a single customer correctly', function () {
    $customer = makeStripeCustomerData(['providerCustomerId' => 'cus_ONLY']);

    $gateway = Mockery::mock(CustomerProviderGatewayInterface::class);
    $gateway->allows('listAll')->andReturn([$customer]);

    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->expects('insertMissing')
        ->with([$customer])
        ->once()
        ->andReturn(1);

    expect(makeCustomerImportService($gateway, $repo)->execute())->toBe(1);
});
