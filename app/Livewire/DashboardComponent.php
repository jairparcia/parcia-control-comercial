<?php

namespace App\Livewire;

use App\Application\Subscription\GetAvailablePlansService;
use App\Application\Subscription\GetSubscriptionStatusService;
use App\Http\Presenters\DashboardPresenter;
use Livewire\Component;

class DashboardComponent extends Component
{
    private GetSubscriptionStatusService $statusService;
    private GetAvailablePlansService     $plansService;

    public function boot(GetSubscriptionStatusService $statusService, GetAvailablePlansService $plansService): void
    {
        $this->statusService = $statusService;
        $this->plansService  = $plansService;
    }

    public function render()
    {
        $status = $this->statusService->execute(auth()->id());

        return view('livewire.dashboard-component', [
            'presenter' => new DashboardPresenter($status, $this->plansService->execute()),
        ]);
    }
}
