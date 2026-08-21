<?php

namespace App\Livewire;

use App\Application\Subscription\CreateCheckoutSessionService;
use App\Application\Subscription\GetAvailablePlansService;
use App\Domain\Auth\Contracts\UserRepository;
use App\Domain\Subscription\Entities\CreateCheckoutSessionInput;
use App\Domain\Subscription\Enums\Plan;
use App\Domain\Subscription\Results\PlanInfo;
use Livewire\Component;

class OnboardingComponent extends Component
{
    private GetAvailablePlansService $plansService;
    private CreateCheckoutSessionService $checkoutService;
    private UserRepository $users;

    public function boot(
        GetAvailablePlansService $plansService,
        CreateCheckoutSessionService $checkoutService,
        UserRepository $users,
    ): void {
        $this->plansService    = $plansService;
        $this->checkoutService = $checkoutService;
        $this->users           = $users;
    }

    public function startFree(): void
    {
        $this->users->markOnboarded(auth()->id());
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function choosePlan(string $key): void
    {
        $plan = Plan::from($key);

        $input = new CreateCheckoutSessionInput(
            userId:     auth()->id(),
            plan:       $plan,
            successUrl: route('dashboard'),
            cancelUrl:  route('onboarding'),
        );

        $result = $this->checkoutService->execute($input);

        $this->redirect($result->checkoutUrl);
    }

    public function render()
    {
        $freePlan = new PlanInfo(
            key: 'free',
            name: 'Gratuito',
            formattedPrice: 'MX$0',
            interval: 'month',
            currency: 'MXN',
        );

        $plans = [$freePlan, ...$this->plansService->execute()];

        return view('livewire.onboarding-component', compact('plans'));
    }
}
