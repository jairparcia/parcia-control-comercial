<?php

use App\Application\Admin\ImportStripeSubscriptionsService;
use App\Application\Admin\ListAdminSubscriptionsService;
use App\Domain\Admin\Results\AdminSubscriptionResult;
use App\Livewire\Admin\SubscriptionsComponent;
use App\Models\User;
use Livewire\Livewire;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeComponentSubResult(array $overrides = []): AdminSubscriptionResult
{
    return new AdminSubscriptionResult(
        id:           $overrides['id']        ?? 1,
        stripeId:     $overrides['stripeId']  ?? 'sub_TEST',
        status:       $overrides['status']    ?? 'active',
        userName:     $overrides['userName']  ?? 'Jane Doe',
        userEmail:    $overrides['userEmail'] ?? 'jane@example.com',
        pmType:       null,
        pmLastFour:   null,
        planName:     $overrides['planName']  ?? 'Starter',
        planKey:      $overrides['planKey']   ?? 'starter',
        unitAmount:   50000,
        currency:     'MXN',
        interval:     'month',
        subscribedAt: new \DateTimeImmutable('2025-01-15'),
        endsAt:       null,
    );
}

// ── Access control ────────────────────────────────────────────────────────────

it('redirects unauthenticated users to login', function () {
    $this->get(route('admin.subscriptions'))->assertRedirect(route('login'));
});

it('returns 403 for authenticated external users', function () {
    $this->actingAs(User::factory()->create(['role' => 'external']))
        ->get(route('admin.subscriptions'))
        ->assertForbidden();
});

it('renders the subscriptions page for internal users', function () {
    $this->mock(ListAdminSubscriptionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    $this->actingAs(User::factory()->internal()->create())
        ->get(route('admin.subscriptions'))
        ->assertOk();
});

// ── Rendering ────────────────────────────────────────────────────────────────

it('renders subscriber name and email in the table', function () {
    $this->mock(ListAdminSubscriptionsService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentSubResult(['userName' => 'Maria Garcia', 'userEmail' => 'maria@example.com'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->assertSee('Maria Garcia')
        ->assertSee('maria@example.com');
});

it('renders plan name in the table', function () {
    $this->mock(ListAdminSubscriptionsService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentSubResult(['planName' => 'Pro'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->assertSee('Pro');
});

it('shows the empty state when there are no subscriptions', function () {
    $this->mock(ListAdminSubscriptionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->assertSee('No subscriptions yet');
});

it('renders multiple subscriptions', function () {
    $this->mock(ListAdminSubscriptionsService::class)
        ->shouldReceive('execute')
        ->andReturn([
            makeComponentSubResult(['id' => 1, 'userName' => 'Alice']),
            makeComponentSubResult(['id' => 2, 'userName' => 'Bob']),
        ]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->assertSee('Alice')
        ->assertSee('Bob');
});

// ── Import action ─────────────────────────────────────────────────────────────

it('dispatches a success toast when new subscriptions are imported', function () {
    $this->mock(ListAdminSubscriptionsService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeSubscriptionsService::class)->shouldReceive('execute')->andReturn(3);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->call('import')
        ->assertDispatched('toast')
        ->assertSet('importing', false);
});

it('dispatches an info toast when no new subscriptions are found', function () {
    $this->mock(ListAdminSubscriptionsService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeSubscriptionsService::class)->shouldReceive('execute')->andReturn(0);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->call('import')
        ->assertDispatched('toast')
        ->assertSet('importing', false);
});

it('dispatches an error toast when the import service throws', function () {
    $this->mock(ListAdminSubscriptionsService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeSubscriptionsService::class)
        ->shouldReceive('execute')
        ->andThrow(new RuntimeException('Connection refused'));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->call('import')
        ->assertDispatched('toast')
        ->assertSet('importing', false);
});

it('resets the importing flag to false after a successful import', function () {
    $this->mock(ListAdminSubscriptionsService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeSubscriptionsService::class)->shouldReceive('execute')->andReturn(1);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->assertSet('importing', false)
        ->call('import')
        ->assertSet('importing', false);
});

it('resets the importing flag to false even when the import fails', function () {
    $this->mock(ListAdminSubscriptionsService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeSubscriptionsService::class)
        ->shouldReceive('execute')
        ->andThrow(new RuntimeException('Timeout'));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionsComponent::class)
        ->call('import')
        ->assertSet('importing', false);
});
