<?php

use App\Application\Admin\ImportStripeCustomersService;
use App\Application\Admin\ListCustomersService;
use App\Domain\Admin\Results\AdminCustomerResult;
use App\Livewire\Admin\CustomersComponent;
use App\Models\User;
use Livewire\Livewire;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeComponentCustomerResult(array $overrides = []): AdminCustomerResult
{
    return new AdminCustomerResult(
        id:          $overrides['id']          ?? 1,
        name:        $overrides['name']        ?? 'Jane Doe',
        email:       $overrides['email']       ?? 'jane@example.com',
        description: $overrides['description'] ?? null,
        country:     $overrides['country']     ?? null,
        createdAt:   $overrides['createdAt']   ?? new \DateTimeImmutable('2025-01-15'),
    );
}

// ── Access control ────────────────────────────────────────────────────────────

it('redirects unauthenticated users to login', function () {
    $this->get(route('admin.customers'))->assertRedirect(route('login'));
});

it('returns 403 for authenticated external users', function () {
    $this->actingAs(User::factory()->create(['role' => 'external']))
        ->get(route('admin.customers'))
        ->assertForbidden();
});

it('renders the customers page for internal users', function () {
    $this->mock(ListCustomersService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    $this->actingAs(User::factory()->internal()->create())
        ->get(route('admin.customers'))
        ->assertOk();
});

// ── Rendering ─────────────────────────────────────────────────────────────────

it('renders customer name and email in the table', function () {
    $this->mock(ListCustomersService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentCustomerResult(['name' => 'Maria Garcia', 'email' => 'maria@example.com'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->assertSee('Maria Garcia')
        ->assertSee('maria@example.com');
});

it('renders description when present', function () {
    $this->mock(ListCustomersService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentCustomerResult(['description' => 'Enterprise account'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->assertSee('Enterprise account');
});

it('renders country when present', function () {
    $this->mock(ListCustomersService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentCustomerResult(['country' => 'MX'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->assertSee('MX');
});

it('shows the empty state when there are no customers', function () {
    $this->mock(ListCustomersService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->assertSee('No customers yet');
});

it('renders multiple customers', function () {
    $this->mock(ListCustomersService::class)
        ->shouldReceive('execute')
        ->andReturn([
            makeComponentCustomerResult(['id' => 1, 'name' => 'Alice']),
            makeComponentCustomerResult(['id' => 2, 'name' => 'Bob']),
        ]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->assertSee('Alice')
        ->assertSee('Bob');
});

// ── Import action ─────────────────────────────────────────────────────────────

it('dispatches a success toast when new customers are imported', function () {
    $this->mock(ListCustomersService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeCustomersService::class)->shouldReceive('execute')->andReturn(3);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->call('import')
        ->assertDispatched('toast')
        ->assertSet('importing', false);
});

it('dispatches an info toast when no new customers are found', function () {
    $this->mock(ListCustomersService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeCustomersService::class)->shouldReceive('execute')->andReturn(0);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->call('import')
        ->assertDispatched('toast')
        ->assertSet('importing', false);
});

it('dispatches an error toast when the import service throws', function () {
    $this->mock(ListCustomersService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeCustomersService::class)
        ->shouldReceive('execute')
        ->andThrow(new RuntimeException('Connection refused'));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->call('import')
        ->assertDispatched('toast')
        ->assertSet('importing', false);
});

it('resets the importing flag to false after a successful import', function () {
    $this->mock(ListCustomersService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeCustomersService::class)->shouldReceive('execute')->andReturn(1);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->assertSet('importing', false)
        ->call('import')
        ->assertSet('importing', false);
});

it('resets the importing flag to false even when the import fails', function () {
    $this->mock(ListCustomersService::class)->shouldReceive('execute')->andReturn([]);
    $this->mock(ImportStripeCustomersService::class)
        ->shouldReceive('execute')
        ->andThrow(new RuntimeException('Timeout'));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(CustomersComponent::class)
        ->call('import')
        ->assertSet('importing', false);
});
