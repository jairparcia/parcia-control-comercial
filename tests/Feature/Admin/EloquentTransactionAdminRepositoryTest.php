<?php

use App\Domain\Admin\Entities\ProviderTransactionDataDTO;
use App\Infrastructure\Repository\Admin\EloquentTransactionAdminRepository;
use App\Models\Transaction;
use App\Models\User;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeTxDTO(array $overrides = []): ProviderTransactionDataDTO
{
    return new ProviderTransactionDataDTO(
        stripeId:            $overrides['stripeId']            ?? 'ch_' . uniqid(),
        amountCents:         $overrides['amountCents']         ?? 99900,
        amountRefundedCents: $overrides['amountRefundedCents'] ?? 0,
        currency:            $overrides['currency']            ?? 'MXN',
        status:              $overrides['status']              ?? 'succeeded',
        paymentMethodType:   $overrides['paymentMethodType']   ?? 'card',
        cardBrand:           $overrides['cardBrand']           ?? 'Visa',
        cardLast4:           $overrides['cardLast4']           ?? '4242',
        description:         $overrides['description']         ?? 'Subscription',
        customerName:        $overrides['customerName']        ?? 'Jane Doe',
        customerEmail:       $overrides['customerEmail']       ?? 'jane@example.com',
        stripeCustomerId:    $overrides['stripeCustomerId']    ?? null,
        createdAt:           $overrides['createdAt']           ?? new \DateTimeImmutable('2025-09-15 12:00:00'),
    );
}

function txRepo(): EloquentTransactionAdminRepository
{
    return new EloquentTransactionAdminRepository();
}

// ── all() ────────────────────────────────────────────────────────────────────

it('returns all transactions ordered by stripe_created_at desc', function () {
    Transaction::factory()->create(['stripe_created_at' => now()->subDays(3), 'stripe_id' => 'ch_old']);
    Transaction::factory()->create(['stripe_created_at' => now()->subDay(),   'stripe_id' => 'ch_new']);

    $results = txRepo()->all();

    expect($results[0]->stripeId)->toBe('ch_new');
    expect($results[1]->stripeId)->toBe('ch_old');
});

it('filters by succeeded status', function () {
    Transaction::factory()->succeeded()->create(['stripe_id' => 'ch_ok']);
    Transaction::factory()->failed()->create(['stripe_id' => 'ch_fail']);

    $results = txRepo()->all('succeeded');

    expect($results)->toHaveCount(1);
    expect($results[0]->stripeId)->toBe('ch_ok');
});

it('filters by failed status', function () {
    Transaction::factory()->succeeded()->create();
    Transaction::factory()->failed()->create(['stripe_id' => 'ch_fail']);

    $results = txRepo()->all('failed');

    expect($results)->toHaveCount(1);
    expect($results[0]->status)->toBe('failed');
});

it('filters by refunded status', function () {
    Transaction::factory()->succeeded()->create();
    Transaction::factory()->refunded()->create(['stripe_id' => 'ch_ref']);

    $results = txRepo()->all('refunded');

    expect($results)->toHaveCount(1);
    expect($results[0]->status)->toBe('refunded');
});

it('filters by partially_refunded status', function () {
    Transaction::factory()->partiallyRefunded()->create(['stripe_id' => 'ch_pr']);
    Transaction::factory()->succeeded()->create();

    $results = txRepo()->all('partially_refunded');

    expect($results)->toHaveCount(1);
    expect($results[0]->status)->toBe('partially_refunded');
});

it('returns all rows when status filter is all', function () {
    Transaction::factory()->succeeded()->create();
    Transaction::factory()->failed()->create();
    Transaction::factory()->refunded()->create();

    expect(txRepo()->all('all'))->toHaveCount(3);
});

it('maps all fields correctly', function () {
    Transaction::factory()->create([
        'stripe_id'             => 'ch_MAPPED',
        'stripe_customer_id'    => 'cus_ABC',
        'amount_cents'          => 29900,
        'amount_refunded_cents' => 5000,
        'currency'              => 'USD',
        'status'                => 'partially_refunded',
        'payment_method_type'   => 'card',
        'card_brand'            => 'Mastercard',
        'card_last4'            => '1234',
        'description'           => 'Pro Plan',
        'customer_name'         => 'Bob',
        'customer_email'        => 'bob@example.com',
        'stripe_created_at'     => '2025-06-01 10:00:00',
    ]);

    $result = txRepo()->all()[0];

    expect($result->stripeId)->toBe('ch_MAPPED')
        ->and($result->amountCents)->toBe(29900)
        ->and($result->amountRefundedCents)->toBe(5000)
        ->and($result->currency)->toBe('USD')
        ->and($result->status)->toBe('partially_refunded')
        ->and($result->paymentMethodType)->toBe('card')
        ->and($result->cardBrand)->toBe('Mastercard')
        ->and($result->cardLast4)->toBe('1234')
        ->and($result->description)->toBe('Pro Plan')
        ->and($result->customerName)->toBe('Bob')
        ->and($result->customerEmail)->toBe('bob@example.com')
        ->and($result->stripeCustomerId)->toBe('cus_ABC');
});

it('populates userId when transaction is linked to a user', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_LINKED']);
    Transaction::factory()->forUser($user)->create(['stripe_id' => 'ch_LINKED']);

    $result = txRepo()->all()[0];

    expect($result->userId)->toBe($user->id);
});

it('returns null userId when transaction has no user', function () {
    Transaction::factory()->create(['user_id' => null, 'stripe_id' => 'ch_NOUSER']);

    $result = txRepo()->all()[0];

    expect($result->userId)->toBeNull();
});

it('populates id from the database row', function () {
    $tx = Transaction::factory()->create(['stripe_id' => 'ch_WITHID']);

    $result = txRepo()->all()[0];

    expect($result->id)->toBe($tx->id);
});

// ── insertMissing() ───────────────────────────────────────────────────────────

it('inserts a new transaction and returns count 1', function () {
    $dto = makeTxDTO(['stripeId' => 'ch_NEW']);

    $count = txRepo()->insertMissing([$dto]);

    expect($count)->toBe(1);
    $this->assertDatabaseHas('transactions', ['stripe_id' => 'ch_NEW']);
});

it('skips duplicate stripe_id and returns count 0', function () {
    Transaction::factory()->create(['stripe_id' => 'ch_DUP']);

    $count = txRepo()->insertMissing([makeTxDTO(['stripeId' => 'ch_DUP'])]);

    expect($count)->toBe(0);
    $this->assertDatabaseCount('transactions', 1);
});

it('resolves user_id by stripe_customer_id on insert', function () {
    $user = User::factory()->create(['stripe_id' => 'cus_RESOLVE']);

    txRepo()->insertMissing([makeTxDTO(['stripeId' => 'ch_USR', 'stripeCustomerId' => 'cus_RESOLVE'])]);

    $this->assertDatabaseHas('transactions', [
        'stripe_id' => 'ch_USR',
        'user_id'   => $user->id,
    ]);
});

it('stores null user_id when stripe_customer_id does not match any user', function () {
    txRepo()->insertMissing([makeTxDTO(['stripeId' => 'ch_NOUSERMATCH', 'stripeCustomerId' => 'cus_UNKNOWN'])]);

    $this->assertDatabaseHas('transactions', [
        'stripe_id' => 'ch_NOUSERMATCH',
        'user_id'   => null,
    ]);
});

it('returns total count of newly inserted rows across multiple DTOs', function () {
    Transaction::factory()->create(['stripe_id' => 'ch_EXISTING']);

    $count = txRepo()->insertMissing([
        makeTxDTO(['stripeId' => 'ch_EXISTING']),
        makeTxDTO(['stripeId' => 'ch_NEW_A']),
        makeTxDTO(['stripeId' => 'ch_NEW_B']),
    ]);

    expect($count)->toBe(2);
});

// ── upsert() ──────────────────────────────────────────────────────────────────

it('creates a new row when upserted stripe_id does not exist', function () {
    $dto = makeTxDTO(['stripeId' => 'ch_UPSERT_NEW', 'status' => 'succeeded']);

    txRepo()->upsert($dto);

    $this->assertDatabaseHas('transactions', ['stripe_id' => 'ch_UPSERT_NEW', 'status' => 'succeeded']);
});

it('updates an existing row when upserted stripe_id already exists', function () {
    Transaction::factory()->create(['stripe_id' => 'ch_UPSERT_UPD', 'status' => 'succeeded']);

    txRepo()->upsert(makeTxDTO(['stripeId' => 'ch_UPSERT_UPD', 'status' => 'refunded']));

    $this->assertDatabaseHas('transactions', ['stripe_id' => 'ch_UPSERT_UPD', 'status' => 'refunded']);
    $this->assertDatabaseCount('transactions', 1);
});
