<?php

namespace App\Livewire;

use App\Application\Subscription\CreateCheckoutSessionService;
use App\Application\Subscription\GetAvailablePlansService;
use App\Application\Subscription\GetSubscriptionStatusService;
use App\Http\Presenters\BillingPresenter;
use Livewire\Component;

class BillingComponent extends Component
{
    private GetSubscriptionStatusService $statusService;
    private GetAvailablePlansService $plansService;
    private CreateCheckoutSessionService $checkoutService;

    public function boot(
        GetSubscriptionStatusService $statusService,
        GetAvailablePlansService $plansService,
        CreateCheckoutSessionService $checkoutService,
    ): void {
        $this->statusService  = $statusService;
        $this->plansService   = $plansService;
        $this->checkoutService = $checkoutService;
    }

    public function checkout(string $key): void
    {
        $result = $this->checkoutService->execute(
            userId:     auth()->id(),
            planKey:    $key,
            successUrl: route('billing'),
            cancelUrl:  route('billing'),
        );

        $this->redirect($result->checkoutUrl, navigate: false);
    }

    public function render()
    {
        $status = $this->statusService->execute(auth()->id());
        $plans  = $this->plansService->execute();

        return view('livewire.billing-component', [
            'presenter' => new BillingPresenter($status, $plans),
        ]);
    }
}
