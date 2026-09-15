<?php

use App\Application\Admin\ListAdminTransactionsService;
use App\Domain\Admin\Contracts\TransactionProviderGatewayInterface;
use App\Domain\Admin\Results\AdminTransactionResult;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeTransactionResult(array $overrides = []): AdminTransactionResult
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
        description:         $overrides['description']         ?? 'Subscription',
        customerName:        $overrides['customerName']        ?? 'Jane Doe',
        customerEmail:       $overrides['customerEmail']       ?? 'jane@example.com',
        stripeCustomerId:    $overrides['stripeCustomerId']    ?? 'cus_TEST',
        createdAt:           $overrides['createdAt']           ?? new \DateTimeImmutable('2025-09-20'),
    );
}

function makeTransactionService(array $transactions): ListAdminTransactionsService
{
    $gateway = Mockery::mock(TransactionProviderGatewayInterface::class);
    $gateway->allows('listRecent')->andReturn($transactions);

    return new ListAdminTransactionsService($gateway);
}

// ── Tests ─────────────────────────────────────────────────────────────────────

it('returns all transactions when filter is all', function () {
    $transactions = [
        makeTransactionResult(['status' => 'succeeded']),
        makeTransactionResult(['status' => 'failed']),
    ];

    $gateway = Mockery::mock(TransactionProviderGatewayInterface::class);
    $gateway->expects('listRecent')->once()->andReturn($transactions);

    $result = (new ListAdminTransactionsService($gateway))->execute('all');

    expect($result)->toHaveCount(2);
});

it('returns an empty array when there are no transactions', function () {
    $gateway = Mockery::mock(TransactionProviderGatewayInterface::class);
    $gateway->expects('listRecent')->once()->andReturn([]);

    expect((new ListAdminTransactionsService($gateway))->execute())->toBeEmpty();
});

it('filters by succeeded status', function () {
    $transactions = [
        makeTransactionResult(['status' => 'succeeded']),
        makeTransactionResult(['status' => 'failed']),
        makeTransactionResult(['status' => 'succeeded']),
    ];

    $result = makeTransactionService($transactions)->execute('succeeded');

    expect($result)->toHaveCount(2);
    expect($result[0]->status)->toBe('succeeded');
    expect($result[1]->status)->toBe('succeeded');
});

it('filters by failed status', function () {
    $transactions = [
        makeTransactionResult(['status' => 'succeeded']),
        makeTransactionResult(['status' => 'failed']),
    ];

    $result = makeTransactionService($transactions)->execute('failed');

    expect($result)->toHaveCount(1);
    expect($result[0]->status)->toBe('failed');
});

it('filters by refunded status', function () {
    $transactions = [
        makeTransactionResult(['status' => 'succeeded']),
        makeTransactionResult(['status' => 'refunded']),
    ];

    $result = makeTransactionService($transactions)->execute('refunded');

    expect($result)->toHaveCount(1);
    expect($result[0]->status)->toBe('refunded');
});

it('filters by partially_refunded status', function () {
    $transactions = [
        makeTransactionResult(['status' => 'succeeded']),
        makeTransactionResult(['status' => 'partially_refunded']),
        makeTransactionResult(['status' => 'partially_refunded']),
    ];

    $result = makeTransactionService($transactions)->execute('partially_refunded');

    expect($result)->toHaveCount(2);
});

it('returns empty array when no transactions match the filter', function () {
    $transactions = [makeTransactionResult(['status' => 'succeeded'])];

    $result = makeTransactionService($transactions)->execute('pending');

    expect($result)->toBeEmpty();
});

it('re-indexes filtered results as a plain array', function () {
    $transactions = [
        makeTransactionResult(['status' => 'failed']),
        makeTransactionResult(['status' => 'succeeded']),
        makeTransactionResult(['status' => 'failed']),
    ];

    $result = makeTransactionService($transactions)->execute('succeeded');

    expect($result)->toHaveCount(1);
    expect(array_keys($result))->toBe([0]);
});
