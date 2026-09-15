<?php

namespace App\Livewire\Admin;

use App\Application\Admin\ListAdminPlansService;
use App\Application\Admin\ToggleAdminPlanService;
use App\Http\Presenters\Admin\AdminPlanPresenter;
use Livewire\Attributes\On;
use Livewire\Component;

class PlansComponent extends Component
{
    private ListAdminPlansService  $listService;
    private ToggleAdminPlanService $toggleService;
    private AdminPlanPresenter     $presenter;

    public function boot(
        ListAdminPlansService  $listService,
        ToggleAdminPlanService $toggleService,
        AdminPlanPresenter     $presenter,
    ): void {
        $this->listService   = $listService;
        $this->toggleService = $toggleService;
        $this->presenter     = $presenter;
    }

    public function toggle(int $id): void
    {
        try {
            $active = $this->toggleService->execute($id);
            $this->dispatch('toast',
                message: $active ? 'Plan activated.' : 'Plan deactivated.',
                type: 'info',
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Could not toggle plan: ' . $e->getMessage(), type: 'error');
        }
    }

    #[On('plan-saved')]
    public function handlePlanSaved(): void {}

    public function render()
    {
        return view('livewire.admin.plans-component', [
            'plans' => $this->presenter->presentAll($this->listService->execute()),
        ]);
    }
}
