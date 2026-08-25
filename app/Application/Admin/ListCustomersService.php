<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;

class ListCustomersService
{
    public function __construct(
        private readonly CustomerAdminRepositoryInterface $repository,
    ) {}

    public function execute(): array
    {
        return $this->repository->all();
    }
}
