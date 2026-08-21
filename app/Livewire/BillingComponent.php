<?php

namespace App\Livewire;

use App\Application\Subscription\CreateCheckoutSessionService;
use App\Application\Subscription\GetAvailablePlansService;
use App\Application\Subscription\GetSubscriptionStatusService;
use App\Domain\Subscription\Entities\CreateCheckoutSessionInput;
use App\Domain\Subscription\Enums\Plan;
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
        $input = new CreateCheckoutSessionInput(
            userId:     auth()->id(),
            plan:       Plan::from($key),
            successUrl: route('billing'),
            cancelUrl:  route('billing'),
        );

        $result = $this->checkoutService->execute($input);

        $this->redirect($result->checkoutUrl);
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
