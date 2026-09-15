<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\PlanAdminRepositoryInterface;

class ListAdminPlansService
{
    public function __construct(
        private readonly PlanAdminRepositoryInterface $plans,
    ) {}

    public function execute(): array
    {
        return $this->plans->all();
    }
}
