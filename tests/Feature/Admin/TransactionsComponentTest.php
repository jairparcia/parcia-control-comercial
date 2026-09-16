<?php

use App\Application\Admin\ImportStripeTransactionsService;
use App\Application\Admin\ListAdminTransactionsService;
use App\Domain\Admin\Results\AdminTransactionResult;
use App\Livewire\Admin\TransactionsComponent;
use App\Models\User;
use Livewire\Livewire;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeComponentTxResult(array $overrides = []): AdminTransactionResult
{
    return new AdminTransactionResult(
        stripeId:            $overrides['stripeId']            ?? 'ch_TEST',
        amountCents:         $overrides['amountCents']         ?? 99900,
        amountRefundedCents: $overrides['amountRefundedCents'] ?? 0,
        currency:            $overrides['currency']            ?? 'MXN',
        status:              $overrides['status']              ?? 'succeeded',
        paymentMethodType:   $overrides['paymentMethodType']   ?? 'card',
        cardBrand:           $overrides['cardBrand']           ?? 'Visa',
        cardLast4:           $overrides['cardLast4']           ?? '4242',
        description:         $overrides['description']         ?? 'Pro Plan',
        customerName:        $overrides['customerName']        ?? 'Jane Doe',
        customerEmail:       $overrides['customerEmail']       ?? 'jane@example.com',
        stripeCustomerId:    $overrides['stripeCustomerId']    ?? 'cus_TEST',
        createdAt:           new \DateTimeImmutable('2025-09-20'),
        id:                  $overrides['id']                  ?? null,
        userId:              $overrides['userId']              ?? null,
    );
}

// ── Access control ────────────────────────────────────────────────────────────

it('redirects unauthenticated users to login', function () {
    $this->get(route('admin.transactions'))->assertRedirect(route('login'));
});

it('returns 403 for authenticated external users', function () {
    $this->actingAs(User::factory()->create(['role' => 'external']))
        ->get(route('admin.transactions'))
        ->assertForbidden();
});

it('renders the transactions page for internal users', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    $this->actingAs(User::factory()->internal()->create())
        ->get(route('admin.transactions'))
        ->assertOk();
});

// ── Rendering ────────────────────────────────────────────────────────────────

it('renders customer name and email in the table', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentTxResult(['customerName' => 'Maria Garcia', 'customerEmail' => 'maria@example.com'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->assertSee('Maria Garcia')
        ->assertSee('maria@example.com');
});

it('renders the formatted amount in the table', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentTxResult(['amountCents' => 99900, 'currency' => 'MXN'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->assertSee('MX$999.00');
});

it('renders the status label in the table', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentTxResult(['status' => 'succeeded'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->assertSee('Exitoso');
});

it('renders failed status label', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([makeComponentTxResult(['status' => 'failed'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->assertSee('Fallido');
});

it('shows the empty state when there are no transactions', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->assertSee('No se encontraron transacciones');
});

it('renders multiple transactions', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([
            makeComponentTxResult(['customerName' => 'Alice']),
            makeComponentTxResult(['customerName' => 'Bob']),
        ]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->assertSee('Alice')
        ->assertSee('Bob');
});

// ── Status filter ─────────────────────────────────────────────────────────────

it('defaults the statusFilter to all', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->assertSet('statusFilter', 'all');
});

it('passes the statusFilter to the service', function () {
    $mock = $this->mock(ListAdminTransactionsService::class);
    $mock->shouldReceive('execute')->with('all')->andReturn([]);
    $mock->shouldReceive('execute')->with('failed')->once()->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->set('statusFilter', 'failed');
});

it('re-renders when the statusFilter changes', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->set('statusFilter', 'succeeded')
        ->assertSet('statusFilter', 'succeeded');
});

// ── Import ────────────────────────────────────────────────────────────────────

it('dispatches a success toast when new transactions are imported', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    $this->mock(ImportStripeTransactionsService::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn(5);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->call('import')
        ->assertDispatched('toast', message: '5 transacción(es) importada(s) de Stripe.', type: 'success');
});

it('dispatches an info toast when there are no new transactions to import', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    $this->mock(ImportStripeTransactionsService::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn(0);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->call('import')
        ->assertDispatched('toast', message: 'No hay nuevas transacciones para importar.', type: 'info');
});

it('dispatches an error toast when the import throws', function () {
    $this->mock(ListAdminTransactionsService::class)
        ->shouldReceive('execute')
        ->andReturn([]);

    $this->mock(ImportStripeTransactionsService::class)
        ->shouldReceive('execute')
        ->once()
        ->andThrow(new \RuntimeException('Stripe timeout'));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionsComponent::class)
        ->call('import')
        ->assertDispatched('toast', type: 'error');
});
