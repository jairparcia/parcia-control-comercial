<?php

use App\Application\Admin\SyncTransactionFromStripeEventService;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeChargeWebhookPayload(string $eventType, array $chargeOverrides = []): array
{
    return [
        'type' => $eventType,
        'data' => [
            'object' => array_merge([
                'id'                     => 'ch_TEST',
                'amount'                 => 99900,
                'amount_refunded'        => 0,
                'currency'               => 'mxn',
                'status'                 => 'succeeded',
                'refunded'               => false,
                'customer'               => 'cus_ABC',
                'description'            => 'Pro Plan',
                'billing_details'        => ['name' => 'Jane Doe', 'email' => 'jane@example.com'],
                'payment_method_details' => ['type' => 'card', 'card' => ['brand' => 'visa', 'last4' => '4242']],
                'created'                => 1700000000,
            ], $chargeOverrides),
        ],
    ];
}

function postStripeWebhook(array $payload): \Illuminate\Testing\TestResponse
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

// ── charge.succeeded ─────────────────────────────────────────────────────────

it('returns 200 on charge.succeeded', function () {
    $this->mock(SyncTransactionFromStripeEventService::class)
        ->shouldReceive('execute')->once();

    postStripeWebhook(makeChargeWebhookPayload('charge.succeeded'))
        ->assertStatus(200);
});

it('calls the sync service with the charge object on charge.succeeded', function () {
    $this->mock(SyncTransactionFromStripeEventService::class)
        ->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($charge) => $charge['id'] === 'ch_SUCCEEDED'));

    postStripeWebhook(makeChargeWebhookPayload('charge.succeeded', ['id' => 'ch_SUCCEEDED']));
});

// ── charge.refunded ──────────────────────────────────────────────────────────

it('returns 200 on charge.refunded', function () {
    $this->mock(SyncTransactionFromStripeEventService::class)
        ->shouldReceive('execute')->once();

    postStripeWebhook(makeChargeWebhookPayload('charge.refunded', [
        'status'          => 'succeeded',
        'refunded'        => true,
        'amount_refunded' => 99900,
    ]))->assertStatus(200);
});

it('calls the sync service with the charge object on charge.refunded', function () {
    $this->mock(SyncTransactionFromStripeEventService::class)
        ->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($charge) => $charge['id'] === 'ch_REFUNDED' && $charge['refunded'] === true));

    postStripeWebhook(makeChargeWebhookPayload('charge.refunded', [
        'id'              => 'ch_REFUNDED',
        'refunded'        => true,
        'amount_refunded' => 99900,
    ]));
});

// ── charge.updated ───────────────────────────────────────────────────────────

it('returns 200 on charge.updated', function () {
    $this->mock(SyncTransactionFromStripeEventService::class)
        ->shouldReceive('execute')->once();

    postStripeWebhook(makeChargeWebhookPayload('charge.updated'))
        ->assertStatus(200);
});

it('calls the sync service with the charge object on charge.updated', function () {
    $this->mock(SyncTransactionFromStripeEventService::class)
        ->shouldReceive('execute')
        ->once()
        ->with(Mockery::on(fn ($charge) => $charge['id'] === 'ch_UPDATED'));

    postStripeWebhook(makeChargeWebhookPayload('charge.updated', ['id' => 'ch_UPDATED']));
});

// ── Unhandled event ───────────────────────────────────────────────────────────

it('does not call the sync service for unrelated events', function () {
    $this->mock(SyncTransactionFromStripeEventService::class)
        ->shouldNotReceive('execute');

    postStripeWebhook(['type' => 'customer.created', 'data' => ['object' => ['id' => 'cus_X']]]);
});
