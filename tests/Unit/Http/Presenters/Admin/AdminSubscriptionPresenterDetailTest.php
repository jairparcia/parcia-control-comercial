<?php

use App\Domain\Admin\Results\SubscriptionDetailResult;
use App\Domain\Admin\Results\SubscriptionInvoiceItemResult;
use App\Domain\Admin\Results\UpcomingInvoiceResult;
use App\Http\Presenters\Admin\AdminSubscriptionPresenter;
use App\Http\Presenters\Admin\SubscriptionDetailViewModel;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeDetail(array $overrides = []): SubscriptionDetailResult
{
    return new SubscriptionDetailResult(
        stripeId:         $overrides['stripeId']        ?? 'sub_TEST',
        stripeCustomerId: $overrides['stripeCustomerId'] ?? 'cus_TEST',
        userId:           $overrides['userId']          ?? 1,
        userName:         $overrides['userName']        ?? 'Jane Doe',
        userEmail:        $overrides['userEmail']       ?? 'jane@example.com',
        status:           $overrides['status']          ?? 'active',
        planName:         $overrides['planName']        ?? 'Pro',
        interval:         $overrides['interval']        ?? 'month',
        unitAmountCents:  $overrides['unitAmountCents'] ?? 99900,
        currency:         $overrides['currency']        ?? 'MXN',
        subscribedAt:     $overrides['subscribedAt']    ?? new \DateTimeImmutable('2025-01-15'),
        upcomingInvoice:  $overrides['upcomingInvoice'] ?? null,
        invoices:         $overrides['invoices']        ?? [],
    );
}

function makeUpcoming(array $overrides = []): UpcomingInvoiceResult
{
    return new UpcomingInvoiceResult(
        periodStart:          $overrides['periodStart']      ?? new \DateTimeImmutable('2025-09-20'),
        nextBillingDate:      $overrides['nextBillingDate']  ?? new \DateTimeImmutable('2025-10-20'),
        description:          $overrides['description']      ?? 'Pro Plan',
        quantity:             $overrides['quantity']         ?? 1,
        unitAmountCents:      $overrides['unitAmountCents']  ?? 99900,
        amountDueCents:       $overrides['amountDueCents']   ?? 99900,
        currency:             $overrides['currency']         ?? 'MXN',
        subtotalCents:        $overrides['subtotalCents']    ?? 99900,
        taxCents:             $overrides['taxCents']         ?? 0,
        totalCents:           $overrides['totalCents']       ?? 99900,
        amountPaidCents:      $overrides['amountPaidCents']  ?? 0,
        amountRemainingCents: $overrides['amountRemainingCents'] ?? 99900,
    );
}

function makeInvoice(array $overrides = []): SubscriptionInvoiceItemResult
{
    return new SubscriptionInvoiceItemResult(
        invoiceNumber: $overrides['invoiceNumber'] ?? 'INV-0001',
        amountCents:   $overrides['amountCents']   ?? 99900,
        currency:      $overrides['currency']      ?? 'MXN',
        interval:      $overrides['interval']      ?? 'month',
        status:        $overrides['status']        ?? 'paid',
        createdAt:     $overrides['createdAt']     ?? new \DateTimeImmutable('2025-09-20'),
    );
}

function presentDetail(array $overrides = []): SubscriptionDetailViewModel
{
    return (new AdminSubscriptionPresenter())->presentDetail(makeDetail($overrides));
}

// ── ViewModel type ────────────────────────────────────────────────────────────

it('returns a SubscriptionDetailViewModel', function () {
    expect(presentDetail())->toBeInstanceOf(SubscriptionDetailViewModel::class);
});

// ── Overview fields ───────────────────────────────────────────────────────────

it('passes stripeId through to the ViewModel', function () {
    expect(presentDetail(['stripeId' => 'sub_XYZ'])->stripeId)->toBe('sub_XYZ');
});

it('passes userName through to the ViewModel', function () {
    expect(presentDetail(['userName' => 'Carlos'])->userName)->toBe('Carlos');
});

it('passes userEmail through to the ViewModel', function () {
    expect(presentDetail(['userEmail' => 'carlos@example.com'])->userEmail)->toBe('carlos@example.com');
});

it('formats subscribedAt in Spanish locale', function () {
    expect(presentDetail(['subscribedAt' => new \DateTimeImmutable('2025-03-01')])->subscribedAt)->toBe('1 mar. 2025');
});

// ── Status labels ─────────────────────────────────────────────────────────────

it('maps active to Activa in the detail view', function () {
    expect(presentDetail(['status' => 'active'])->statusLabel)->toBe('Activa');
});

it('maps canceled to Cancelada in the detail view', function () {
    expect(presentDetail(['status' => 'canceled'])->statusLabel)->toBe('Cancelada');
});

it('maps past_due to Pago pendiente in the detail view', function () {
    expect(presentDetail(['status' => 'past_due'])->statusLabel)->toBe('Pago pendiente');
});

// ── Interval labels ───────────────────────────────────────────────────────────

it('translates month interval to Mensual', function () {
    expect(presentDetail(['interval' => 'month'])->interval)->toBe('Mensual');
});

it('translates year interval to Anual', function () {
    expect(presentDetail(['interval' => 'year'])->interval)->toBe('Anual');
});

it('translates week interval to Semanal', function () {
    expect(presentDetail(['interval' => 'week'])->interval)->toBe('Semanal');
});

it('title-cases unknown intervals', function () {
    expect(presentDetail(['interval' => 'biannual'])->interval)->toBe('Biannual');
});

// ── Amount formatting ─────────────────────────────────────────────────────────

it('formats MXN unit amount with MX$ prefix', function () {
    expect(presentDetail(['unitAmountCents' => 99900, 'currency' => 'MXN'])->formattedAmount)->toBe('MX$999');
});

it('formats USD unit amount with US$ prefix', function () {
    expect(presentDetail(['unitAmountCents' => 4500, 'currency' => 'USD'])->formattedAmount)->toBe('US$45');
});

// ── Current period — no upcoming invoice ──────────────────────────────────────

it('falls back to subscribedAt as period start when there is no upcoming invoice', function () {
    $vm = presentDetail([
        'subscribedAt'   => new \DateTimeImmutable('2025-01-15'),
        'upcomingInvoice' => null,
    ]);

    expect($vm->currentPeriod)->toStartWith('15 ene. 2025');
});

it('shows a dash as period end when there is no upcoming invoice', function () {
    $vm = presentDetail(['upcomingInvoice' => null]);

    expect($vm->currentPeriod)->toEndWith('—');
});

// ── Current period — with upcoming invoice ────────────────────────────────────

it('uses periodStart and nextBillingDate from the upcoming invoice for currentPeriod', function () {
    $upcoming = makeUpcoming([
        'periodStart'     => new \DateTimeImmutable('2025-09-20'),
        'nextBillingDate' => new \DateTimeImmutable('2025-10-20'),
    ]);

    $vm = presentDetail(['upcomingInvoice' => $upcoming]);

    expect($vm->currentPeriod)->toBe('20 sep. 2025 – 20 oct. 2025');
});

// ── Upcoming invoice — null when none ────────────────────────────────────────

it('sets upcomingInvoice to null in the ViewModel when not present', function () {
    expect(presentDetail(['upcomingInvoice' => null])->upcomingInvoice)->toBeNull();
});

// ── Upcoming invoice — field mapping ─────────────────────────────────────────

it('maps the upcoming invoice description', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['description' => 'Pro Monthly'])]);

    expect($vm->upcomingInvoice['description'])->toBe('Pro Monthly');
});

it('maps the upcoming invoice quantity', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['quantity' => 3])]);

    expect($vm->upcomingInvoice['quantity'])->toBe(3);
});

it('formats the upcoming invoice unit amount', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['unitAmountCents' => 99900, 'currency' => 'MXN'])]);

    expect($vm->upcomingInvoice['unitAmount'])->toBe('MX$999');
});

it('formats the upcoming invoice subtotal', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['subtotalCents' => 99900, 'currency' => 'MXN'])]);

    expect($vm->upcomingInvoice['subtotal'])->toBe('MX$999');
});

it('formats the upcoming invoice tax', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['taxCents' => 15984, 'currency' => 'MXN'])]);

    expect($vm->upcomingInvoice['tax'])->toBe('MX$160');
});

it('formats the upcoming invoice total', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['totalCents' => 115884, 'currency' => 'MXN'])]);

    expect($vm->upcomingInvoice['total'])->toBe('MX$1,159');
});

it('formats the upcoming invoice amount paid', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['amountPaidCents' => 0, 'currency' => 'MXN'])]);

    expect($vm->upcomingInvoice['amountPaid'])->toBe('MX$0');
});

it('formats the upcoming invoice amount remaining', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['amountRemainingCents' => 99900, 'currency' => 'MXN'])]);

    expect($vm->upcomingInvoice['amountRemaining'])->toBe('MX$999');
});

it('formats the upcoming invoice next billing date', function () {
    $vm = presentDetail(['upcomingInvoice' => makeUpcoming(['nextBillingDate' => new \DateTimeImmutable('2025-10-20')])]);

    expect($vm->upcomingInvoice['nextBillingDate'])->toBe('20 oct. 2025');
});

// ── Invoice history — empty ───────────────────────────────────────────────────

it('returns an empty invoices array when there are no invoices', function () {
    expect(presentDetail(['invoices' => []])->invoices)->toBeEmpty();
});

// ── Invoice history — field mapping ──────────────────────────────────────────

it('maps the invoice number', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['invoiceNumber' => 'INV-2025-09'])]]);

    expect($vm->invoices[0]['number'])->toBe('INV-2025-09');
});

it('replaces a blank invoice number with a dash', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['invoiceNumber' => ''])]]);

    expect($vm->invoices[0]['number'])->toBe('—');
});

it('formats the invoice amount in MXN', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['amountCents' => 99900, 'currency' => 'MXN'])]]);

    expect($vm->invoices[0]['amount'])->toBe('MX$999');
});

it('formats the invoice amount in USD', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['amountCents' => 4500, 'currency' => 'USD'])]]);

    expect($vm->invoices[0]['amount'])->toBe('US$45');
});

it('formats the invoice date in Spanish locale', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['createdAt' => new \DateTimeImmutable('2025-09-20')])]]);

    expect($vm->invoices[0]['date'])->toBe('20 sep. 2025');
});

it('copies the user email into each invoice row', function () {
    $vm = presentDetail([
        'userEmail' => 'jane@example.com',
        'invoices'  => [makeInvoice()],
    ]);

    expect($vm->invoices[0]['email'])->toBe('jane@example.com');
});

// ── Invoice history — interval labels ────────────────────────────────────────

it('translates month interval to Mensual in invoice row', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['interval' => 'month'])]]);

    expect($vm->invoices[0]['interval'])->toBe('Mensual');
});

it('translates year interval to Anual in invoice row', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['interval' => 'year'])]]);

    expect($vm->invoices[0]['interval'])->toBe('Anual');
});

// ── Invoice history — status labels ──────────────────────────────────────────

it('labels paid invoices as Pagada', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['status' => 'paid'])]]);

    expect($vm->invoices[0]['statusLabel'])->toBe('Pagada');
});

it('labels open invoices as Abierta', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['status' => 'open'])]]);

    expect($vm->invoices[0]['statusLabel'])->toBe('Abierta');
});

it('labels void invoices as Anulada', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['status' => 'void'])]]);

    expect($vm->invoices[0]['statusLabel'])->toBe('Anulada');
});

it('labels draft invoices as Borrador', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['status' => 'draft'])]]);

    expect($vm->invoices[0]['statusLabel'])->toBe('Borrador');
});

it('title-cases unknown invoice statuses', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['status' => 'refunded'])]]);

    expect($vm->invoices[0]['statusLabel'])->toBe('Refunded');
});

// ── Invoice history — status badge classes ────────────────────────────────────

it('applies emerald badge class to paid invoices', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['status' => 'paid'])]]);

    expect($vm->invoices[0]['statusBadgeClass'])->toBe('bg-emerald-900/30 text-emerald-400');
});

it('applies yellow badge class to open invoices', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['status' => 'open'])]]);

    expect($vm->invoices[0]['statusBadgeClass'])->toBe('bg-yellow-900/30 text-yellow-400');
});

it('applies default badge class to void invoices', function () {
    $vm = presentDetail(['invoices' => [makeInvoice(['status' => 'void'])]]);

    expect($vm->invoices[0]['statusBadgeClass'])->toBe('bg-[#27272a] text-[#71717a]');
});

// ── Multiple invoices ─────────────────────────────────────────────────────────

it('returns one row per invoice', function () {
    $vm = presentDetail([
        'invoices' => [
            makeInvoice(['invoiceNumber' => 'INV-001']),
            makeInvoice(['invoiceNumber' => 'INV-002']),
            makeInvoice(['invoiceNumber' => 'INV-003']),
        ],
    ]);

    expect($vm->invoices)->toHaveCount(3);
});
