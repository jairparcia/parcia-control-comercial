<?php

namespace App\Livewire\Admin;

use App\Application\Admin\AdminPlanService;
use App\Http\Presenters\Admin\AdminPlanPresenter;
use Livewire\Attributes\On;
use Livewire\Component;

class PlansComponent extends Component
{
    private AdminPlanService  $planService;
    private AdminPlanPresenter $presenter;

    public function boot(
        AdminPlanService   $planService,
        AdminPlanPresenter $presenter,
    ): void {
        $this->planService = $planService;
        $this->presenter   = $presenter;
    }

    public function toggle(int $id): void
    {
        try {
            $active = $this->planService->toggle($id);
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
            'plans' => $this->presenter->presentAll($this->planService->list()),
        ]);
    }
}
