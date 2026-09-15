<?php

use App\Application\Admin\SyncInvoiceFromStripeEventService;
use App\Domain\Admin\Contracts\InvoiceAdminRepositoryInterface;
use App\Domain\Admin\Entities\ProviderInvoiceDataDTO;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeInvoicePayload(array $overrides = []): array
{
    return array_merge([
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
        'lines'          => ['data' => [
            ['price' => ['recurring' => ['interval' => 'month', 'interval_count' => 1]]],
        ]],
    ], $overrides);
}

function makeSyncInvoiceService(InvoiceAdminRepositoryInterface $repository): SyncInvoiceFromStripeEventService
{
    return new SyncInvoiceFromStripeEventService($repository);
}

// ── Tests ─────────────────────────────────────────────────────────────────────

it('calls upsert once with a DTO built from the invoice payload', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::type(ProviderInvoiceDataDTO::class));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload());
});

it('maps stripe_id correctly', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->stripeId === 'in_SPECIFIC'
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload(['id' => 'in_SPECIFIC']));
});

it('maps total_cents and uppercases currency', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->totalCents === 50000 && $dto->currency === 'USD'
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload(['total' => 50000, 'currency' => 'usd']));
});

it('maps status', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->status === 'open'
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload(['status' => 'open']));
});

it('maps customer name and email', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->customerName === 'Bob' && $dto->customerEmail === 'bob@example.com'
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload([
        'customer_name'  => 'Bob',
        'customer_email' => 'bob@example.com',
    ]));
});

it('maps stripe_customer_id from the customer field', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->stripeCustomerId === 'cus_XYZ'
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload(['customer' => 'cus_XYZ']));
});

it('maps invoice_number', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->invoiceNumber === 'INV-9999'
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload(['number' => 'INV-9999']));
});

it('resolves interval from price.recurring', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->interval === 'year' && $dto->intervalCount === 1
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload([
        'lines' => ['data' => [
            ['price' => ['recurring' => ['interval' => 'year', 'interval_count' => 1]]],
        ]],
    ]));
});

it('resolves interval from deprecated plan field when price is absent', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->interval === 'month' && $dto->intervalCount === 1
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload([
        'lines' => ['data' => [
            ['price' => null, 'plan' => ['interval' => 'month', 'interval_count' => 1]],
        ]],
    ]));
});

it('sets interval to null when no line items exist', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->interval === null && $dto->intervalCount === 1
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload(['lines' => ['data' => []]]));
});

it('maps due_date as a DateTimeImmutable when present', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->dueDate instanceof \DateTimeImmutable
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload(['due_date' => 1700000000]));
});

it('sets due_date to null when not present', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->expects('upsert')->once()->with(Mockery::on(
        fn (ProviderInvoiceDataDTO $dto) => $dto->dueDate === null
    ));

    makeSyncInvoiceService($repo)->execute(makeInvoicePayload(['due_date' => null]));
});
