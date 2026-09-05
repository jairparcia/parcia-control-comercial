<?php

use App\Application\Admin\GetTransactionDetailService;
use App\Domain\Admin\Results\TransactionDetailResult;
use App\Domain\Admin\Results\TransactionEventResult;
use App\Domain\Admin\Results\TransactionFeeDetailResult;
use App\Livewire\Admin\TransactionDetailPanel;
use App\Models\User;
use Livewire\Livewire;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makePanelTxDetail(array $overrides = []): TransactionDetailResult
{
    return new TransactionDetailResult(
        stripeId:        $overrides['stripeId']        ?? 'ch_PANEL',
        amountCents:     $overrides['amountCents']     ?? 99900,
        currency:        $overrides['currency']        ?? 'MXN',
        status:          $overrides['status']          ?? 'succeeded',
        customerName:    $overrides['customerName']    ?? 'Jane Doe',
        customerEmail:   $overrides['customerEmail']   ?? 'jane@example.com',
        stripeFeesCents: $overrides['stripeFeesCents'] ?? 1061,
        netAmountCents:  $overrides['netAmountCents']  ?? 98839,
        paymentMethodId: $overrides['paymentMethodId'] ?? 'pm_PANEL',
        cardLast4:       $overrides['cardLast4']       ?? '4242',
        cardFingerprint: $overrides['cardFingerprint'] ?? 'FINGERPRINT',
        cardExpMonth:    $overrides['cardExpMonth']    ?? '12',
        cardExpYear:     $overrides['cardExpYear']     ?? '2034',
        cardFunding:     $overrides['cardFunding']     ?? 'credit',
        cardBrand:       $overrides['cardBrand']       ?? 'Visa',
        cardIssuer:      $overrides['cardIssuer']      ?? null,
        cardCountry:     $overrides['cardCountry']     ?? 'US',
        cvcCheck:        $overrides['cvcCheck']        ?? 'pass',
        billingName:     $overrides['billingName']     ?? 'Jane Doe',
        billingEmail:    $overrides['billingEmail']    ?? 'jane@example.com',
        billingCountry:  $overrides['billingCountry']  ?? 'MX',
        subscriptionId:  $overrides['subscriptionId']  ?? 'sub_PANEL',
        planName:        $overrides['planName']        ?? 'Starter',
        priceId:         $overrides['priceId']         ?? 'price_PANEL',
        invoiceNumber:   $overrides['invoiceNumber']   ?? 'INV-0001',
        paymentIntentId: $overrides['paymentIntentId'] ?? 'pi_PANEL',
        events:          $overrides['events']          ?? [
            new TransactionEventResult('Pago efectuado correctamente', new \DateTimeImmutable('2025-08-20 09:16:00')),
            new TransactionEventResult('Pago iniciado', new \DateTimeImmutable('2025-08-20 09:16:00')),
        ],
        createdAt:  new \DateTimeImmutable('2025-08-20'),
        feeDetails: $overrides['feeDetails'] ?? [],
    );
}

// ── Initial state ─────────────────────────────────────────────────────────────

it('starts with the panel closed', function () {
    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->assertSet('panelOpen', false);
});

// ── openPanel ─────────────────────────────────────────────────────────────────

it('opens the panel and loads data when openPanel is called', function () {
    $this->mock(GetTransactionDetailService::class)
        ->shouldReceive('execute')
        ->with('ch_PANEL')
        ->once()
        ->andReturn(makePanelTxDetail());

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->call('openPanel', 'ch_PANEL')
        ->assertSet('panelOpen', true)
        ->assertSet('stripeId', 'ch_PANEL')
        ->assertSet('formattedAmount', 'MX$999.00')
        ->assertSet('statusLabel', 'Exitoso');
});

it('dispatches an error toast and keeps the panel closed when the transaction is not found', function () {
    $this->mock(GetTransactionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(null);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->call('openPanel', 'ch_MISSING')
        ->assertSet('panelOpen', false)
        ->assertDispatched('toast');
});

// ── close ─────────────────────────────────────────────────────────────────────

it('closes the panel and resets chargeId when close is called', function () {
    $this->mock(GetTransactionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelTxDetail());

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->call('openPanel', 'ch_PANEL')
        ->call('close')
        ->assertSet('panelOpen', false)
        ->assertSet('chargeId', '');
});

// ── Data fields ───────────────────────────────────────────────────────────────

it('loads the customer name into the panel', function () {
    $this->mock(GetTransactionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelTxDetail(['customerName' => 'Arthur Morgan']));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->call('openPanel', 'ch_PANEL')
        ->assertSet('customerName', 'Arthur Morgan');
});

it('loads the formatted fees into the panel', function () {
    $this->mock(GetTransactionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelTxDetail(['stripeFeesCents' => 1061, 'currency' => 'MXN']));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->call('openPanel', 'ch_PANEL')
        ->assertSet('formattedFees', 'MX$10.61');
});

it('loads the subscription ID into the panel', function () {
    $this->mock(GetTransactionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelTxDetail(['subscriptionId' => 'sub_XYZ']));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->call('openPanel', 'ch_PANEL')
        ->assertSet('subscriptionId', 'sub_XYZ');
});

it('loads the payment intent ID into the panel', function () {
    $this->mock(GetTransactionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelTxDetail(['paymentIntentId' => 'pi_XYZ']));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->call('openPanel', 'ch_PANEL')
        ->assertSet('paymentIntentId', 'pi_XYZ');
});

it('loads timeline events into the panel', function () {
    $events = [
        new TransactionEventResult('Pago efectuado correctamente', new \DateTimeImmutable('2025-08-20 09:16:00')),
        new TransactionEventResult('Pago iniciado', new \DateTimeImmutable('2025-08-20 09:16:00')),
    ];

    $this->mock(GetTransactionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelTxDetail(['events' => $events]));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(TransactionDetailPanel::class)
        ->call('openPanel', 'ch_PANEL')
        ->assertSet('events', fn ($value) => count($value) === 2);
});
