<?php

use App\Application\Admin\ListAdminInvoicesService;
use App\Domain\Admin\Contracts\InvoiceAdminRepositoryInterface;
use App\Domain\Admin\Results\AdminInvoiceResult;

// ── Helper ────────────────────────────────────────────────────────────────────

function makeInvoiceResult(array $overrides = []): AdminInvoiceResult
{
    return new AdminInvoiceResult(
        stripeId:         $overrides['stripeId']         ?? 'in_TEST',
        invoiceNumber:    $overrides['invoiceNumber']    ?? 'INV-0001',
        totalCents:       $overrides['totalCents']       ?? 99900,
        currency:         $overrides['currency']         ?? 'MXN',
        status:           $overrides['status']           ?? 'paid',
        interval:         $overrides['interval']         ?? 'month',
        intervalCount:    $overrides['intervalCount']    ?? 1,
        customerName:     $overrides['customerName']     ?? 'Jane Doe',
        customerEmail:    $overrides['customerEmail']    ?? 'jane@example.com',
        stripeCustomerId: $overrides['stripeCustomerId'] ?? 'cus_TEST',
        dueDate:          $overrides['dueDate']          ?? null,
        createdAt:        $overrides['createdAt']        ?? new \DateTimeImmutable('2025-08-01'),
    );
}

// ── Delegation ────────────────────────────────────────────────────────────────

it('delegates to repository with the given status filter', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->shouldReceive('all')->with('paid')->once()->andReturn([]);

    (new ListAdminInvoicesService($repo))->execute('paid');
});

it('passes all status filter to the repository', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->shouldReceive('all')->with('all')->once()->andReturn([]);

    (new ListAdminInvoicesService($repo))->execute('all');
});

it('uses paid as the default status filter', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->shouldReceive('all')->with('paid')->once()->andReturn([]);

    (new ListAdminInvoicesService($repo))->execute();
});

// ── Return value ──────────────────────────────────────────────────────────────

it('returns the array from the repository unchanged', function () {
    $invoices = [
        makeInvoiceResult(['stripeId' => 'in_001']),
        makeInvoiceResult(['stripeId' => 'in_002']),
    ];

    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->shouldReceive('all')->andReturn($invoices);

    expect((new ListAdminInvoicesService($repo))->execute())->toBe($invoices);
});

it('returns an empty array when the repository returns nothing', function () {
    $repo = Mockery::mock(InvoiceAdminRepositoryInterface::class);
    $repo->shouldReceive('all')->andReturn([]);

    expect((new ListAdminInvoicesService($repo))->execute())->toBeEmpty();
});
