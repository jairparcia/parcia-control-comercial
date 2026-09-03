<?php

use App\Application\Admin\ListAdminTransactionsService;
use App\Domain\Admin\Contracts\TransactionAdminRepositoryInterface;
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

// ── Tests ─────────────────────────────────────────────────────────────────────

it('delegates to the repository with the given status filter', function () {
    $results    = [makeTransactionResult(['status' => 'succeeded'])];
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('all')->with('succeeded')->once()->andReturn($results);

    $service = new ListAdminTransactionsService($repository);

    expect($service->execute('succeeded'))->toBe($results);
});

it('uses all as the default status filter', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->expects('all')->with('all')->once()->andReturn([]);

    (new ListAdminTransactionsService($repository))->execute();
});

it('returns whatever the repository returns', function () {
    $results = [
        makeTransactionResult(['status' => 'succeeded']),
        makeTransactionResult(['status' => 'failed']),
    ];

    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->allows('all')->andReturn($results);

    $result = (new ListAdminTransactionsService($repository))->execute('all');

    expect($result)->toHaveCount(2);
});

it('returns an empty array when the repository returns nothing', function () {
    $repository = Mockery::mock(TransactionAdminRepositoryInterface::class);
    $repository->allows('all')->andReturn([]);

    expect((new ListAdminTransactionsService($repository))->execute())->toBeEmpty();
});
