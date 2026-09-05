<?php

use App\Domain\Admin\Results\TransactionDetailResult;
use App\Domain\Admin\Results\TransactionEventResult;
use App\Domain\Admin\Results\TransactionFeeDetailResult;
use App\Http\Presenters\Admin\AdminTransactionPresenter;
use App\Http\Presenters\Admin\TransactionDetailViewModel;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeTxDetail(array $overrides = []): TransactionDetailResult
{
    return new TransactionDetailResult(
        stripeId:        $overrides['stripeId']        ?? 'ch_TEST',
        amountCents:     $overrides['amountCents']     ?? 99900,
        currency:        $overrides['currency']        ?? 'MXN',
        status:          $overrides['status']          ?? 'succeeded',
        customerName:    array_key_exists('customerName', $overrides)  ? $overrides['customerName']  : 'Jane Doe',
        customerEmail:   array_key_exists('customerEmail', $overrides) ? $overrides['customerEmail'] : 'jane@example.com',
        stripeFeesCents: $overrides['stripeFeesCents'] ?? 1061,
        netAmountCents:  $overrides['netAmountCents']  ?? 98839,
        paymentMethodId: array_key_exists('paymentMethodId', $overrides) ? $overrides['paymentMethodId'] : 'pm_TEST',
        cardLast4:       array_key_exists('cardLast4', $overrides)       ? $overrides['cardLast4']       : '4242',
        cardFingerprint: array_key_exists('cardFingerprint', $overrides) ? $overrides['cardFingerprint'] : 'ABCXYZ',
        cardExpMonth:    array_key_exists('cardExpMonth', $overrides)    ? $overrides['cardExpMonth']    : '12',
        cardExpYear:     array_key_exists('cardExpYear', $overrides)     ? $overrides['cardExpYear']     : '2034',
        cardFunding:     array_key_exists('cardFunding', $overrides)     ? $overrides['cardFunding']     : 'credit',
        cardBrand:       array_key_exists('cardBrand', $overrides)       ? $overrides['cardBrand']       : 'Visa',
        cardIssuer:      array_key_exists('cardIssuer', $overrides)      ? $overrides['cardIssuer']      : null,
        cardCountry:     array_key_exists('cardCountry', $overrides)     ? $overrides['cardCountry']     : 'US',
        cvcCheck:        array_key_exists('cvcCheck', $overrides)        ? $overrides['cvcCheck']        : 'pass',
        billingName:     array_key_exists('billingName', $overrides)     ? $overrides['billingName']     : 'Jane Doe',
        billingEmail:    array_key_exists('billingEmail', $overrides)    ? $overrides['billingEmail']    : 'jane@example.com',
        billingCountry:  array_key_exists('billingCountry', $overrides)  ? $overrides['billingCountry']  : 'MX',
        subscriptionId:  array_key_exists('subscriptionId', $overrides)  ? $overrides['subscriptionId']  : 'sub_TEST',
        planName:        array_key_exists('planName', $overrides)        ? $overrides['planName']        : 'Starter',
        priceId:         array_key_exists('priceId', $overrides)         ? $overrides['priceId']         : 'price_TEST',
        invoiceNumber:   array_key_exists('invoiceNumber', $overrides)   ? $overrides['invoiceNumber']   : 'INV-0001',
        paymentIntentId: array_key_exists('paymentIntentId', $overrides) ? $overrides['paymentIntentId'] : 'pi_TEST',
        events:          $overrides['events']          ?? [],
        createdAt:       $overrides['createdAt']       ?? new \DateTimeImmutable('2025-08-20 09:16:00'),
        feeDetails:      $overrides['feeDetails']      ?? [],
    );
}

function presentTxDetail(array $overrides = []): TransactionDetailViewModel
{
    return (new AdminTransactionPresenter())->presentDetail(makeTxDetail($overrides));
}

// ── ViewModel type ────────────────────────────────────────────────────────────

it('returns a TransactionDetailViewModel', function () {
    expect(presentTxDetail())->toBeInstanceOf(TransactionDetailViewModel::class);
});

// ── Passthrough fields ────────────────────────────────────────────────────────

it('passes stripeId through', function () {
    expect(presentTxDetail(['stripeId' => 'ch_XYZ'])->stripeId)->toBe('ch_XYZ');
});

it('passes status through', function () {
    expect(presentTxDetail(['status' => 'failed'])->status)->toBe('failed');
});

it('passes customerName through', function () {
    expect(presentTxDetail(['customerName' => 'Carlos'])->customerName)->toBe('Carlos');
});

it('passes customerEmail through', function () {
    expect(presentTxDetail(['customerEmail' => 'carlos@example.com'])->customerEmail)->toBe('carlos@example.com');
});

// ── Status labels ─────────────────────────────────────────────────────────────

it('maps succeeded to Exitoso in the detail view', function () {
    expect(presentTxDetail(['status' => 'succeeded'])->statusLabel)->toBe('Exitoso');
});

it('maps failed to Fallido in the detail view', function () {
    expect(presentTxDetail(['status' => 'failed'])->statusLabel)->toBe('Fallido');
});

it('maps refunded to Reembolsado in the detail view', function () {
    expect(presentTxDetail(['status' => 'refunded'])->statusLabel)->toBe('Reembolsado');
});

// ── Amount formatting ─────────────────────────────────────────────────────────

it('formats the main amount in MXN with two decimals', function () {
    expect(presentTxDetail(['amountCents' => 99900, 'currency' => 'MXN'])->formattedAmount)->toBe('MX$999.00');
});

it('formats the Stripe fee with two decimals', function () {
    expect(presentTxDetail(['stripeFeesCents' => 1061, 'currency' => 'MXN'])->formattedFees)->toBe('MX$10.61');
});

it('formats the net amount with two decimals', function () {
    expect(presentTxDetail(['netAmountCents' => 98839, 'currency' => 'MXN'])->formattedNet)->toBe('MX$988.39');
});

// ── Card display ──────────────────────────────────────────────────────────────

it('formats card display as •••• last4', function () {
    expect(presentTxDetail(['cardLast4' => '4242'])->cardDisplay)->toBe('•••• 4242');
});

it('sets cardDisplay to null when last4 is null', function () {
    expect(presentTxDetail(['cardLast4' => null])->cardDisplay)->toBeNull();
});

it('formats card expiry as MM / YYYY', function () {
    expect(presentTxDetail(['cardExpMonth' => '12', 'cardExpYear' => '2034'])->cardExpiry)->toBe('12 / 2034');
});

it('sets cardExpiry to null when expMonth is null', function () {
    expect(presentTxDetail(['cardExpMonth' => null, 'cardExpYear' => null])->cardExpiry)->toBeNull();
});

it('passes cardFingerprint through', function () {
    expect(presentTxDetail(['cardFingerprint' => 'ABCXYZ'])->cardFingerprint)->toBe('ABCXYZ');
});

// ── Card type ─────────────────────────────────────────────────────────────────

it('formats credit card type as tarjeta de crédito Brand', function () {
    expect(presentTxDetail(['cardFunding' => 'credit', 'cardBrand' => 'Visa'])->cardType)->toBe('tarjeta de crédito Visa');
});

it('formats debit card type as tarjeta de débito Brand', function () {
    expect(presentTxDetail(['cardFunding' => 'debit', 'cardBrand' => 'Mastercard'])->cardType)->toBe('tarjeta de débito Mastercard');
});

it('formats prepaid card type as prepago Brand', function () {
    expect(presentTxDetail(['cardFunding' => 'prepaid', 'cardBrand' => 'Amex'])->cardType)->toBe('prepago Amex');
});

it('falls back to tarjeta for unknown funding', function () {
    expect(presentTxDetail(['cardFunding' => 'unknown', 'cardBrand' => 'Visa'])->cardType)->toBe('tarjeta Visa');
});

it('returns null cardType when brand is null', function () {
    expect(presentTxDetail(['cardBrand' => null])->cardType)->toBeNull();
});

// ── CVC check ─────────────────────────────────────────────────────────────────

it('maps pass to Superada', function () {
    expect(presentTxDetail(['cvcCheck' => 'pass'])->cvcCheckLabel)->toBe('Superada');
});

it('maps fail to Fallida', function () {
    expect(presentTxDetail(['cvcCheck' => 'fail'])->cvcCheckLabel)->toBe('Fallida');
});

it('maps unavailable to No disponible', function () {
    expect(presentTxDetail(['cvcCheck' => 'unavailable'])->cvcCheckLabel)->toBe('No disponible');
});

it('maps unchecked to No verificado', function () {
    expect(presentTxDetail(['cvcCheck' => 'unchecked'])->cvcCheckLabel)->toBe('No verificado');
});

it('returns null cvcCheckLabel for unknown check values', function () {
    expect(presentTxDetail(['cvcCheck' => 'other'])->cvcCheckLabel)->toBeNull();
});

it('returns null cvcCheckLabel when check is null', function () {
    expect(presentTxDetail(['cvcCheck' => null])->cvcCheckLabel)->toBeNull();
});

// ── Purchase summary ──────────────────────────────────────────────────────────

it('passes subscriptionId through', function () {
    expect(presentTxDetail(['subscriptionId' => 'sub_XYZ'])->subscriptionId)->toBe('sub_XYZ');
});

it('passes planName through', function () {
    expect(presentTxDetail(['planName' => 'Pro'])->planName)->toBe('Pro');
});

it('passes priceId through', function () {
    expect(presentTxDetail(['priceId' => 'price_ABC'])->priceId)->toBe('price_ABC');
});

it('passes invoiceNumber through', function () {
    expect(presentTxDetail(['invoiceNumber' => 'INV-0099'])->invoiceNumber)->toBe('INV-0099');
});

it('passes paymentIntentId through', function () {
    expect(presentTxDetail(['paymentIntentId' => 'pi_XYZ'])->paymentIntentId)->toBe('pi_XYZ');
});

// ── Timeline events ───────────────────────────────────────────────────────────

it('returns empty events array when none provided', function () {
    expect(presentTxDetail(['events' => []])->events)->toBeEmpty();
});

it('maps event description and formats time in Spanish locale', function () {
    $events = [
        new TransactionEventResult('Pago iniciado', new \DateTimeImmutable('2025-08-20 09:16:00')),
    ];

    $result = presentTxDetail(['events' => $events])->events;

    expect($result[0]['description'])->toBe('Pago iniciado');
    expect($result[0]['time'])->toBe('20 ago. 09:16');
});

it('returns one row per event', function () {
    $events = [
        new TransactionEventResult('Pago iniciado', new \DateTimeImmutable('2025-08-20')),
        new TransactionEventResult('Pago efectuado correctamente', new \DateTimeImmutable('2025-08-20')),
    ];

    expect(presentTxDetail(['events' => $events])->events)->toHaveCount(2);
});

// ── Fee details ───────────────────────────────────────────────────────────────

it('returns empty feeDetails array when none provided', function () {
    expect(presentTxDetail(['feeDetails' => []])->feeDetails)->toBeEmpty();
});

it('maps fee detail description and formats amount', function () {
    $fees = [
        new TransactionFeeDetailResult('stripe_fee', 'Stripe processing fees', 917, 'MXN'),
    ];

    $vm = presentTxDetail(['feeDetails' => $fees]);

    expect($vm->feeDetails[0]['description'])->toBe('Stripe processing fees');
    expect($vm->feeDetails[0]['amount'])->toBe('MX$9.17');
});

it('maps multiple fee details', function () {
    $fees = [
        new TransactionFeeDetailResult('stripe_fee', 'Stripe processing fees', 917, 'MXN'),
        new TransactionFeeDetailResult('tax', 'IVA', 144, 'MXN'),
    ];

    expect(presentTxDetail(['feeDetails' => $fees])->feeDetails)->toHaveCount(2);
});

// ── Date ──────────────────────────────────────────────────────────────────────

it('formats createdAt in Spanish locale for the detail view', function () {
    expect(presentTxDetail(['createdAt' => new \DateTimeImmutable('2025-08-20')])->date)->toBe('20 ago. 2025');
});
