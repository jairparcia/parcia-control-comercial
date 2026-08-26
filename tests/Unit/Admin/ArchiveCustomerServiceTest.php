<?php

use App\Application\Admin\ArchiveCustomerService;
use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;

it('delegates archive to the repository with the given user id', function () {
    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->shouldReceive('archive')->once()->with(42);

    (new ArchiveCustomerService($repo))->execute(42);
});

afterEach(fn () => Mockery::close());
