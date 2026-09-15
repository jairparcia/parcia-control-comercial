<?php

namespace App\Livewire\Admin;

use App\Application\Admin\ImportStripeInvoicesService;
use App\Application\Admin\ListAdminInvoicesService;
use App\Http\Presenters\Admin\AdminInvoicePresenter;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class InvoicesComponent extends Component
{
    public string $statusFilter = 'paid';
    public bool   $importing    = false;

    private ListAdminInvoicesService   $listService;
    private ImportStripeInvoicesService $importService;
    private AdminInvoicePresenter      $presenter;

    public function boot(
        ListAdminInvoicesService   $listService,
        ImportStripeInvoicesService $importService,
        AdminInvoicePresenter      $presenter,
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
                    ? "{$count} invoice(s) imported from Stripe."
                    : 'No new invoices to import.',
                type: $count > 0 ? 'success' : 'info',
            );
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Import failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->importing = false;
        }
    }

    public function render(): View
    {
        $invoices = $this->presenter->presentAll(
            $this->listService->execute($this->statusFilter),
        );

        return view('livewire.admin.invoices-component', ['invoices' => $invoices]);
    }
}
