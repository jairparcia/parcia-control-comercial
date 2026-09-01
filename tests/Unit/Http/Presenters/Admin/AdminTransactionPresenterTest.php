<?php

use App\Domain\Admin\Results\AdminTransactionResult;
use App\Http\Presenters\Admin\AdminTransactionPresenter;
use App\Http\Presenters\Admin\AdminTransactionViewModel;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeTxResult(array $overrides = []): AdminTransactionResult
{
    return new AdminTransactionResult(
        stripeId:            $overrides['stripeId']            ?? 'ch_TEST',
        amountCents:         $overrides['amountCents']         ?? 99900,
        amountRefundedCents: $overrides['amountRefundedCents'] ?? 0,
        currency:            $overrides['currency']            ?? 'MXN',
        status:              $overrides['status']              ?? 'succeeded',
        paymentMethodType:   array_key_exists('paymentMethodType', $overrides) ? $overrides['paymentMethodType'] : 'card',
        cardBrand:           array_key_exists('cardBrand', $overrides)         ? $overrides['cardBrand']         : 'Visa',
        cardLast4:           array_key_exists('cardLast4', $overrides)         ? $overrides['cardLast4']         : '4242',
        description:         array_key_exists('description', $overrides)       ? $overrides['description']       : 'Subscription',
        customerName:        array_key_exists('customerName', $overrides)      ? $overrides['customerName']      : 'Jane Doe',
        customerEmail:       array_key_exists('customerEmail', $overrides)     ? $overrides['customerEmail']     : 'jane@example.com',
        stripeCustomerId:    $overrides['stripeCustomerId']    ?? 'cus_TEST',
        createdAt:           $overrides['createdAt']           ?? new \DateTimeImmutable('2025-09-20'),
    );
}

function presentTx(array $overrides = []): AdminTransactionViewModel
{
    return (new AdminTransactionPresenter())->presentAll([makeTxResult($overrides)])[0];
}

// ── ViewModel type ────────────────────────────────────────────────────────────

it('returns AdminTransactionViewModel instances', function () {
    $result = (new AdminTransactionPresenter())->presentAll([makeTxResult()]);

    expect($result[0])->toBeInstanceOf(AdminTransactionViewModel::class);
});

it('returns one ViewModel per transaction', function () {
    $result = (new AdminTransactionPresenter())->presentAll([
        makeTxResult(['stripeId' => 'ch_001']),
        makeTxResult(['stripeId' => 'ch_002']),
    ]);

    expect($result)->toHaveCount(2);
});

// ── Passthrough fields ────────────────────────────────────────────────────────

it('passes stripeId through', function () {
    expect(presentTx(['stripeId' => 'ch_XYZ'])->stripeId)->toBe('ch_XYZ');
});

it('passes status through', function () {
    expect(presentTx(['status' => 'failed'])->status)->toBe('failed');
});

// ── Amount formatting ─────────────────────────────────────────────────────────

it('formats MXN amount with MX$ prefix and two decimals', function () {
    expect(presentTx(['amountCents' => 99900, 'currency' => 'MXN'])->formattedAmount)->toBe('MX$999.00');
});

it('formats USD amount with US$ prefix and two decimals', function () {
    expect(presentTx(['amountCents' => 4500, 'currency' => 'USD'])->formattedAmount)->toBe('US$45.00');
});

it('formats amounts greater than 999 with comma separator', function () {
    expect(presentTx(['amountCents' => 115900, 'currency' => 'MXN'])->formattedAmount)->toBe('MX$1,159.00');
});

it('sets formattedAmountRefunded to empty string when amount refunded is zero', function () {
    expect(presentTx(['amountRefundedCents' => 0])->formattedAmountRefunded)->toBe('');
});

it('formats the refunded amount with two decimals', function () {
    expect(presentTx([
        'amountRefundedCents' => 49950,
        'currency'            => 'MXN',
    ])->formattedAmountRefunded)->toBe('MX$499.50');
});

// ── Status labels ─────────────────────────────────────────────────────────────

it('maps succeeded to Exitoso', function () {
    expect(presentTx(['status' => 'succeeded'])->statusLabel)->toBe('Exitoso');
});

it('maps pending to Pendiente', function () {
    expect(presentTx(['status' => 'pending'])->statusLabel)->toBe('Pendiente');
});

it('maps failed to Fallido', function () {
    expect(presentTx(['status' => 'failed'])->statusLabel)->toBe('Fallido');
});

it('maps refunded to Reembolsado', function () {
    expect(presentTx(['status' => 'refunded'])->statusLabel)->toBe('Reembolsado');
});

it('maps partially_refunded to Parcialmente reembolsado', function () {
    expect(presentTx(['status' => 'partially_refunded'])->statusLabel)->toBe('Parcialmente reembolsado');
});

it('title-cases unknown statuses', function () {
    expect(presentTx(['status' => 'disputed'])->statusLabel)->toBe('Disputed');
});

// ── Status badge classes ──────────────────────────────────────────────────────

it('applies emerald badge to succeeded', function () {
    expect(presentTx(['status' => 'succeeded'])->statusBadgeClass)->toBe('bg-emerald-900/30 text-emerald-400');
});

it('applies blue badge to pending', function () {
    expect(presentTx(['status' => 'pending'])->statusBadgeClass)->toBe('bg-blue-900/30 text-blue-400');
});

it('applies red badge to failed', function () {
    expect(presentTx(['status' => 'failed'])->statusBadgeClass)->toBe('bg-red-900/30 text-red-400');
});

it('applies zinc badge to refunded', function () {
    expect(presentTx(['status' => 'refunded'])->statusBadgeClass)->toBe('bg-[#27272a] text-[#71717a]');
});

it('applies amber badge to partially_refunded', function () {
    expect(presentTx(['status' => 'partially_refunded'])->statusBadgeClass)->toBe('bg-amber-900/30 text-amber-400');
});

it('applies zinc badge to unknown statuses', function () {
    expect(presentTx(['status' => 'disputed'])->statusBadgeClass)->toBe('bg-[#27272a] text-[#71717a]');
});

// ── Payment method ────────────────────────────────────────────────────────────

it('formats card payment method as Brand ••••last4', function () {
    expect(presentTx(['cardBrand' => 'Visa', 'cardLast4' => '4242'])->paymentMethod)->toBe('Visa ••••4242');
});

it('formats mastercard correctly', function () {
    expect(presentTx(['cardBrand' => 'Mastercard', 'cardLast4' => '1234'])->paymentMethod)->toBe('Mastercard ••••1234');
});

it('returns OXXO label for oxxo type', function () {
    expect(presentTx([
        'paymentMethodType' => 'oxxo',
        'cardBrand'         => null,
        'cardLast4'         => null,
    ])->paymentMethod)->toBe('OXXO');
});

it('returns Bank transfer label for bank_transfer type', function () {
    expect(presentTx([
        'paymentMethodType' => 'bank_transfer',
        'cardBrand'         => null,
        'cardLast4'         => null,
    ])->paymentMethod)->toBe('Bank transfer');
});

it('returns dash when payment method type and card are both null', function () {
    expect(presentTx([
        'paymentMethodType' => null,
        'cardBrand'         => null,
        'cardLast4'         => null,
    ])->paymentMethod)->toBe('—');
});

it('uses card brand and last4 even when type is present', function () {
    expect(presentTx([
        'paymentMethodType' => 'card',
        'cardBrand'         => 'Amex',
        'cardLast4'         => '0001',
    ])->paymentMethod)->toBe('Amex ••••0001');
});

// ── Description ───────────────────────────────────────────────────────────────

it('passes description through', function () {
    expect(presentTx(['description' => 'Pro Plan Monthly'])->description)->toBe('Pro Plan Monthly');
});

it('replaces null description with dash', function () {
    expect(presentTx(['description' => null])->description)->toBe('—');
});

// ── Customer ──────────────────────────────────────────────────────────────────

it('passes customerName through', function () {
    expect(presentTx(['customerName' => 'Carlos López'])->customerName)->toBe('Carlos López');
});

it('replaces null customerName with dash', function () {
    expect(presentTx(['customerName' => null])->customerName)->toBe('—');
});

it('passes customerEmail through', function () {
    expect(presentTx(['customerEmail' => 'carlos@example.com'])->customerEmail)->toBe('carlos@example.com');
});

it('replaces null customerEmail with empty string', function () {
    expect(presentTx(['customerEmail' => null])->customerEmail)->toBe('');
});

// ── Date ──────────────────────────────────────────────────────────────────────

it('formats createdAt in Spanish locale', function () {
    expect(presentTx(['createdAt' => new \DateTimeImmutable('2025-03-15')])->date)->toBe('15 mar. 2025');
});

it('formats September date correctly', function () {
    expect(presentTx(['createdAt' => new \DateTimeImmutable('2025-09-20')])->date)->toBe('20 sep. 2025');
});
