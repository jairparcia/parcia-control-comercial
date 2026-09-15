<?php

use App\Application\Admin\ListCustomersService;
use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;

it('passes the status filter through to the repository', function (string $filter) {
    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->shouldReceive('all')->once()->with($filter)->andReturn([]);

    $result = (new ListCustomersService($repo))->execute($filter);

    expect($result)->toBeArray()->toBeEmpty();
})->with(['all', 'active', 'inactive', 'archived']);

it('defaults to all statuses when no filter is provided', function () {
    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->shouldReceive('all')->once()->with('all')->andReturn([]);

    (new ListCustomersService($repo))->execute();
});

afterEach(fn () => Mockery::close());
