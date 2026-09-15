<?php

use App\Application\Admin\AdminPlanService;
use App\Domain\Admin\Contracts\PlanAdminRepositoryInterface;
use App\Domain\Admin\Contracts\PlanProviderGatewayInterface;
use App\Domain\Admin\Entities\UpdateAdminPlanInputDTO;
use App\Domain\Admin\Results\AdminPlanResult;
use App\Domain\Admin\Results\ProviderPlanIds;
use App\Events\PlanPriceChanged;
use Illuminate\Support\Facades\Event;

// ── Helpers ───────────────────────────────────────────────────────────────────

function planResult(array $overrides = []): AdminPlanResult
{
    return new AdminPlanResult(
        id:             $overrides['id']             ?? 1,
        key:            $overrides['key']            ?? 'pro',
        name:           $overrides['name']           ?? 'Pro',
        description:    $overrides['description']    ?? '',
        features:       $overrides['features']       ?? [],
        quota:          $overrides['quota']          ?? 1000,
        unitAmount:     $overrides['unitAmount']     ?? 30000,
        currency:       $overrides['currency']       ?? 'MXN',
        interval:       $overrides['interval']       ?? 'month',
        sortOrder:      $overrides['sortOrder']      ?? 1,
        active:         $overrides['active']         ?? true,
        stripePriceId:  array_key_exists('stripePriceId', $overrides)   ? $overrides['stripePriceId']   : 'price_ABC',
        stripeProductId:array_key_exists('stripeProductId', $overrides) ? $overrides['stripeProductId'] : 'prod_ABC',
    );
}

// ── list() ────────────────────────────────────────────────────────────────────

it('list() returns all plans from the repository', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->expects('all')->once()->andReturn([planResult(), planResult(['id' => 2])]);

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);

    $result = (new AdminPlanService($plans, $provider))->list();

    expect($result)->toHaveCount(2);
});

// ── create() ─────────────────────────────────────────────────────────────────

it('create() calls createPlan in Stripe when unitAmount is greater than zero', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('create')->andReturn(planResult());

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('createPlan')->with('Pro', 50000, 'MXN', 'month')->once()
        ->andReturn(new ProviderPlanIds('prod_NEW', 'price_NEW'));

    (new AdminPlanService($plans, $provider))->create('Pro', 'pro', '', [], 1000, 50000, 'MXN', 'month', 1);
});

it('create() skips Stripe when unitAmount is zero', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('create')->andReturn(planResult());

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('createPlan')->never();

    (new AdminPlanService($plans, $provider))->create('Pro', 'pro', '', [], 1000, 0, 'MXN', 'month', 1);
});

it('create() passes null Stripe IDs to repository when unitAmount is zero', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->expects('create')
        ->with(Mockery::any(), null, null)
        ->once()
        ->andReturn(planResult(['stripeProductId' => null, 'stripePriceId' => null]));

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);

    (new AdminPlanService($plans, $provider))->create('Pro', 'pro', '', [], 1000, 0, 'MXN', 'month', 1);
});

// ── update() ─────────────────────────────────────────────────────────────────

it('update() calls updatePlanName when the name changes and the plan has a Stripe product', function () {
    $current = planResult(['name' => 'Pro', 'stripeProductId' => 'prod_ABC']);
    $updated = planResult(['name' => 'Pro Plus']);

    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn($current);
    $plans->allows('update')->andReturn($updated);

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('updatePlanName')->with('prod_ABC', 'Pro Plus')->once();

    (new AdminPlanService($plans, $provider))->update(1, 'Pro Plus', '', [], 1000, 1);
});

it('update() does not call updatePlanName when the name is unchanged', function () {
    $current = planResult(['name' => 'Pro']);
    $updated = planResult(['name' => 'Pro']);

    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn($current);
    $plans->allows('update')->andReturn($updated);

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('updatePlanName')->never();

    (new AdminPlanService($plans, $provider))->update(1, 'Pro', '', [], 1000, 1);
});

it('update() does not call updatePlanName when the plan has no Stripe product', function () {
    $current = planResult(['name' => 'Pro', 'stripeProductId' => null]);
    $updated = planResult(['stripeProductId' => null]);

    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn($current);
    $plans->allows('update')->andReturn($updated);

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('updatePlanName')->never();

    (new AdminPlanService($plans, $provider))->update(1, 'Pro Plus', '', [], 1000, 1);
});

it('update() passes the correct DTO to the repository', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult());
    $plans->expects('update')
        ->with(1, Mockery::on(fn ($dto) =>
            $dto instanceof UpdateAdminPlanInputDTO
            && $dto->name      === 'New Name'
            && $dto->quota     === 2000
            && $dto->sortOrder === 3
        ))
        ->once()
        ->andReturn(planResult());

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->allows('updatePlanName');

    (new AdminPlanService($plans, $provider))->update(1, 'New Name', '', [], 2000, 3);
});

// ── toggle() ─────────────────────────────────────────────────────────────────

it('toggle() deactivates the Stripe price when the plan is being disabled', function () {
    $plan = planResult(['stripePriceId' => 'price_ABC', 'active' => true]);

    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn($plan);
    $plans->allows('toggle')->andReturn(false);

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('deactivatePlan')->with('price_ABC')->once();

    $result = (new AdminPlanService($plans, $provider))->toggle(1);

    expect($result)->toBeFalse();
});

it('toggle() does not call Stripe when the plan is being enabled', function () {
    $plan = planResult(['active' => false]);

    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn($plan);
    $plans->allows('toggle')->andReturn(true);

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('deactivatePlan')->never();

    $result = (new AdminPlanService($plans, $provider))->toggle(1);

    expect($result)->toBeTrue();
});

it('toggle() does not call Stripe when the plan has no Stripe price', function () {
    $plan = planResult(['stripePriceId' => null]);

    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn($plan);
    $plans->allows('toggle')->andReturn(false);

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('deactivatePlan')->never();

    (new AdminPlanService($plans, $provider))->toggle(1);
});

// ── listPrices() ──────────────────────────────────────────────────────────────

it('listPrices() returns empty array when plan has no Stripe product', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult(['stripeProductId' => null, 'stripePriceId' => null]));

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('listProductPrices')->never();

    $result = (new AdminPlanService($plans, $provider))->listPrices(1);

    expect($result)->toBe([]);
});

it('listPrices() fetches prices from Stripe and maps them to PlanPriceResult', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult(['stripePriceId' => 'price_ABC', 'stripeProductId' => 'prod_ABC']));
    $plans->allows('countActiveSubscriptionsForPrice')->andReturn(2);

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->allows('listProductPrices')->andReturn([[
        'id'          => 'price_ABC',
        'unit_amount' => 30000,
        'currency'    => 'mxn',
        'active'      => true,
        'recurring'   => ['interval' => 'month', 'interval_count' => 1],
        'created'     => 1700000000,
    ]]);

    $result = (new AdminPlanService($plans, $provider))->listPrices(1);

    expect($result)->toHaveCount(1)
        ->and($result[0]->stripeId)->toBe('price_ABC')
        ->and($result[0]->unitAmountCents)->toBe(30000)
        ->and($result[0]->isDefault)->toBeTrue()
        ->and($result[0]->activeSubscriptionsCount)->toBe(2);
});

// ── addPrice() ────────────────────────────────────────────────────────────────

it('addPrice() creates product and price in Stripe when plan has no Stripe IDs', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult(['stripeProductId' => null, 'stripePriceId' => null]));
    $plans->allows('setStripeIds');

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('createPlan')->with('Pro', 50000, 'MXN', 'month')->once()
        ->andReturn(new ProviderPlanIds('prod_NEW', 'price_NEW'));
    $provider->expects('addPriceToProduct')->never();

    (new AdminPlanService($plans, $provider))->addPrice(1, 50000, 'MXN', 'month');
});

it('addPrice() stores Stripe IDs via setStripeIds when creating a new product', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult(['stripeProductId' => null, 'stripePriceId' => null]));
    $plans->expects('setStripeIds')->with(1, 'prod_NEW', 'price_NEW', 50000, 'MXN', 'month')->once();

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->allows('createPlan')->andReturn(new ProviderPlanIds('prod_NEW', 'price_NEW'));

    (new AdminPlanService($plans, $provider))->addPrice(1, 50000, 'MXN', 'month');
});

it('addPrice() adds price to existing Stripe product when plan already has one', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult());
    $plans->allows('updateDefaultPrice');

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('addPriceToProduct')->with('prod_ABC', 80000, 'USD', 'year')->once()->andReturn('price_NEW');
    $provider->expects('createPlan')->never();

    (new AdminPlanService($plans, $provider))->addPrice(1, 80000, 'USD', 'year');
});

it('addPrice() updates default price in DB when adding to existing product', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult());
    $plans->expects('updateDefaultPrice')->with(1, 'price_NEW', 80000, 'USD', 'year')->once();

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->allows('addPriceToProduct')->andReturn('price_NEW');

    (new AdminPlanService($plans, $provider))->addPrice(1, 80000, 'USD', 'year');
});

// ── setDefaultPrice() ─────────────────────────────────────────────────────────

it('setDefaultPrice() sets the price as default in Stripe and syncs the DB', function () {
    Event::fake();

    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult());
    $plans->expects('updateDefaultPrice')
        ->with(1, 'price_B', 80000, 'USD', 'year')
        ->once();

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('setDefaultPrice')->with('prod_ABC', 'price_B')->once();
    $provider->allows('retrievePrice')->andReturn([
        'unit_amount' => 80000,
        'currency'    => 'usd',
        'recurring'   => ['interval' => 'year'],
    ]);

    (new AdminPlanService($plans, $provider))->setDefaultPrice(1, 'price_B');

    Event::assertDispatched(PlanPriceChanged::class, fn ($e) =>
        $e->planId             === 1     &&
        $e->planName           === 'Pro' &&
        $e->newUnitAmountCents === 80000 &&
        $e->currency           === 'USD' &&
        $e->interval           === 'year'
    );
});

// ── archivePrice() ────────────────────────────────────────────────────────────

it('archivePrice() archives the price in Stripe', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult(['stripePriceId' => 'price_OTHER']));

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('archivePrice')->with('price_DIFF')->once();

    (new AdminPlanService($plans, $provider))->archivePrice(1, 'price_DIFF');
});

it('archivePrice() throws when attempting to archive the current default price', function () {
    $plans = Mockery::mock(PlanAdminRepositoryInterface::class);
    $plans->allows('findById')->andReturn(planResult(['stripePriceId' => 'price_ABC']));

    $provider = Mockery::mock(PlanProviderGatewayInterface::class);
    $provider->expects('archivePrice')->never();

    expect(fn () => (new AdminPlanService($plans, $provider))->archivePrice(1, 'price_ABC'))
        ->toThrow(\RuntimeException::class);
});
