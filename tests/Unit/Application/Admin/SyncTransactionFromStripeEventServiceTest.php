<?php

use App\Application\Admin\SyncTransactionFromStripeEventService;
use App\Domain\Admin\Contracts\TransactionAdminRepositoryInterface;
use App\Domain\Admin\Entities\ProviderTransactionDataDTO;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeChargePayload(array $overrides = []): array
{
    return array_merge([
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
    ], $overrides);
}

function syncService(TransactionAdminRepositoryInterface $repository): SyncTransactionFromStripeEventService
{
    return new SyncTransactionFromStripeEventService($repository);
}

// ── Tests ─────────────────────────────────────────────────────────────────────

it('calls upsert once with a DTO built from the charge payload', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::type(ProviderTransactionDataDTO::class));

    syncService($repository)->execute(makeChargePayload());
});

it('maps stripe_id correctly', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->stripeId === 'ch_SPECIFIC'
    ));

    syncService($repository)->execute(makeChargePayload(['id' => 'ch_SPECIFIC']));
});

it('maps amount_cents and currency', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->amountCents === 50000 && $dto->currency === 'USD'
    ));

    syncService($repository)->execute(makeChargePayload(['amount' => 50000, 'currency' => 'usd']));
});

it('resolves status as succeeded', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->status === 'succeeded'
    ));

    syncService($repository)->execute(makeChargePayload(['status' => 'succeeded', 'refunded' => false, 'amount_refunded' => 0]));
});

it('resolves status as refunded when refunded flag is true', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->status === 'refunded'
    ));

    syncService($repository)->execute(makeChargePayload([
        'status'          => 'succeeded',
        'refunded'        => true,
        'amount_refunded' => 99900,
    ]));
});

it('resolves status as partially_refunded when amount_refunded > 0 and refunded is false', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->status === 'partially_refunded'
    ));

    syncService($repository)->execute(makeChargePayload([
        'status'          => 'succeeded',
        'refunded'        => false,
        'amount_refunded' => 5000,
    ]));
});

it('ucfirsts the card brand', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->cardBrand === 'Mastercard'
    ));

    syncService($repository)->execute(makeChargePayload([
        'payment_method_details' => ['type' => 'card', 'card' => ['brand' => 'mastercard', 'last4' => '5555']],
    ]));
});

it('maps billing name and email', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->customerName === 'Bob' && $dto->customerEmail === 'bob@example.com'
    ));

    syncService($repository)->execute(makeChargePayload([
        'billing_details' => ['name' => 'Bob', 'email' => 'bob@example.com'],
    ]));
});

it('maps stripe_customer_id from the customer field', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->stripeCustomerId === 'cus_XYZ'
    ));

    syncService($repository)->execute(makeChargePayload(['customer' => 'cus_XYZ']));
});

it('maps amount_refunded_cents', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderTransactionDataDTO $dto) => $dto->amountRefundedCents === 25000
    ));

    syncService($repository)->execute(makeChargePayload([
        'refunded'        => false,
        'amount_refunded' => 25000,
    ]));
});
