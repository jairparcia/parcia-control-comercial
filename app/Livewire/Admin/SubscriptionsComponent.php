<?php

namespace App\Livewire\Admin;

use App\Application\Admin\ImportStripeSubscriptionsService;
use App\Application\Admin\ListAdminSubscriptionsService;
use App\Http\Presenters\Admin\AdminSubscriptionPresenter;
use Livewire\Attributes\On;
use Livewire\Component;

class SubscriptionsComponent extends Component
{
    public bool   $importing    = false;
    public string $statusFilter = 'active';

    private ListAdminSubscriptionsService    $listService;
    private ImportStripeSubscriptionsService $importService;
    private AdminSubscriptionPresenter       $presenter;

    public function boot(
        ListAdminSubscriptionsService    $listService,
        ImportStripeSubscriptionsService $importService,
        AdminSubscriptionPresenter       $presenter,
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
                    ? "{$count} subscription(s) imported from Stripe."
                    : 'No new subscriptions to import.',
                type: $count > 0 ? 'success' : 'info',
            );
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Import failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->importing = false;
        }
    }

    #[On('subscription-updated')]
    public function handleSubscriptionUpdated(): void {}

    public function render()
    {
        return view('livewire.admin.subscriptions-component', [
            'subscriptions' => $this->presenter->presentAll($this->listService->execute($this->statusFilter)),
        ]);
    }
}
