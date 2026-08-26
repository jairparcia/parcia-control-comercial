<?php

use App\Application\Admin\RestoreCustomerService;
use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;

it('delegates restore to the repository with the given user id', function () {
    $repo = Mockery::mock(CustomerAdminRepositoryInterface::class);
    $repo->shouldReceive('restore')->once()->with(7);

    (new RestoreCustomerService($repo))->execute(7);
});

afterEach(fn () => Mockery::close());
