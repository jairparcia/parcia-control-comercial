<?php

namespace App\Livewire\Admin;

use App\Application\Admin\ListAdminTransactionsService;
use App\Http\Presenters\Admin\AdminTransactionPresenter;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TransactionsComponent extends Component
{
    public string $statusFilter = 'all';

    private ListAdminTransactionsService $listService;
    private AdminTransactionPresenter    $presenter;

    public function boot(
        ListAdminTransactionsService $listService,
        AdminTransactionPresenter    $presenter,
    ): void {
        $this->listService = $listService;
        $this->presenter   = $presenter;
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
