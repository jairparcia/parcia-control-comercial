<?php

use App\Domain\Admin\Entities\ProviderInvoiceDataDTO;
use App\Infrastructure\Repository\Admin\EloquentInvoiceAdminRepository;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;

// ── Helper: ProviderInvoiceDataDTO ────────────────────────────────────────────

function makeInvoiceDTO(array $overrides = []): ProviderInvoiceDataDTO
{
    return new ProviderInvoiceDataDTO(
        stripeId:         $overrides['stripeId']         ?? 'in_' . uniqid(),
        invoiceNumber:    $overrides['invoiceNumber']    ?? 'INV-0001',
        totalCents:       $overrides['totalCents']       ?? 14900,
        currency:         $overrides['currency']         ?? 'MXN',
        status:           $overrides['status']           ?? 'paid',
        interval:         $overrides['interval']         ?? 'month',
        intervalCount:    $overrides['intervalCount']    ?? 1,
        customerName:     $overrides['customerName']     ?? 'Jane Doe',
        customerEmail:    $overrides['customerEmail']    ?? 'jane@example.com',
        stripeCustomerId: $overrides['stripeCustomerId'] ?? 'cus_TEST',
        dueDate:          $overrides['dueDate']          ?? null,
        createdAt:        $overrides['createdAt']        ?? new \DateTimeImmutable('2025-08-01 00:00:00'),
    );
}

function repo(): EloquentInvoiceAdminRepository
{
    return new EloquentInvoiceAdminRepository();
}

// ── all() — status filter ─────────────────────────────────────────────────────

it('returns only paid invoices by default', function () {
    Invoice::factory()->paid()->create();
    Invoice::factory()->open()->create();

    $results = repo()->all('paid');

    expect(collect($results)->pluck('status')->unique()->all())->toBe(['paid']);
});

it('returns only open invoices when filter is open', function () {
    Invoice::factory()->paid()->create();
    Invoice::factory()->open()->create();

    $results = repo()->all('open');

    expect(collect($results)->pluck('status')->unique()->all())->toBe(['open']);
});

it('returns all invoices regardless of status when filter is all', function () {
    Invoice::factory()->paid()->create();
    Invoice::factory()->open()->create();
    Invoice::factory()->void()->create();

    expect(repo()->all('all'))->toHaveCount(3);
});

it('orders invoices by stripe_created_at descending', function () {
    $old   = Invoice::factory()->create(['stripe_created_at' => now()->subDays(10)]);
    $recent = Invoice::factory()->create(['stripe_created_at' => now()->subDay()]);

    $results = repo()->all('all');

    expect($results[0]->stripeId)->toBe($recent->stripe_id)
        ->and($results[1]->stripeId)->toBe($old->stripe_id);
});

it('returns an empty array when no invoices match the filter', function () {
    Invoice::factory()->paid()->create();

    expect(repo()->all('open'))->toBeEmpty();
});

// ── all() — result shape ──────────────────────────────────────────────────────

it('maps all scalar fields correctly', function () {
    Invoice::factory()->create([
        'stripe_id'              => 'in_abc123',
        'invoice_number'         => 'TEST-0001',
        'total_cents'            => 29900,
        'currency'               => 'MXN',
        'status'                 => 'paid',
        'billing_interval'       => 'month',
        'billing_interval_count' => 1,
        'customer_name'          => 'Carlos López',
        'customer_email'         => 'carlos@example.com',
        'stripe_customer_id'     => 'cus_XYZ',
    ]);

    $result = repo()->all('paid')[0];

    expect($result->stripeId)->toBe('in_abc123')
        ->and($result->invoiceNumber)->toBe('TEST-0001')
        ->and($result->totalCents)->toBe(29900)
        ->and($result->currency)->toBe('MXN')
        ->and($result->interval)->toBe('month')
        ->and($result->intervalCount)->toBe(1)
        ->and($result->customerName)->toBe('Carlos López')
        ->and($result->customerEmail)->toBe('carlos@example.com')
        ->and($result->stripeCustomerId)->toBe('cus_XYZ');
});

it('sets userId from the linked user', function () {
    $user    = User::factory()->create();
    Invoice::factory()->forUser($user)->create();

    $result = repo()->all('paid')[0];

    expect($result->userId)->toBe($user->id);
});

it('sets userId to null when the invoice has no linked user', function () {
    Invoice::factory()->create(['user_id' => null]);

    $result = repo()->all('paid')[0];

    expect($result->userId)->toBeNull();
});

it('populates stripeSubscriptionId from the user active subscription', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_LINKED']);
    Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_ACTIVE',
        'stripe_status' => 'active',
        'stripe_price'  => 'price_test',
        'quantity'      => 1,
    ]);
    Invoice::factory()->forUser($user)->create();

    $result = repo()->all('paid')[0];

    expect($result->stripeSubscriptionId)->toBe('sub_ACTIVE');
});

it('sets stripeSubscriptionId to null when user has no active subscription', function () {
    $user = User::factory()->create();
    Invoice::factory()->forUser($user)->create();

    $result = repo()->all('paid')[0];

    expect($result->stripeSubscriptionId)->toBeNull();
});

it('sets stripeSubscriptionId to null when invoice has no linked user', function () {
    Invoice::factory()->create(['user_id' => null]);

    $result = repo()->all('paid')[0];

    expect($result->stripeSubscriptionId)->toBeNull();
});

// ── insertMissing() ───────────────────────────────────────────────────────────

it('inserts new invoices and returns the count', function () {
    $count = repo()->insertMissing([
        makeInvoiceDTO(['stripeId' => 'in_001']),
        makeInvoiceDTO(['stripeId' => 'in_002']),
    ]);

    expect($count)->toBe(2)
        ->and(Invoice::count())->toBe(2);
});

it('skips invoices with a duplicate stripe_id', function () {
    Invoice::factory()->create(['stripe_id' => 'in_EXISTING']);

    $count = repo()->insertMissing([
        makeInvoiceDTO(['stripeId' => 'in_EXISTING']),
        makeInvoiceDTO(['stripeId' => 'in_NEW']),
    ]);

    expect($count)->toBe(1)
        ->and(Invoice::count())->toBe(2);
});

it('resolves user_id by stripe_customer_id when inserting', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_LINKED']);

    repo()->insertMissing([makeInvoiceDTO(['stripeCustomerId' => 'cus_LINKED'])]);

    expect(Invoice::first()->user_id)->toBe($user->id);
});

it('leaves user_id null when stripe_customer_id does not match any user', function () {
    repo()->insertMissing([makeInvoiceDTO(['stripeCustomerId' => 'cus_UNKNOWN'])]);

    expect(Invoice::first()->user_id)->toBeNull();
});

it('returns 0 when given an empty array', function () {
    expect(repo()->insertMissing([]))->toBe(0);
});

// ── upsert() ──────────────────────────────────────────────────────────────────

it('creates a new invoice when stripe_id does not exist', function () {
    repo()->upsert(makeInvoiceDTO(['stripeId' => 'in_NEW', 'status' => 'open']));

    expect(Invoice::count())->toBe(1)
        ->and(Invoice::first()->status)->toBe('open');
});

it('updates an existing invoice when stripe_id already exists', function () {
    Invoice::factory()->create(['stripe_id' => 'in_EXISTS', 'status' => 'open']);

    repo()->upsert(makeInvoiceDTO(['stripeId' => 'in_EXISTS', 'status' => 'paid']));

    expect(Invoice::count())->toBe(1)
        ->and(Invoice::first()->status)->toBe('paid');
});

it('links the user by stripe_customer_id on upsert', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_LINKED']);

    repo()->upsert(makeInvoiceDTO(['stripeCustomerId' => 'cus_LINKED']));

    expect(Invoice::first()->user_id)->toBe($user->id);
});
