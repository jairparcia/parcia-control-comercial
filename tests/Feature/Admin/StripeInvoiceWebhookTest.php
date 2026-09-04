<?php

use App\Application\Admin\SyncInvoiceFromStripeEventService;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeInvoiceWebhookPayload(string $eventType, array $invoiceOverrides = []): array
{
    return [
        'type' => $eventType,
        'data' => [
            'object' => array_merge([
                'id'             => 'in_TEST',
                'number'         => 'INV-0001',
                'total'          => 99900,
                'currency'       => 'mxn',
                'status'         => 'paid',
                'customer'       => 'cus_ABC',
                'customer_name'  => 'Jane Doe',
                'customer_email' => 'jane@example.com',
                'due_date'       => null,
                'created'        => 1700000000,
                'lines'          => ['data' => []],
            ], $invoiceOverrides),
        ],
    ];
}

function postInvoiceWebhook(array $payload): \Illuminate\Testing\TestResponse
{
    $secret    = 'whsec_test_secret';
    config(['cashier.webhook_secret' => $secret]);

    $json      = json_encode($payload);
    $timestamp = time();
    $sig       = hash_hmac('sha256', "{$timestamp}.{$json}", $secret);
    $header    = "t={$timestamp},v1={$sig}";

    return test()->call('POST', '/stripe/webhook', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => $header,
        'CONTENT_TYPE'          => 'application/json',
    ], $json);
}

// ── invoice.paid ──────────────────────────────────────────────────────────────

it('calls the sync service on invoice.paid', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($inv) => $inv['id'] === 'in_PAID'));

    postInvoiceWebhook(makeInvoiceWebhookPayload('invoice.paid', ['id' => 'in_PAID']));
});

// ── invoice.payment_failed ────────────────────────────────────────────────────

it('calls the sync service on invoice.payment_failed', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($inv) => $inv['id'] === 'in_FAILED'));

    postInvoiceWebhook(makeInvoiceWebhookPayload('invoice.payment_failed', [
        'id'     => 'in_FAILED',
        'status' => 'open',
    ]));
});

// ── invoice.finalized ─────────────────────────────────────────────────────────

it('returns 200 on invoice.finalized', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldReceive('execute')->once();

    postInvoiceWebhook(makeInvoiceWebhookPayload('invoice.finalized', ['status' => 'open']))
        ->assertStatus(200);
});

it('calls the sync service on invoice.finalized', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($inv) => $inv['id'] === 'in_FINALIZED'));

    postInvoiceWebhook(makeInvoiceWebhookPayload('invoice.finalized', ['id' => 'in_FINALIZED', 'status' => 'open']));
});

// ── invoice.updated ───────────────────────────────────────────────────────────

it('returns 200 on invoice.updated', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldReceive('execute')->once();

    postInvoiceWebhook(makeInvoiceWebhookPayload('invoice.updated'))
        ->assertStatus(200);
});

it('calls the sync service on invoice.updated', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($inv) => $inv['id'] === 'in_UPDATED'));

    postInvoiceWebhook(makeInvoiceWebhookPayload('invoice.updated', ['id' => 'in_UPDATED']));
});

// ── invoice.voided ────────────────────────────────────────────────────────────

it('returns 200 on invoice.voided', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldReceive('execute')->once();

    postInvoiceWebhook(makeInvoiceWebhookPayload('invoice.voided', ['status' => 'void']))
        ->assertStatus(200);
});

it('calls the sync service on invoice.voided', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($inv) => $inv['id'] === 'in_VOIDED'));

    postInvoiceWebhook(makeInvoiceWebhookPayload('invoice.voided', ['id' => 'in_VOIDED', 'status' => 'void']));
});

// ── Unrelated events ──────────────────────────────────────────────────────────

it('does not call the sync service for unrelated events', function () {
    $this->mock(SyncInvoiceFromStripeEventService::class)
        ->shouldNotReceive('execute');

    postInvoiceWebhook(['type' => 'customer.created', 'data' => ['object' => ['id' => 'cus_X']]]);
});
