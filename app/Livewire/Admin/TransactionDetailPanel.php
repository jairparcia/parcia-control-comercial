<?php

namespace App\Livewire\Admin;

use App\Application\Admin\GetTransactionDetailService;
use App\Http\Presenters\Admin\AdminTransactionPresenter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TransactionDetailPanel extends Component
{
    public bool    $panelOpen = false;
    public string  $chargeId  = '';

    // Header
    public string  $stripeId        = '';
    public string  $formattedAmount = '';
    public string  $status          = '';
    public string  $statusLabel     = '';
    public string  $statusBadgeClass = '';
    public ?string $customerName    = null;
    public ?string $customerEmail   = null;

    // Breakdown
    public string $formattedFees = '';
    public string $formattedNet  = '';

    // Payment method
    public ?string $paymentMethodId = null;
    public ?string $cardDisplay     = null;
    public ?string $cardExpiry      = null;
    public ?string $cardFingerprint = null;
    public ?string $cardType        = null;
    public ?string $cardIssuer      = null;
    public ?string $cardCountry     = null;
    public ?string $cvcCheckLabel   = null;
    public bool    $cvcCheckPassed  = false;
    public string  $cvcCheckClass   = 'text-[#a1a1aa]';
    public ?string $billingName     = null;
    public ?string $billingEmail    = null;
    public ?string $billingCountry  = null;

    // Purchase summary
    public ?string $subscriptionId  = null;
    public ?string $planName        = null;
    public ?string $priceId         = null;
    public ?string $invoiceNumber   = null;
    public ?string $paymentIntentId = null;

    // Timeline + date
    public array   $events     = [];
    public string  $date       = '';
    public array   $feeDetails = [];

    private GetTransactionDetailService $detailService;
    private AdminTransactionPresenter   $presenter;

    public function boot(
        GetTransactionDetailService $detailService,
        AdminTransactionPresenter   $presenter,
    ): void {
        $this->detailService = $detailService;
        $this->presenter     = $presenter;
    }

    #[On('open-transaction-panel')]
    public function openPanel(string $chargeId): void
    {
        $this->chargeId  = $chargeId;
        $this->panelOpen = true;
        $this->loadPanelData();
    }

    public function close(): void
    {
        $this->panelOpen = false;
        $this->chargeId  = '';
    }

    public function render(): View
    {
        return view('livewire.admin.transaction-detail-panel');
    }

    private function loadPanelData(): void
    {
        $detail = $this->detailService->execute($this->chargeId);

        if (! $detail) {
            $this->dispatch('toast', type: 'error', message: 'Transaction not found.');
            $this->panelOpen = false;
            return;
        }

        $vm = $this->presenter->presentDetail($detail);

        $this->stripeId         = $vm->stripeId;
        $this->formattedAmount  = $vm->formattedAmount;
        $this->status           = $vm->status;
        $this->statusLabel      = $vm->statusLabel;
        $this->statusBadgeClass = $vm->statusBadgeClass;
        $this->customerName     = $vm->customerName;
        $this->customerEmail    = $vm->customerEmail;
        $this->formattedFees    = $vm->formattedFees;
        $this->formattedNet     = $vm->formattedNet;
        $this->paymentMethodId  = $vm->paymentMethodId;
        $this->cardDisplay      = $vm->cardDisplay;
        $this->cardExpiry       = $vm->cardExpiry;
        $this->cardFingerprint  = $vm->cardFingerprint;
        $this->cardType         = $vm->cardType;
        $this->cardIssuer       = $vm->cardIssuer;
        $this->cardCountry      = $vm->cardCountry;
        $this->cvcCheckLabel    = $vm->cvcCheckLabel;
        $this->cvcCheckPassed   = $vm->cvcCheckPassed;
        $this->cvcCheckClass    = $vm->cvcCheckClass;
        $this->billingName      = $vm->billingName;
        $this->billingEmail     = $vm->billingEmail;
        $this->billingCountry   = $vm->billingCountry;
        $this->subscriptionId   = $vm->subscriptionId;
        $this->planName         = $vm->planName;
        $this->priceId          = $vm->priceId;
        $this->invoiceNumber    = $vm->invoiceNumber;
        $this->paymentIntentId  = $vm->paymentIntentId;
        $this->events           = $vm->events;
        $this->date             = $vm->date;
        $this->feeDetails       = $vm->feeDetails;
    }
}
