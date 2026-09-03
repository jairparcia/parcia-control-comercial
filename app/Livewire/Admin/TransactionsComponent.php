<?php

namespace App\Livewire\Admin;

use App\Application\Admin\ImportStripeTransactionsService;
use App\Application\Admin\ListAdminTransactionsService;
use App\Http\Presenters\Admin\AdminTransactionPresenter;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TransactionsComponent extends Component
{
    public string $statusFilter = 'all';
    public bool   $importing    = false;

    private ListAdminTransactionsService   $listService;
    private ImportStripeTransactionsService $importService;
    private AdminTransactionPresenter      $presenter;

    public function boot(
        ListAdminTransactionsService    $listService,
        ImportStripeTransactionsService $importService,
        AdminTransactionPresenter       $presenter,
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
                    ? "{$count} transaction(s) imported from Stripe."
                    : 'No new transactions to import.',
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
        return view('livewire.admin.transactions-component', [
            'transactions' => $this->presenter->presentAll(
                $this->listService->execute($this->statusFilter),
            ),
        ]);
    }
}
