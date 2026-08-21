<?php

namespace App\Livewire\Admin;

use App\Application\Admin\CancelSubscriptionService;
use App\Application\Admin\GetCancellationInfoService;
use App\Application\Admin\ImportStripeSubscriptionsService;
use App\Application\Admin\ListAdminSubscriptionsService;
use App\Http\Presenters\Admin\AdminSubscriptionPresenter;
use Livewire\Component;

class SubscriptionsComponent extends Component
{
    public bool   $importing     = false;
    public string $statusFilter  = 'active';

    public bool   $cancelModalOpen    = false;
    public bool   $canceling          = false;
    public string $cancelStripeId     = '';
    public string $cancelTiming       = 'immediately'; // immediately | period_end
    public string $cancelRefundType   = 'none';        // none | full | prorated

    public string $cancelPeriodEnd       = '';
    public string $cancelLastPayment     = '';
    public string $cancelProratedAmount  = '';
    public int    $cancelProratedDays    = 0;

    private ListAdminSubscriptionsService   $listService;
    private ImportStripeSubscriptionsService $importService;
    private GetCancellationInfoService      $infoService;
    private CancelSubscriptionService       $cancelService;
    private AdminSubscriptionPresenter      $presenter;

    public function boot(
        ListAdminSubscriptionsService    $listService,
        ImportStripeSubscriptionsService $importService,
        GetCancellationInfoService       $infoService,
        CancelSubscriptionService        $cancelService,
        AdminSubscriptionPresenter       $presenter,
    ): void {
        $this->listService   = $listService;
        $this->importService = $importService;
        $this->infoService   = $infoService;
        $this->cancelService = $cancelService;
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
            $this->dispatch('toast', message: 'Import failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->importing = false;
        }
    }

    public function openCancelModal(string $stripeId): void
    {
        try {
            $info = $this->presenter->presentCancellationInfo($this->infoService->execute($stripeId));

            $this->cancelStripeId        = $stripeId;
            $this->cancelTiming          = 'immediately';
            $this->cancelRefundType      = 'none';
            $this->cancelPeriodEnd       = $info->periodEndFormatted;
            $this->cancelLastPayment     = $info->lastPaymentFormatted;
            $this->cancelProratedAmount  = $info->proratedAmountFormatted;
            $this->cancelProratedDays    = $info->proratedDays;
            $this->cancelModalOpen       = true;
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Could not load subscription info: ' . $e->getMessage(), type: 'error');
        }
    }

    public function closeCancelModal(): void
    {
        $this->cancelModalOpen = false;
        $this->cancelStripeId  = '';
    }

    public function confirmCancel(): void
    {
        $this->canceling = true;

        try {
            $this->cancelService->execute(
                stripeSubscriptionId: $this->cancelStripeId,
                immediately:          $this->cancelTiming === 'immediately',
                refundType:           $this->cancelRefundType,
            );

            $message = $this->cancelTiming === 'immediately'
                ? 'Subscription canceled immediately.'
                : 'Subscription set to cancel at period end.';

            $this->dispatch('toast', message: $message, type: 'success');
            $this->closeCancelModal();
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Cancellation failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->canceling = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.subscriptions-component', [
            'subscriptions' => $this->presenter->presentAll($this->listService->execute($this->statusFilter)),
        ]);
    }
}
