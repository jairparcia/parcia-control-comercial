<?php

namespace App\Livewire\Admin;

use App\Application\Admin\ArchiveCustomerService;
use App\Application\Admin\CancelSubscriptionService;
use App\Application\Admin\GetCancellationInfoService;
use App\Application\Admin\GetCustomerDetailService;
use App\Application\Admin\ImportStripeCustomersService;
use App\Application\Admin\ListCustomersService;
use App\Application\Admin\RestoreCustomerService;
use App\Application\Admin\SyncCustomerFromStripeService;
use App\Application\Admin\UpdateCustomerService;
use App\Http\Presenters\Admin\AdminCustomerPresenter;
use App\Http\Presenters\Admin\AdminSubscriptionPresenter;
use Livewire\Component;

class CustomersComponent extends Component
{
    // Table filter
    public string $statusFilter = 'active';

    // Table import
    public bool $importing = false;

    // Panel open/close
    public bool $panelOpen  = false;
    public int  $selectedId = 0;
    public bool $syncing    = false;

    // Personal info (read-only)
    public string $panelName        = '';
    public string $panelEmail       = '';
    public string $panelMemberSince = '';
    public string $panelTotalSpent  = '';
    public string $panelMrr         = '';

    // Editable fields
    public ?string $editDescription = null;
    public ?string $editCountry     = null;
    public bool    $saving          = false;

    // Archive state
    public bool $panelArchived     = false;
    public bool $archiveModalOpen  = false;

    // Subscription section
    public bool   $hasSub             = false;
    public string $panelSubStripeId   = '';
    public string $panelSubPlanName   = '';
    public string $panelSubInterval   = '';
    public string $panelSubNextDate   = '';
    public string $panelSubNextAmount = '';

    // Payment history
    public array $panelPayments = [];

    // Cancel modal
    public bool   $cancelModalOpen      = false;
    public bool   $canceling            = false;
    public string $cancelStripeId       = '';
    public string $cancelTiming         = 'immediately';
    public string $cancelRefundType     = 'none';
    public string $cancelPeriodEnd      = '';
    public string $cancelLastPayment    = '';
    public string $cancelProratedAmount = '';
    public int    $cancelProratedDays   = 0;

    private ListCustomersService          $listService;
    private ImportStripeCustomersService  $importService;
    private GetCustomerDetailService      $detailService;
    private UpdateCustomerService         $updateService;
    private SyncCustomerFromStripeService $syncService;
    private ArchiveCustomerService        $archiveService;
    private RestoreCustomerService        $restoreService;
    private GetCancellationInfoService    $infoService;
    private CancelSubscriptionService     $cancelService;
    private AdminCustomerPresenter        $presenter;
    private AdminSubscriptionPresenter    $subscriptionPresenter;

    public function boot(
        ListCustomersService          $listService,
        ImportStripeCustomersService  $importService,
        GetCustomerDetailService      $detailService,
        UpdateCustomerService         $updateService,
        SyncCustomerFromStripeService $syncService,
        ArchiveCustomerService        $archiveService,
        RestoreCustomerService        $restoreService,
        GetCancellationInfoService    $infoService,
        CancelSubscriptionService     $cancelService,
        AdminCustomerPresenter        $presenter,
        AdminSubscriptionPresenter    $subscriptionPresenter,
    ): void {
        $this->listService           = $listService;
        $this->importService         = $importService;
        $this->detailService         = $detailService;
        $this->updateService         = $updateService;
        $this->syncService           = $syncService;
        $this->archiveService        = $archiveService;
        $this->restoreService        = $restoreService;
        $this->infoService           = $infoService;
        $this->cancelService         = $cancelService;
        $this->presenter             = $presenter;
        $this->subscriptionPresenter = $subscriptionPresenter;
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
            report($e);
            $this->dispatch('toast', message: 'Import failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->importing = false;
        }
    }

    public function openPanel(int $id): void
    {
        try {
            $this->loadPanelData($id);
            $this->panelOpen = true;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Could not load customer: ' . $e->getMessage(), type: 'error');
        }
    }

    public function closePanel(): void
    {
        $this->panelOpen  = false;
        $this->selectedId = 0;
    }

    public function syncCustomer(): void
    {
        $this->syncing = true;

        try {
            $this->syncService->execute($this->selectedId);
            $this->loadPanelData($this->selectedId);
            $this->dispatch('toast', message: 'Customer synced from Stripe.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Sync failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->syncing = false;
        }
    }

    public function saveCustomer(): void
    {
        $this->saving = true;

        try {
            $this->updateService->execute(
                userId:      $this->selectedId,
                description: $this->editDescription,
                country:     $this->editCountry,
            );

            $this->dispatch('toast', message: 'Customer updated successfully.', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Update failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->saving = false;
        }
    }

    public function archiveCustomer(): void
    {
        if ($this->hasSub) {
            $this->archiveModalOpen = true;
            return;
        }

        $this->doArchive();
    }

    public function confirmArchive(): void
    {
        $this->archiveModalOpen = false;
        $this->doArchive();
    }

    public function closeArchiveModal(): void
    {
        $this->archiveModalOpen = false;
    }

    private function doArchive(): void
    {
        try {
            $this->archiveService->execute($this->selectedId);
            $this->dispatch('toast', message: 'Customer archived.', type: 'success');
            $this->closePanel();
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Archive failed: ' . $e->getMessage(), type: 'error');
        }
    }

    public function restoreCustomer(): void
    {
        try {
            $this->restoreService->execute($this->selectedId);
            $this->panelArchived = false;
            $this->dispatch('toast', message: 'Customer restored.', type: 'success');
            $this->closePanel();
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Restore failed: ' . $e->getMessage(), type: 'error');
        }
    }

    public function openCancelModal(string $stripeId): void
    {
        try {
            $info = $this->subscriptionPresenter->presentCancellationInfo($this->infoService->execute($stripeId));

            $this->cancelStripeId       = $stripeId;
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
            $this->hasSub = false;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Cancellation failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->canceling = false;
        }
    }

    public function render()
    {
        return view('livewire.admin.customers-component', [
            'customers' => $this->presenter->presentAll($this->listService->execute($this->statusFilter)),
        ]);
    }

    private function loadPanelData(int $id): void
    {
        $detail = $this->presenter->presentDetail($this->detailService->execute($id));

        $this->selectedId         = $id;
        $this->panelName          = $detail->name;
        $this->panelEmail         = $detail->email;
        $this->panelMemberSince   = $detail->memberSince;
        $this->panelTotalSpent    = $detail->totalSpent;
        $this->panelMrr           = $detail->mrr;
        $this->editDescription    = $detail->description;
        $this->editCountry        = $detail->country;
        $this->panelArchived      = $detail->archived;
        $this->hasSub             = $detail->hasSub;
        $this->panelSubStripeId   = $detail->subStripeId;
        $this->panelSubPlanName   = $detail->subPlanName;
        $this->panelSubInterval   = $detail->subInterval;
        $this->panelSubNextDate   = $detail->subNextDate;
        $this->panelSubNextAmount = $detail->subNextAmount;
        $this->panelPayments      = $detail->payments;
    }
}
