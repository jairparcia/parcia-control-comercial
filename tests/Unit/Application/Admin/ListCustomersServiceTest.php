<?php

use App\Application\Admin\ListCustomersService;
use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;
use App\Domain\Admin\Results\AdminCustomerResult;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeCustomerResult(array $overrides = []): AdminCustomerResult
{
    return new AdminCustomerResult(
        id:           $overrides['id']          ?? 1,
        name:         $overrides['name']        ?? 'Jane Doe',
        email:        $overrides['email']       ?? 'jane@example.com',
        description:  $overrides['description'] ?? null,
        country:      $overrides['country']     ?? null,
        archived:     $overrides['archived']    ?? false,
        hasActiveSub: $overrides['hasActiveSub'] ?? false,
        createdAt:    $overrides['createdAt']   ?? new \DateTimeImmutable('2025-01-15'),
    );
}

// ── Tests ─────────────────────────────────────────────────────────────────────

it('returns all customers from the repository', function () {
    $results = [makeCustomerResult(['id' => 1]), makeCustomerResult(['id' => 2])];

    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->expects('all')->once()->andReturn($results);

    expect((new ListCustomersService($repo))->execute())->toBe($results);
});

it('returns an empty array when there are no customers', function () {
    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->expects('all')->once()->andReturn([]);

    expect((new ListCustomersService($repo))->execute())->toBeEmpty();
});

it('delegates entirely to the repository with no transformation', function () {
    $result = makeCustomerResult(['name' => 'Specific Name']);

    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->allows('all')->andReturn([$result]);

    $output = (new ListCustomersService($repo))->execute();

    expect($output[0]->name)->toBe('Specific Name');
});
