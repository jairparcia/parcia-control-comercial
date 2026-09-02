<?php

use App\Application\Admin\ImportStripeInvoicesService;
use App\Application\Admin\ListAdminInvoicesService;
use App\Domain\Admin\Results\AdminInvoiceResult;
use App\Livewire\Admin\InvoicesComponent;
use App\Models\User;
use Livewire\Livewire;

// ── Helper ────────────────────────────────────────────────────────────────────

function makeInvoiceComponentResult(array $overrides = []): AdminInvoiceResult
{
    return new AdminInvoiceResult(
        stripeId:             $overrides['stripeId']             ?? 'in_TEST',
        invoiceNumber:        $overrides['invoiceNumber']        ?? 'INV-0001',
        totalCents:           $overrides['totalCents']           ?? 99900,
        currency:             $overrides['currency']             ?? 'MXN',
        status:               $overrides['status']               ?? 'paid',
        interval:             $overrides['interval']             ?? 'month',
        intervalCount:        $overrides['intervalCount']        ?? 1,
        customerName:         $overrides['customerName']         ?? 'Jane Doe',
        customerEmail:        $overrides['customerEmail']        ?? 'jane@example.com',
        stripeCustomerId:     $overrides['stripeCustomerId']     ?? 'cus_TEST',
        dueDate:              $overrides['dueDate']              ?? null,
        createdAt:            new \DateTimeImmutable('2025-08-01'),
        id:                   $overrides['id']                   ?? null,
        userId:               $overrides['userId']               ?? null,
        stripeSubscriptionId: $overrides['stripeSubscriptionId'] ?? null,
    );
}

// ── Access control ────────────────────────────────────────────────────────────

it('redirects unauthenticated users to login', function () {
    $this->get(route('admin.invoices'))->assertRedirect(route('login'));
});

it('returns 403 for authenticated external users', function () {
    $this->actingAs(User::factory()->create(['role' => 'external']))
        ->get(route('admin.invoices'))
        ->assertForbidden();
});

it('renders the invoices page for internal users', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->withAnyArgs()
        ->andReturn([]);

    $this->actingAs(User::factory()->internal()->create())
        ->get(route('admin.invoices'))
        ->assertOk();
});

// ── Rendering ────────────────────────────────────────────────────────────────

it('renders customer name and email in the table', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['customerName' => 'Maria Garcia', 'customerEmail' => 'maria@example.com'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('Maria Garcia')
        ->assertSee('maria@example.com');
});

it('renders the formatted total in the table', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['totalCents' => 99900, 'currency' => 'MXN'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('MX$999.00');
});

it('renders the status label in the table', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['status' => 'paid'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('Pagada');
});

it('renders the frequency in the table', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['interval' => 'month', 'intervalCount' => 1])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('Mensual');
});

it('renders the invoice number in the table', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['invoiceNumber' => 'INV-9999'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('INV-9999');
});

it('shows the empty state when there are no invoices', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->withAnyArgs()
        ->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('No invoices found');
});

it('renders multiple invoices', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([
            makeInvoiceComponentResult(['customerName' => 'Alice']),
            makeInvoiceComponentResult(['customerName' => 'Bob']),
        ]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('Alice')
        ->assertSee('Bob');
});

// ── Status filter ─────────────────────────────────────────────────────────────

it('defaults the statusFilter to paid', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->withAnyArgs()
        ->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSet('statusFilter', 'paid');
});

it('passes the statusFilter to the service', function () {
    $mock = $this->mock(ListAdminInvoicesService::class);
    $mock->shouldReceive('execute')->with('paid')->andReturn([]);
    $mock->shouldReceive('execute')->with('open')->once()->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->set('statusFilter', 'open');
});

// ── Actions dropdown ──────────────────────────────────────────────────────────

it('renders the Ver cliente button when userId is set', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['userId' => 7])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('Ver cliente');
});

it('does not render the Ver cliente button when userId is null', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['userId' => null])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertDontSee('Ver cliente');
});

it('renders the Ver suscripcion button when stripeSubscriptionId is set', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['stripeSubscriptionId' => 'sub_ABC'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('Ver suscripción');
});

it('does not render the Ver suscripcion button when stripeSubscriptionId is null', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['stripeSubscriptionId' => null])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertDontSee('Ver suscripción');
});

it('renders the no-actions message when both userId and stripeSubscriptionId are null', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['userId' => null, 'stripeSubscriptionId' => null])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('No hay acciones disponibles');
});

it('renders both action buttons when userId and stripeSubscriptionId are both set', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->andReturn([makeInvoiceComponentResult(['userId' => 5, 'stripeSubscriptionId' => 'sub_XYZ'])]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->assertSee('Ver cliente')
        ->assertSee('Ver suscripción');
});

// ── Import button ─────────────────────────────────────────────────────────────

it('dispatches a success toast when import finds new invoices', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->withAnyArgs()
        ->andReturn([]);

    $this->mock(ImportStripeInvoicesService::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn(3);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->call('import')
        ->assertDispatched('toast', message: '3 invoice(s) imported from Stripe.', type: 'success');
});

it('dispatches an info toast when import finds no new invoices', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->withAnyArgs()
        ->andReturn([]);

    $this->mock(ImportStripeInvoicesService::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn(0);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->call('import')
        ->assertDispatched('toast', message: 'No new invoices to import.', type: 'info');
});

it('re-renders when the statusFilter changes', function () {
    $this->mock(ListAdminInvoicesService::class)
        ->shouldReceive('execute')
        ->withAnyArgs()
        ->andReturn([]);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(InvoicesComponent::class)
        ->set('statusFilter', 'void')
        ->assertSet('statusFilter', 'void');
});
