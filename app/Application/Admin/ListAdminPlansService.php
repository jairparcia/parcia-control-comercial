<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\PlanAdminRepository;

class ListAdminPlansService
{
    public function __construct(
        private readonly PlanAdminRepository $plans,
    ) {}

    public function execute(): array
    {
        return $this->plans->all();
    }
}
