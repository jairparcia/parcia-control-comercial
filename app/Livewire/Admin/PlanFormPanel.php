<?php

namespace App\Livewire\Admin;

use App\Application\Admin\AdminPlanService;
use App\Http\Presenters\Admin\AdminPlanPresenter;
use Livewire\Attributes\On;
use Livewire\Component;

class PlanFormPanel extends Component
{
    public bool $panelOpen = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $formName        = '';
    public string $formKey         = '';
    public string $formDescription = '';
    public string $formFeatures    = '';
    public int    $formQuota       = 100;
    public int    $formSortOrder   = 0;

    public array  $prices           = [];
    public bool   $showPriceForm    = false;
    public int    $newPriceAmount   = 0;
    public string $newPriceCurrency = 'MXN';
    public string $newPriceInterval = 'month';

    private AdminPlanService   $planService;
    private AdminPlanPresenter $presenter;

    public function boot(
        AdminPlanService   $planService,
        AdminPlanPresenter $presenter,
    ): void {
        $this->planService = $planService;
        $this->presenter   = $presenter;
    }

    #[On('open-plan-form')]
    public function openPanel(?int $id): void
    {
        if ($id === null) {
            $this->reset(['formName', 'formKey', 'formDescription', 'formFeatures', 'formSortOrder', 'editingId', 'prices', 'showPriceForm', 'newPriceAmount', 'newPriceCurrency', 'newPriceInterval']);
            $this->formQuota = 100;
            $this->isEditing = false;
        } else {
            $plan = collect($this->planService->list())->firstWhere('id', $id);

            if (! $plan) {
                return;
            }

            $this->formName        = $plan->name;
            $this->formKey         = $plan->key;
            $this->formDescription = $plan->description;
            $this->formFeatures    = implode("\n", $plan->features);
            $this->formQuota       = $plan->quota;
            $this->formSortOrder   = $plan->sortOrder;
            $this->isEditing       = true;
            $this->editingId       = $id;

            $this->loadPrices();
        }

        $this->resetValidation();
        $this->panelOpen = true;
    }

    public function loadPrices(): void
    {
        if (! $this->isEditing || ! $this->editingId) {
            $this->prices = [];
            return;
        }

        try {
            $results = $this->planService->listPrices($this->editingId);

            $this->prices = array_map(fn ($p) => [
                'stripeId'      => $p->stripeId,
                'amount'        => $this->formatAmount($p->unitAmountCents, $p->currency),
                'interval'      => $p->interval === 'month' ? 'Mensual' : 'Anual',
                'isDefault'     => $p->isDefault,
                'isActive'      => $p->isActive,
                'subscriptions' => $p->activeSubscriptionsCount,
                'createdAt'     => $p->createdAt->format('d M Y'),
            ], $results);
        } catch (\Throwable) {
            $this->prices = [];
        }
    }

    public function archivePrice(string $stripePriceId): void
    {
        try {
            $this->planService->archivePrice($this->editingId, $stripePriceId);
            $this->loadPrices();
            $this->dispatch('toast', message: __('admin.price_archived'), type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function setDefaultPrice(string $stripePriceId): void
    {
        try {
            $this->planService->setDefaultPrice($this->editingId, $stripePriceId);
            $this->loadPrices();
            $this->dispatch('plan-saved');
            $this->dispatch('toast', message: __('admin.default_price_updated'), type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function openPriceForm(): void
    {
        $this->showPriceForm    = true;
        $this->newPriceAmount   = 0;
        $this->newPriceCurrency = 'MXN';
        $this->newPriceInterval = 'month';
    }

    public function cancelPriceForm(): void
    {
        $this->showPriceForm = false;
    }

    public function addPrice(): void
    {
        $this->validate([
            'newPriceAmount'   => 'required|integer|min:1',
            'newPriceCurrency' => 'required|in:MXN,USD',
            'newPriceInterval' => 'required|in:month,year',
        ]);

        try {
            $this->planService->addPrice(
                $this->editingId,
                $this->newPriceAmount * 100,
                $this->newPriceCurrency,
                $this->newPriceInterval,
            );

            $this->showPriceForm = false;
            $this->loadPrices();
            $this->dispatch('plan-saved');
            $this->dispatch('toast', message: __('admin.price_added'), type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function close(): void
    {
        $this->panelOpen = false;
    }

    public function save(): void
    {
        if ($this->isEditing) {
            $this->validate([
                'formName'      => 'required|min:2|max:100',
                'formQuota'     => 'required|integer|min:0',
                'formSortOrder' => 'required|integer|min:0',
            ]);

            try {
                $this->planService->update(
                    planId:      $this->editingId,
                    name:        $this->formName,
                    description: $this->formDescription,
                    features:    $this->parseFeatures(),
                    quota:       $this->formQuota,
                    sortOrder:   $this->formSortOrder,
                );

                $this->panelOpen = false;
                $this->dispatch('plan-saved');
                $this->dispatch('toast', message: __('admin.plan_updated'), type: 'success');
            } catch (\Throwable $e) {
                $this->dispatch('toast', message: $e->getMessage(), type: 'error');
            }
        } else {
            $this->validate([
                'formName'      => 'required|min:2|max:100',
                'formKey'       => 'required|alpha_dash|max:30|unique:plans,key',
                'formQuota'     => 'required|integer|min:0',
                'formSortOrder' => 'required|integer|min:0',
            ]);

            try {
                $this->planService->create(
                    name:        $this->formName,
                    key:         $this->formKey,
                    description: $this->formDescription,
                    features:    $this->parseFeatures(),
                    quota:       $this->formQuota,
                    unitAmount:  0,
                    currency:    'MXN',
                    interval:    'month',
                    sortOrder:   $this->formSortOrder,
                );

                $this->panelOpen = false;
                $this->dispatch('plan-saved');
                $this->dispatch('toast', message: __('admin.plan_created'), type: 'success');
            } catch (\Throwable $e) {
                $this->dispatch('toast', message: $e->getMessage(), type: 'error');
            }
        }
    }

    private function parseFeatures(): array
    {
        return array_values(array_filter(
            array_map('trim', explode("\n", $this->formFeatures)),
        ));
    }

    private function formatAmount(int $cents, string $currency): string
    {
        $symbol = strtoupper($currency) === 'USD' ? 'US$' : 'MX$';
        return $symbol . number_format($cents / 100, 2);
    }

    public function render()
    {
        return view('livewire.admin.plan-form-panel', [
            'modalTitle'    => $this->presenter->modalTitle($this->isEditing),
            'keyFieldClass' => $this->presenter->keyFieldClass($this->isEditing),
        ]);
    }
}
