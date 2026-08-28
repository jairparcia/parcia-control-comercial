<?php

use App\Application\Admin\CancelSubscriptionService;
use App\Application\Admin\GetCancellationInfoService;
use App\Application\Admin\GetSubscriptionDetailService;
use App\Domain\Admin\Results\SubscriptionCancellationInfoResult;
use App\Domain\Admin\Results\SubscriptionDetailResult;
use App\Livewire\Admin\SubscriptionDetailPanel;
use App\Models\User;
use Livewire\Livewire;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makePanelSubDetail(array $overrides = []): SubscriptionDetailResult
{
    return new SubscriptionDetailResult(
        stripeId:         $overrides['stripeId']        ?? 'sub_PANEL',
        stripeCustomerId: $overrides['stripeCustomerId'] ?? 'cus_PANEL',
        userId:           $overrides['userId']          ?? 1,
        userName:         $overrides['userName']        ?? 'Jane Doe',
        userEmail:        $overrides['userEmail']       ?? 'jane@example.com',
        status:           $overrides['status']          ?? 'active',
        planName:         $overrides['planName']        ?? 'Pro',
        interval:         $overrides['interval']        ?? 'month',
        unitAmountCents:  $overrides['unitAmountCents'] ?? 99900,
        currency:         $overrides['currency']        ?? 'MXN',
        subscribedAt:     new \DateTimeImmutable('2025-01-15'),
        upcomingInvoice:  $overrides['upcomingInvoice'] ?? null,
        invoices:         $overrides['invoices']        ?? [],
    );
}

function makePanelCancellationInfo(): SubscriptionCancellationInfoResult
{
    return new SubscriptionCancellationInfoResult(
        stripeSubscriptionId: 'sub_PANEL',
        periodEnd:            new \DateTimeImmutable('2025-02-15'),
        lastPaymentAmount:    99900,
        lastPaymentCurrency:  'MXN',
        proratedAmount:       49950,
        proratedDays:         15,
    );
}

// ── Panel open/close ─────────────────────────────────────────────────────────

it('opens the panel and loads subscription data', function () {
    $this->mock(GetSubscriptionDetailService::class)
        ->shouldReceive('execute')
        ->with('sub_PANEL')
        ->andReturn(makePanelSubDetail(['userName' => 'Jane Doe', 'status' => 'active']));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionDetailPanel::class)
        ->call('openPanel', 'sub_PANEL')
        ->assertSet('panelOpen', true)
        ->assertSet('stripeId', 'sub_PANEL')
        ->assertSet('userName', 'Jane Doe')
        ->assertSet('statusLabel', 'Activa');
});

it('sets panelOpen to false on close', function () {
    $this->mock(GetSubscriptionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelSubDetail());

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionDetailPanel::class)
        ->call('openPanel', 'sub_PANEL')
        ->assertSet('panelOpen', true)
        ->call('close')
        ->assertSet('panelOpen', false);
});

it('dispatches an error toast when the subscription is not found', function () {
    $this->mock(GetSubscriptionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(null);

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionDetailPanel::class)
        ->call('openPanel', 'sub_MISSING')
        ->assertSet('panelOpen', false)
        ->assertDispatched('toast');
});

// ── Cancel modal ──────────────────────────────────────────────────────────────

it('opens the cancel modal and loads cancellation info', function () {
    $this->mock(GetSubscriptionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelSubDetail());

    $this->mock(GetCancellationInfoService::class)
        ->shouldReceive('execute')
        ->with('sub_PANEL')
        ->andReturn(makePanelCancellationInfo());

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionDetailPanel::class)
        ->call('openPanel', 'sub_PANEL')
        ->call('openCancelModal')
        ->assertSet('cancelModalOpen', true)
        ->assertSet('cancelTiming', 'immediately')
        ->assertSet('cancelRefundType', 'none');
});

it('closes the cancel modal without canceling', function () {
    $this->mock(GetSubscriptionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelSubDetail());

    $this->mock(GetCancellationInfoService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelCancellationInfo());

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionDetailPanel::class)
        ->call('openPanel', 'sub_PANEL')
        ->call('openCancelModal')
        ->call('closeCancelModal')
        ->assertSet('cancelModalOpen', false);
});

it('confirms immediate cancellation and closes the panel', function () {
    $this->mock(GetSubscriptionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelSubDetail());

    $this->mock(GetCancellationInfoService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelCancellationInfo());

    $this->mock(CancelSubscriptionService::class)
        ->shouldReceive('execute')
        ->with('sub_PANEL', true, 'none')
        ->once();

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionDetailPanel::class)
        ->call('openPanel', 'sub_PANEL')
        ->call('openCancelModal')
        ->set('cancelTiming', 'immediately')
        ->set('cancelRefundType', 'none')
        ->call('confirmCancel')
        ->assertSet('cancelModalOpen', false)
        ->assertSet('panelOpen', false)
        ->assertDispatched('subscription-updated')
        ->assertDispatched('toast');
});

it('dispatches an error toast when cancellation fails', function () {
    $this->mock(GetSubscriptionDetailService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelSubDetail());

    $this->mock(GetCancellationInfoService::class)
        ->shouldReceive('execute')
        ->andReturn(makePanelCancellationInfo());

    $this->mock(CancelSubscriptionService::class)
        ->shouldReceive('execute')
        ->andThrow(new RuntimeException('Stripe error'));

    Livewire::actingAs(User::factory()->internal()->create())
        ->test(SubscriptionDetailPanel::class)
        ->call('openPanel', 'sub_PANEL')
        ->call('openCancelModal')
        ->call('confirmCancel')
        ->assertSet('canceling', false)
        ->assertDispatched('toast');
});
