<?php

namespace App\Livewire\Admin;

use App\Application\Admin\CancelSubscriptionService;
use App\Application\Admin\GetCancellationInfoService;
use App\Application\Admin\GetSubscriptionDetailService;
use App\Http\Presenters\Admin\AdminSubscriptionPresenter;
use Livewire\Attributes\On;
use Livewire\Component;

class SubscriptionDetailPanel extends Component
{
    // Panel state
    public bool   $panelOpen  = false;
    public string $stripeId   = '';

    // Overview section
    public string $userName         = '';
    public string $userEmail        = '';
    public string $statusLabel      = '';
    public string $statusBadgeClass = '';
    public string $subscribedAt     = '';
    public string $planName         = '';
    public string $interval         = '';
    public string $formattedAmount  = '';
    public string $currentPeriod    = '';

    // Upcoming invoice
    public ?array $upcomingInvoice = null;

    // Invoice history
    public array $invoices = [];

    // Cancel modal
    public bool   $cancelModalOpen      = false;
    public bool   $canceling            = false;
    public string $cancelTiming         = 'immediately';
    public string $cancelRefundType     = 'none';
    public string $cancelPeriodEnd      = '';
    public string $cancelLastPayment    = '';
    public string $cancelProratedAmount = '';
    public int    $cancelProratedDays   = 0;

    private GetSubscriptionDetailService $detailService;
    private GetCancellationInfoService   $infoService;
    private CancelSubscriptionService    $cancelService;
    private AdminSubscriptionPresenter   $presenter;

    public function boot(
        GetSubscriptionDetailService $detailService,
        GetCancellationInfoService   $infoService,
        CancelSubscriptionService    $cancelService,
        AdminSubscriptionPresenter   $presenter,
    ): void {
        $this->detailService = $detailService;
        $this->infoService   = $infoService;
        $this->cancelService = $cancelService;
        $this->presenter     = $presenter;
    }

    #[On('open-subscription-panel')]
    public function openPanel(string $stripeId): void
    {
        try {
            $this->loadPanelData($stripeId);
            $this->panelOpen = true;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Could not load subscription: ' . $e->getMessage(), type: 'error');
        }
    }

    public function close(): void
    {
        $this->panelOpen = false;
        $this->stripeId  = '';
    }

    public function openCancelModal(): void
    {
        try {
            $info = $this->presenter->presentCancellationInfo(
                $this->infoService->execute($this->stripeId),
            );

            $this->cancelTiming         = 'immediately';
            $this->cancelRefundType     = 'none';
            $this->cancelPeriodEnd      = $info->periodEndFormatted;
            $this->cancelLastPayment    = $info->lastPaymentFormatted;
            $this->cancelProratedAmount = $info->proratedAmountFormatted;
            $this->cancelProratedDays   = $info->proratedDays;
            $this->cancelModalOpen      = true;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Could not load subscription info: ' . $e->getMessage(), type: 'error');
        }
    }

    public function closeCancelModal(): void
    {
        $this->cancelModalOpen = false;
    }

    public function confirmCancel(): void
    {
        $this->canceling = true;

        try {
            $this->cancelService->execute(
                stripeSubscriptionId: $this->stripeId,
                immediately:          $this->cancelTiming === 'immediately',
                refundType:           $this->cancelRefundType,
            );

            $message = $this->cancelTiming === 'immediately'
                ? 'Subscription canceled immediately.'
                : 'Subscription set to cancel at period end.';

            $this->dispatch('toast', message: $message, type: 'success');
            $this->dispatch('subscription-updated');
            $this->closeCancelModal();
            $this->close();
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Cancellation failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->canceling = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.subscription-detail-panel');
    }

    private function loadPanelData(string $stripeId): void
    {
        $detail = $this->detailService->execute($stripeId);

        if (! $detail) {
            throw new \RuntimeException('Subscription not found.');
        }

        $vm = $this->presenter->presentDetail($detail);

        $this->stripeId        = $stripeId;
        $this->userName        = $vm->userName;
        $this->userEmail       = $vm->userEmail;
        $this->statusLabel     = $vm->statusLabel;
        $this->statusBadgeClass = $vm->statusBadgeClass;
        $this->subscribedAt    = $vm->subscribedAt;
        $this->planName        = $vm->planName;
        $this->interval        = $vm->interval;
        $this->formattedAmount = $vm->formattedAmount;
        $this->currentPeriod   = $vm->currentPeriod;
        $this->upcomingInvoice = $vm->upcomingInvoice;
        $this->invoices        = $vm->invoices;
    }
}
