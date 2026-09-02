<?php

use App\Domain\Admin\Results\AdminInvoiceResult;
use App\Http\Presenters\Admin\AdminInvoicePresenter;
use App\Http\Presenters\Admin\AdminInvoiceViewModel;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeInvResult(array $overrides = []): AdminInvoiceResult
{
    return new AdminInvoiceResult(
        stripeId:         $overrides['stripeId']         ?? 'in_TEST',
        invoiceNumber:    array_key_exists('invoiceNumber', $overrides) ? $overrides['invoiceNumber'] : 'INV-0001',
        totalCents:       $overrides['totalCents']       ?? 99900,
        currency:         $overrides['currency']         ?? 'MXN',
        status:           $overrides['status']           ?? 'paid',
        interval:         array_key_exists('interval', $overrides)      ? $overrides['interval']      : 'month',
        intervalCount:    $overrides['intervalCount']    ?? 1,
        customerName:     array_key_exists('customerName', $overrides)  ? $overrides['customerName']  : 'Jane Doe',
        customerEmail:    array_key_exists('customerEmail', $overrides) ? $overrides['customerEmail'] : 'jane@example.com',
        stripeCustomerId: $overrides['stripeCustomerId'] ?? 'cus_TEST',
        dueDate:              array_key_exists('dueDate', $overrides)              ? $overrides['dueDate']              : null,
        createdAt:            $overrides['createdAt']            ?? new \DateTimeImmutable('2025-08-01'),
        id:                   $overrides['id']                   ?? null,
        userId:               array_key_exists('userId', $overrides)              ? $overrides['userId']               : null,
        stripeSubscriptionId: array_key_exists('stripeSubscriptionId', $overrides) ? $overrides['stripeSubscriptionId'] : null,
    );
}

function presentInv(array $overrides = []): AdminInvoiceViewModel
{
    return (new AdminInvoicePresenter())->presentAll([makeInvResult($overrides)])[0];
}

// ── ViewModel type ────────────────────────────────────────────────────────────

it('returns AdminInvoiceViewModel instances', function () {
    expect(presentInv())->toBeInstanceOf(AdminInvoiceViewModel::class);
});

it('returns one ViewModel per invoice', function () {
    $result = (new AdminInvoicePresenter())->presentAll([
        makeInvResult(['stripeId' => 'in_001']),
        makeInvResult(['stripeId' => 'in_002']),
    ]);

    expect($result)->toHaveCount(2);
});

// ── Passthrough fields ────────────────────────────────────────────────────────

it('passes stripeId through', function () {
    expect(presentInv(['stripeId' => 'in_XYZ'])->stripeId)->toBe('in_XYZ');
});

it('passes status through', function () {
    expect(presentInv(['status' => 'open'])->status)->toBe('open');
});

it('shows dash when invoice number is null', function () {
    expect(presentInv(['invoiceNumber' => null])->invoiceNumber)->toBe('—');
});

// ── Amount formatting ─────────────────────────────────────────────────────────

it('formats MXN amount with MX$ prefix and two decimals', function () {
    expect(presentInv(['totalCents' => 99900, 'currency' => 'MXN'])->formattedTotal)->toBe('MX$999.00');
});

it('formats USD amount with US$ prefix', function () {
    expect(presentInv(['totalCents' => 4500, 'currency' => 'USD'])->formattedTotal)->toBe('US$45.00');
});

it('formats amounts greater than 999 with comma separator', function () {
    expect(presentInv(['totalCents' => 115900, 'currency' => 'MXN'])->formattedTotal)->toBe('MX$1,159.00');
});

// ── Status labels ─────────────────────────────────────────────────────────────

it('maps paid to Pagada', function () {
    expect(presentInv(['status' => 'paid'])->statusLabel)->toBe('Pagada');
});

it('maps open to Pendiente', function () {
    expect(presentInv(['status' => 'open'])->statusLabel)->toBe('Pendiente');
});

it('maps draft to Borrador', function () {
    expect(presentInv(['status' => 'draft'])->statusLabel)->toBe('Borrador');
});

it('maps uncollectible to Incobrable', function () {
    expect(presentInv(['status' => 'uncollectible'])->statusLabel)->toBe('Incobrable');
});

it('maps void to Anulada', function () {
    expect(presentInv(['status' => 'void'])->statusLabel)->toBe('Anulada');
});

it('title-cases unknown statuses', function () {
    expect(presentInv(['status' => 'disputed'])->statusLabel)->toBe('Disputed');
});

// ── Status badge classes ──────────────────────────────────────────────────────

it('applies emerald badge to paid', function () {
    expect(presentInv(['status' => 'paid'])->statusBadgeClass)->toBe('bg-emerald-900/30 text-emerald-400');
});

it('applies blue badge to open', function () {
    expect(presentInv(['status' => 'open'])->statusBadgeClass)->toBe('bg-blue-900/30 text-blue-400');
});

it('applies zinc badge to draft', function () {
    expect(presentInv(['status' => 'draft'])->statusBadgeClass)->toBe('bg-[#27272a] text-[#71717a]');
});

it('applies red badge to uncollectible', function () {
    expect(presentInv(['status' => 'uncollectible'])->statusBadgeClass)->toBe('bg-red-900/30 text-red-400');
});

it('applies amber badge to void', function () {
    expect(presentInv(['status' => 'void'])->statusBadgeClass)->toBe('bg-amber-900/30 text-amber-400');
});

// ── Frequency ─────────────────────────────────────────────────────────────────

it('formats month x1 as Mensual', function () {
    expect(presentInv(['interval' => 'month', 'intervalCount' => 1])->frequency)->toBe('Mensual');
});

it('formats year x1 as Anual', function () {
    expect(presentInv(['interval' => 'year', 'intervalCount' => 1])->frequency)->toBe('Anual');
});

it('formats week x1 as Semanal', function () {
    expect(presentInv(['interval' => 'week', 'intervalCount' => 1])->frequency)->toBe('Semanal');
});

it('formats day x1 as Diario', function () {
    expect(presentInv(['interval' => 'day', 'intervalCount' => 1])->frequency)->toBe('Diario');
});

it('formats month x3 as Trimestral', function () {
    expect(presentInv(['interval' => 'month', 'intervalCount' => 3])->frequency)->toBe('Trimestral');
});

it('formats month x6 as Semestral', function () {
    expect(presentInv(['interval' => 'month', 'intervalCount' => 6])->frequency)->toBe('Semestral');
});

it('shows dash when interval is null', function () {
    expect(presentInv(['interval' => null])->frequency)->toBe('—');
});

// ── Customer fields ───────────────────────────────────────────────────────────

it('passes customerName through', function () {
    expect(presentInv(['customerName' => 'Carlos López'])->customerName)->toBe('Carlos López');
});

it('replaces null customerName with dash', function () {
    expect(presentInv(['customerName' => null])->customerName)->toBe('—');
});

it('passes customerEmail through', function () {
    expect(presentInv(['customerEmail' => 'carlos@example.com'])->customerEmail)->toBe('carlos@example.com');
});

it('replaces null customerEmail with empty string', function () {
    expect(presentInv(['customerEmail' => null])->customerEmail)->toBe('');
});

// ── Dates ─────────────────────────────────────────────────────────────────────

it('formats createdAt in Spanish locale', function () {
    expect(presentInv(['createdAt' => new \DateTimeImmutable('2025-08-01')])->date)->toBe('1 ago. 2025');
});

it('formats dueDate in Spanish locale', function () {
    expect(presentInv(['dueDate' => new \DateTimeImmutable('2025-09-01')])->dueDate)->toBe('1 sep. 2025');
});

it('shows dash when dueDate is null', function () {
    expect(presentInv(['dueDate' => null])->dueDate)->toBe('—');
});

// ── Action fields ─────────────────────────────────────────────────────────────

it('passes userId through when set', function () {
    expect(presentInv(['userId' => 42])->userId)->toBe(42);
});

it('passes userId as null when not set', function () {
    expect(presentInv()->userId)->toBeNull();
});

it('passes stripeSubscriptionId through when set', function () {
    expect(presentInv(['stripeSubscriptionId' => 'sub_XYZ'])->stripeSubscriptionId)->toBe('sub_XYZ');
});

it('passes stripeSubscriptionId as null when not set', function () {
    expect(presentInv()->stripeSubscriptionId)->toBeNull();
});
