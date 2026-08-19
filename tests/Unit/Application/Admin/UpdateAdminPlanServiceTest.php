<?php

use App\Application\Admin\UpdateAdminPlanService;
use App\Domain\Admin\Contracts\PlanAdminRepository;
use App\Domain\Admin\Contracts\PlanProviderGateway;
use App\Domain\Admin\Entities\UpdateAdminPlanInput;
use App\Domain\Admin\Results\AdminPlanResult;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makePlanResult(array $overrides = []): AdminPlanResult
{
    // Use array_key_exists for nullable fields so passing null is not treated as "absent".
    return new AdminPlanResult(
        id:             $overrides['id']          ?? 1,
        key:            $overrides['key']         ?? 'pro',
        name:           $overrides['name']        ?? 'Pro',
        description:    $overrides['description'] ?? '',
        features:       $overrides['features']    ?? [],
        quota:          $overrides['quota']       ?? 1000,
        unitAmount:     $overrides['unitAmount']  ?? 30000,
        currency:       $overrides['currency']    ?? 'MXN',
        interval:       $overrides['interval']    ?? 'month',
        sortOrder:      $overrides['sortOrder']   ?? 1,
        active:         $overrides['active']      ?? true,
        stripePriceId:  array_key_exists('stripePriceId', $overrides)   ? $overrides['stripePriceId']   : 'price_OLD',
        stripeProductId:array_key_exists('stripeProductId', $overrides) ? $overrides['stripeProductId'] : 'prod_ABC',
    );
}

function executeUpdate(
    UpdateAdminPlanService $service,
    int   $planId     = 1,
    array $overrides  = [],
): AdminPlanResult {
    return $service->execute(
        planId:      $planId,
        name:        $overrides['name']        ?? 'Pro',
        description: $overrides['description'] ?? '',
        features:    $overrides['features']    ?? [],
        quota:       $overrides['quota']       ?? 1000,
        unitAmount:  $overrides['unitAmount']  ?? 30000,
        currency:    $overrides['currency']    ?? 'MXN',
        interval:    $overrides['interval']    ?? 'month',
        sortOrder:   $overrides['sortOrder']   ?? 1,
    );
}

// ── Price change ──────────────────────────────────────────────────────────────

it('archives the old price ID when the price changes', function () {
    $current = makePlanResult(['stripePriceId' => 'price_OLD', 'unitAmount' => 30000]);
    $updated = makePlanResult(['stripePriceId' => 'price_NEW', 'unitAmount' => 50000]);

    $plans = Mockery::mock(PlanAdminRepository::class);
    $plans->allows('findById')->with(1)->andReturn($current);
    $plans->expects('appendLegacyPriceId')->with(1, 'price_OLD')->once();
    $plans->allows('update')->andReturn($updated);

    $provider = Mockery::mock(PlanProviderGateway::class);
    $provider->allows('replacePlanPrice')->andReturn('price_NEW');

    $result = executeUpdate(new UpdateAdminPlanService($plans, $provider), overrides: ['unitAmount' => 50000]);

    expect($result->stripePriceId)->toBe('price_NEW');
});

it('does not archive when the price is unchanged', function () {
    $current = makePlanResult(['unitAmount' => 30000]);
    $updated = makePlanResult(['unitAmount' => 30000]);

    $plans = Mockery::mock(PlanAdminRepository::class);
    $plans->allows('findById')->with(1)->andReturn($current);
    $plans->expects('appendLegacyPriceId')->never();
    $plans->allows('update')->andReturn($updated);

    $provider = Mockery::mock(PlanProviderGateway::class);
    $provider->expects('replacePlanPrice')->never();

    executeUpdate(new UpdateAdminPlanService($plans, $provider), overrides: ['unitAmount' => 30000]);
});

it('does not archive when the plan has no stripe product', function () {
    $current = makePlanResult(['stripeProductId' => null, 'stripePriceId' => null]);
    $updated = makePlanResult(['stripeProductId' => null, 'stripePriceId' => null]);

    $plans = Mockery::mock(PlanAdminRepository::class);
    $plans->allows('findById')->with(1)->andReturn($current);
    $plans->expects('appendLegacyPriceId')->never();
    $plans->allows('update')->andReturn($updated);

    $provider = Mockery::mock(PlanProviderGateway::class);
    $provider->expects('replacePlanPrice')->never();

    executeUpdate(new UpdateAdminPlanService($plans, $provider), overrides: ['unitAmount' => 99999]);
});

it('propagates the new price ID to the update call', function () {
    $current = makePlanResult(['unitAmount' => 30000]);
    $updated = makePlanResult(['stripePriceId' => 'price_NEW', 'unitAmount' => 50000]);

    $plans = Mockery::mock(PlanAdminRepository::class);
    $plans->allows('findById')->andReturn($current);
    $plans->allows('appendLegacyPriceId');
    $plans->expects('update')
        ->with(1, Mockery::type(UpdateAdminPlanInput::class), 'price_NEW')
        ->once()
        ->andReturn($updated);

    $provider = Mockery::mock(PlanProviderGateway::class);
    $provider->allows('replacePlanPrice')->andReturn('price_NEW');

    executeUpdate(new UpdateAdminPlanService($plans, $provider), overrides: ['unitAmount' => 50000]);
});
