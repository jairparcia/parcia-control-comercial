<?php

namespace App\Http\Presenters\Admin;

use App\Domain\Admin\Results\AdminCustomerResult;
use Carbon\Carbon;

class AdminCustomerPresenter
{
    /** @return AdminCustomerViewModel[] */
    public function presentAll(array $customers): array
    {
        return array_map(fn ($c) => $this->present($c), $customers);
    }

    private function present(AdminCustomerResult $customer): AdminCustomerViewModel
    {
        return new AdminCustomerViewModel(
            id:          $customer->id,
            name:        $customer->name,
            email:       $customer->email,
            description: $customer->description ?? '—',
            country:     $customer->country ?? '—',
            createdAt:   Carbon::instance($customer->createdAt)->locale('es')->isoFormat('D MMM YYYY'),
        );
    }
}
