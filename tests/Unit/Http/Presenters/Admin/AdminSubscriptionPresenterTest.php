<?php

use App\Domain\Admin\Results\AdminSubscriptionResult;
use App\Http\Presenters\Admin\AdminSubscriptionPresenter;
use App\Http\Presenters\Admin\AdminSubscriptionViewModel;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makePresenterResult(array $overrides = []): AdminSubscriptionResult
{
    return new AdminSubscriptionResult(
        id:               $overrides['id']        ?? 1,
        userId:           $overrides['userId']    ?? 1,
        stripeId:         $overrides['stripeId']  ?? 'sub_TEST',
        stripeCustomerId: $overrides['stripeCustomerId'] ?? 'cus_TEST',
        status:           $overrides['status']    ?? 'active',
        userName:         $overrides['userName']  ?? 'Jane Doe',
        userEmail:        $overrides['userEmail'] ?? 'jane@example.com',
        pmType:           array_key_exists('pmType', $overrides)     ? $overrides['pmType']     : 'visa',
        pmLastFour:       array_key_exists('pmLastFour', $overrides) ? $overrides['pmLastFour'] : '4242',
        planName:         array_key_exists('planName', $overrides)   ? $overrides['planName']   : 'Starter',
        planKey:          array_key_exists('planKey', $overrides)    ? $overrides['planKey']    : 'starter',
        unitAmount:       array_key_exists('unitAmount', $overrides) ? $overrides['unitAmount'] : 50000,
        currency:         array_key_exists('currency', $overrides)   ? $overrides['currency']   : 'MXN',
        interval:         array_key_exists('interval', $overrides)   ? $overrides['interval']   : 'month',
        subscribedAt:     $overrides['subscribedAt'] ?? new \DateTimeImmutable('2025-01-15'),
        endsAt:           array_key_exists('endsAt', $overrides)     ? $overrides['endsAt']     : null,
    );
}

function presentSingle(array $overrides = []): AdminSubscriptionViewModel
{
    return (new AdminSubscriptionPresenter())->presentAll([makePresenterResult($overrides)])[0];
}

// ── Status labels ─────────────────────────────────────────────────────────────

it('maps active to Activa', function () {
    expect(presentSingle(['status' => 'active'])->statusLabel)->toBe('Activa');
});

it('maps trialing to Prueba', function () {
    expect(presentSingle(['status' => 'trialing'])->statusLabel)->toBe('Prueba');
});

it('maps past_due to Pago pendiente', function () {
    expect(presentSingle(['status' => 'past_due'])->statusLabel)->toBe('Pago pendiente');
});

it('maps canceled to Cancelada', function () {
    expect(presentSingle(['status' => 'canceled'])->statusLabel)->toBe('Cancelada');
});

it('maps incomplete to Incompleta', function () {
    expect(presentSingle(['status' => 'incomplete'])->statusLabel)->toBe('Incompleta');
});

it('maps incomplete_expired to Expirada', function () {
    expect(presentSingle(['status' => 'incomplete_expired'])->statusLabel)->toBe('Expirada');
});

it('maps unpaid to Sin pagar', function () {
    expect(presentSingle(['status' => 'unpaid'])->statusLabel)->toBe('Sin pagar');
});

it('maps paused to Pausada', function () {
    expect(presentSingle(['status' => 'paused'])->statusLabel)->toBe('Pausada');
});

it('title-cases unknown statuses', function () {
    expect(presentSingle(['status' => 'pending'])->statusLabel)->toBe('Pending');
});

// ── Badge classes ─────────────────────────────────────────────────────────────

it('applies emerald badge class for active', function () {
    expect(presentSingle(['status' => 'active'])->statusBadgeClass)->toBe('bg-emerald-900/30 text-emerald-400');
});

it('applies blue badge class for trialing', function () {
    expect(presentSingle(['status' => 'trialing'])->statusBadgeClass)->toBe('bg-blue-900/30 text-blue-400');
});

it('applies red badge class for past_due', function () {
    expect(presentSingle(['status' => 'past_due'])->statusBadgeClass)->toBe('bg-red-900/30 text-red-400');
});

it('applies red badge class for unpaid', function () {
    expect(presentSingle(['status' => 'unpaid'])->statusBadgeClass)->toBe('bg-red-900/30 text-red-400');
});

it('applies default badge class for canceled', function () {
    expect(presentSingle(['status' => 'canceled'])->statusBadgeClass)->toBe('bg-[#27272a] text-[#71717a]');
});

// ── Dot classes ───────────────────────────────────────────────────────────────

it('applies emerald dot class for active', function () {
    expect(presentSingle(['status' => 'active'])->statusDotClass)->toBe('bg-emerald-400');
});

it('applies blue dot class for trialing', function () {
    expect(presentSingle(['status' => 'trialing'])->statusDotClass)->toBe('bg-blue-400');
});

it('applies red dot class for past_due', function () {
    expect(presentSingle(['status' => 'past_due'])->statusDotClass)->toBe('bg-red-400');
});

it('applies gray dot class for canceled', function () {
    expect(presentSingle(['status' => 'canceled'])->statusDotClass)->toBe('bg-[#52525b]');
});

// ── Payment method ────────────────────────────────────────────────────────────

it('formats payment method with capitalized card type and masked digits', function () {
    expect(presentSingle(['pmType' => 'visa', 'pmLastFour' => '4242'])->paymentMethod)->toBe('Visa ••••4242');
});

it('capitalizes multi-word card types', function () {
    expect(presentSingle(['pmType' => 'mastercard', 'pmLastFour' => '5678'])->paymentMethod)->toBe('Mastercard ••••5678');
});

it('returns a dash when both pmType and pmLastFour are null', function () {
    expect(presentSingle(['pmType' => null, 'pmLastFour' => null])->paymentMethod)->toBe('—');
});

it('returns a dash when pmLastFour is null even if pmType is set', function () {
    expect(presentSingle(['pmType' => 'visa', 'pmLastFour' => null])->paymentMethod)->toBe('—');
});

it('returns a dash when pmType is null even if pmLastFour is set', function () {
    expect(presentSingle(['pmType' => null, 'pmLastFour' => '9999'])->paymentMethod)->toBe('—');
});

// ── Amount calculation — monthly plan ─────────────────────────────────────────

it('uses unitAmount directly as monthly average for a monthly plan', function () {
    $vm = presentSingle(['unitAmount' => 50000, 'interval' => 'month', 'currency' => 'MXN']);

    expect($vm->formattedMonthlyAverage)->toBe('MX$500');
});

it('multiplies unitAmount by 12 to get annual average for a monthly plan', function () {
    $vm = presentSingle(['unitAmount' => 50000, 'interval' => 'month', 'currency' => 'MXN']);

    expect($vm->formattedAnnualAverage)->toBe('MX$6,000');
});

// ── Amount calculation — annual plan ─────────────────────────────────────────

it('divides unitAmount by 12 to get monthly average for an annual plan', function () {
    $vm = presentSingle(['unitAmount' => 120000, 'interval' => 'year', 'currency' => 'MXN']);

    expect($vm->formattedMonthlyAverage)->toBe('MX$100');
});

it('uses unitAmount directly as annual average for an annual plan', function () {
    $vm = presentSingle(['unitAmount' => 120000, 'interval' => 'year', 'currency' => 'MXN']);

    expect($vm->formattedAnnualAverage)->toBe('MX$1,200');
});

it('returns a dash for both averages when unitAmount is null', function () {
    $vm = presentSingle(['unitAmount' => null, 'interval' => 'month']);

    expect($vm->formattedMonthlyAverage)->toBe('—')
        ->and($vm->formattedAnnualAverage)->toBe('—');
});

it('returns a dash for both averages when interval is null', function () {
    $vm = presentSingle(['unitAmount' => 50000, 'interval' => null]);

    expect($vm->formattedMonthlyAverage)->toBe('—')
        ->and($vm->formattedAnnualAverage)->toBe('—');
});

// ── Currency formatting ───────────────────────────────────────────────────────

it('prefixes MXN amounts with MX$', function () {
    $vm = presentSingle(['unitAmount' => 99900, 'currency' => 'MXN', 'interval' => 'month']);

    expect($vm->formattedMonthlyAverage)->toBe('MX$999');
});

it('prefixes USD amounts with US$', function () {
    $vm = presentSingle(['unitAmount' => 2900, 'currency' => 'USD', 'interval' => 'month']);

    expect($vm->formattedMonthlyAverage)->toBe('US$29');
});

it('uses the currency code as prefix for unknown currencies', function () {
    $vm = presentSingle(['unitAmount' => 5000, 'currency' => 'EUR', 'interval' => 'month']);

    expect($vm->formattedMonthlyAverage)->toBe('EUR 50');
});

// ── Plan name fallback ────────────────────────────────────────────────────────

it('shows plan name when set', function () {
    expect(presentSingle(['planName' => 'Pro'])->planName)->toBe('Pro');
});

it('falls back to a dash when plan name is null', function () {
    expect(presentSingle(['planName' => null])->planName)->toBe('—');
});

// ── Dates ─────────────────────────────────────────────────────────────────────

it('formats subscribedAt in Spanish locale', function () {
    expect(presentSingle(['subscribedAt' => new \DateTimeImmutable('2025-03-01')])->subscribedAt)->toBe('1 mar. 2025');
});

it('formats endsAt in Spanish locale when present', function () {
    expect(presentSingle(['endsAt' => new \DateTimeImmutable('2025-12-31')])->endsAt)->toBe('31 dic. 2025');
});

it('returns null for endsAt when not set', function () {
    expect(presentSingle(['endsAt' => null])->endsAt)->toBeNull();
});

// ── presentAll ────────────────────────────────────────────────────────────────

it('returns one ViewModel per result', function () {
    $results = [
        makePresenterResult(['id' => 1]),
        makePresenterResult(['id' => 2]),
        makePresenterResult(['id' => 3]),
    ];

    expect((new AdminSubscriptionPresenter())->presentAll($results))->toHaveCount(3);
});

it('returns an empty array when given no results', function () {
    expect((new AdminSubscriptionPresenter())->presentAll([]))->toBeEmpty();
});

it('each ViewModel is an instance of AdminSubscriptionViewModel', function () {
    $vms = (new AdminSubscriptionPresenter())->presentAll([makePresenterResult()]);

    expect($vms[0])->toBeInstanceOf(AdminSubscriptionViewModel::class);
});
