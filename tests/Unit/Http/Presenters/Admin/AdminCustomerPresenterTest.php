<?php

use App\Domain\Admin\Results\AdminCustomerResult;
use App\Http\Presenters\Admin\AdminCustomerPresenter;
use App\Http\Presenters\Admin\AdminCustomerViewModel;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeCustomerPresenterResult(array $overrides = []): AdminCustomerResult
{
    return new AdminCustomerResult(
        id:          $overrides['id']          ?? 1,
        name:        $overrides['name']        ?? 'Jane Doe',
        email:       $overrides['email']       ?? 'jane@example.com',
        description: array_key_exists('description', $overrides) ? $overrides['description'] : 'Agency client',
        country:     array_key_exists('country', $overrides)     ? $overrides['country']     : 'MX',
        createdAt:   $overrides['createdAt']   ?? new \DateTimeImmutable('2025-03-01'),
    );
}

function presentCustomer(array $overrides = []): AdminCustomerViewModel
{
    return (new AdminCustomerPresenter())->presentAll([makeCustomerPresenterResult($overrides)])[0];
}

// ── Name and email ────────────────────────────────────────────────────────────

it('passes through name unchanged', function () {
    expect(presentCustomer(['name' => 'Carlos Molina'])->name)->toBe('Carlos Molina');
});

it('passes through email unchanged', function () {
    expect(presentCustomer(['email' => 'carlos@parcia.co'])->email)->toBe('carlos@parcia.co');
});

// ── Description ───────────────────────────────────────────────────────────────

it('passes through description when present', function () {
    expect(presentCustomer(['description' => 'Enterprise account'])->description)->toBe('Enterprise account');
});

it('falls back to a dash when description is null', function () {
    expect(presentCustomer(['description' => null])->description)->toBe('—');
});

// ── Country ───────────────────────────────────────────────────────────────────

it('passes through country when present', function () {
    expect(presentCustomer(['country' => 'US'])->country)->toBe('US');
});

it('falls back to a dash when country is null', function () {
    expect(presentCustomer(['country' => null])->country)->toBe('—');
});

// ── Date ─────────────────────────────────────────────────────────────────────

it('formats createdAt in Spanish locale', function () {
    expect(presentCustomer(['createdAt' => new \DateTimeImmutable('2025-03-01')])->createdAt)->toBe('1 mar. 2025');
});

it('formats a different date correctly', function () {
    expect(presentCustomer(['createdAt' => new \DateTimeImmutable('2025-12-31')])->createdAt)->toBe('31 dic. 2025');
});

// ── presentAll ────────────────────────────────────────────────────────────────

it('returns one ViewModel per result', function () {
    $results = [
        makeCustomerPresenterResult(['id' => 1]),
        makeCustomerPresenterResult(['id' => 2]),
        makeCustomerPresenterResult(['id' => 3]),
    ];

    expect((new AdminCustomerPresenter())->presentAll($results))->toHaveCount(3);
});

it('returns an empty array when given no results', function () {
    expect((new AdminCustomerPresenter())->presentAll([]))->toBeEmpty();
});

it('each ViewModel is an instance of AdminCustomerViewModel', function () {
    $vms = (new AdminCustomerPresenter())->presentAll([makeCustomerPresenterResult()]);

    expect($vms[0])->toBeInstanceOf(AdminCustomerViewModel::class);
});
