<?php

namespace App\Livewire;

use App\Application\Subscription\GetSubscriptionStatusService;
use App\Http\Presenters\DashboardPresenter;
use Livewire\Component;

class DashboardComponent extends Component
{
    private GetSubscriptionStatusService $statusService;

    public function boot(GetSubscriptionStatusService $statusService): void
    {
        $this->statusService = $statusService;
    }

    public function render()
    {
        $status = $this->statusService->execute(auth()->id());

        return view('livewire.dashboard-component', [
            'presenter' => new DashboardPresenter($status),
        ]);
    }
}
