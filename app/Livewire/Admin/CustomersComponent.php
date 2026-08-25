<?php

namespace App\Livewire\Admin;

use App\Application\Admin\ImportStripeCustomersService;
use App\Application\Admin\ListCustomersService;
use App\Http\Presenters\Admin\AdminCustomerPresenter;
use Livewire\Component;

class CustomersComponent extends Component
{
    public bool $importing = false;

    private ListCustomersService        $listService;
    private ImportStripeCustomersService $importService;
    private AdminCustomerPresenter      $presenter;

    public function boot(
        ListCustomersService         $listService,
        ImportStripeCustomersService $importService,
        AdminCustomerPresenter       $presenter,
    ): void {
        $this->listService   = $listService;
        $this->importService = $importService;
        $this->presenter     = $presenter;
    }

    public function import(): void
    {
        $this->importing = true;

        try {
            $count = $this->importService->execute();

            $this->dispatch('toast',
                message: $count > 0
                    ? "{$count} customer(s) imported from Stripe."
                    : 'No new customers to import.',
                type: $count > 0 ? 'success' : 'info',
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Import failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->importing = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.customers-component', [
            'customers' => $this->presenter->presentAll($this->listService->execute()),
        ]);
    }
}
