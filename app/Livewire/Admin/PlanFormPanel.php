<?php

namespace App\Livewire\Admin;

use App\Application\Admin\CreateAdminPlanService;
use App\Application\Admin\ListAdminPlansService;
use App\Application\Admin\UpdateAdminPlanService;
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
    public int    $formUnitAmount  = 0;
    public string $formCurrency    = 'MXN';
    public string $formInterval    = 'month';
    public int    $formSortOrder   = 0;

    private ListAdminPlansService  $listService;
    private CreateAdminPlanService $createService;
    private UpdateAdminPlanService $updateService;
    private AdminPlanPresenter     $presenter;

    public function boot(
        ListAdminPlansService  $listService,
        CreateAdminPlanService $createService,
        UpdateAdminPlanService $updateService,
        AdminPlanPresenter     $presenter,
    ): void {
        $this->listService   = $listService;
        $this->createService = $createService;
        $this->updateService = $updateService;
        $this->presenter     = $presenter;
    }

    #[On('open-plan-form')]
    public function openPanel(?int $id): void
    {
        if ($id === null) {
            $this->reset(['formName', 'formKey', 'formDescription', 'formFeatures', 'formSortOrder', 'editingId']);
            $this->formQuota      = 100;
            $this->formUnitAmount = 0;
            $this->formCurrency   = 'MXN';
            $this->formInterval   = 'month';
            $this->isEditing      = false;
        } else {
            $plan = collect($this->listService->execute())->firstWhere('id', $id);

            if (! $plan) {
                return;
            }

            $this->formName        = $plan->name;
            $this->formKey         = $plan->key;
            $this->formDescription = $plan->description;
            $this->formFeatures    = implode("\n", $plan->features);
            $this->formQuota       = $plan->quota;
            $this->formUnitAmount  = intval($plan->unitAmount / 100);
            $this->formCurrency    = $plan->currency;
            $this->formInterval    = $plan->interval;
            $this->formSortOrder   = $plan->sortOrder;
            $this->isEditing       = true;
            $this->editingId       = $id;
        }

        $this->resetValidation();
        $this->panelOpen = true;
    }

    public function close(): void
    {
        $this->panelOpen = false;
    }

    public function save(): void
    {
        if ($this->isEditing) {
            $this->validate([
                'formName'       => 'required|min:2|max:100',
                'formQuota'      => 'required|integer|min:0',
                'formUnitAmount' => 'required|integer|min:0',
                'formCurrency'   => 'required|in:MXN,USD',
                'formInterval'   => 'required|in:month,year',
                'formSortOrder'  => 'required|integer|min:0',
            ]);

            try {
                $this->updateService->execute(
                    planId:      $this->editingId,
                    name:        $this->formName,
                    description: $this->formDescription,
                    features:    $this->parseFeatures(),
                    quota:       $this->formQuota,
                    unitAmount:  $this->formUnitAmount * 100,
                    currency:    $this->formCurrency,
                    interval:    $this->formInterval,
                    sortOrder:   $this->formSortOrder,
                );

                $this->panelOpen = false;
                $this->dispatch('plan-saved');
                $this->dispatch('toast', message: 'Plan updated successfully.', type: 'success');
            } catch (\Throwable $e) {
                $this->dispatch('toast', message: 'Could not save plan: ' . $e->getMessage(), type: 'error');
            }
        } else {
            $this->validate([
                'formName'       => 'required|min:2|max:100',
                'formKey'        => 'required|alpha_dash|max:30|unique:plans,key',
                'formQuota'      => 'required|integer|min:0',
                'formUnitAmount' => 'required|integer|min:0',
                'formCurrency'   => 'required|in:MXN,USD',
                'formInterval'   => 'required|in:month,year',
                'formSortOrder'  => 'required|integer|min:0',
            ]);

            try {
                $this->createService->execute(
                    name:        $this->formName,
                    key:         $this->formKey,
                    description: $this->formDescription,
                    features:    $this->parseFeatures(),
                    quota:       $this->formQuota,
                    unitAmount:  $this->formUnitAmount * 100,
                    currency:    $this->formCurrency,
                    interval:    $this->formInterval,
                    sortOrder:   $this->formSortOrder,
                );

                $this->panelOpen = false;
                $this->dispatch('plan-saved');
                $this->dispatch('toast', message: 'Plan created successfully.', type: 'success');
            } catch (\Throwable $e) {
                $this->dispatch('toast', message: 'Could not create plan: ' . $e->getMessage(), type: 'error');
            }
        }
    }

    private function parseFeatures(): array
    {
        return array_values(array_filter(
            array_map('trim', explode("\n", $this->formFeatures)),
        ));
    }

    public function render()
    {
        return view('livewire.admin.plan-form-panel', [
            'modalTitle'    => $this->presenter->modalTitle($this->isEditing),
            'keyFieldClass' => $this->presenter->keyFieldClass($this->isEditing),
        ]);
    }
}
