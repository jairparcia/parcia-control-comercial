<?php

namespace App\Livewire;

use App\Application\Subscription\CreateCheckoutSessionService;
use App\Application\Subscription\GetAvailablePlansService;
use App\Domain\Auth\Contracts\UserRepository;
use App\Http\Presenters\Onboarding\OnboardingPresenter;
use Livewire\Component;

class OnboardingComponent extends Component
{
    private GetAvailablePlansService $plansService;
    private CreateCheckoutSessionService $checkoutService;
    private UserRepository $users;
    private OnboardingPresenter $presenter;

    public function boot(
        GetAvailablePlansService $plansService,
        CreateCheckoutSessionService $checkoutService,
        UserRepository $users,
        OnboardingPresenter $presenter,
    ): void {
        $this->plansService    = $plansService;
        $this->checkoutService = $checkoutService;
        $this->users           = $users;
        $this->presenter       = $presenter;
    }

    public function startFree(): void
    {
        $this->users->markOnboarded(auth()->id());
        $this->redirect(route('dashboard'), navigate: true);
    }

    public function choosePlan(string $key): void
    {
        $result = $this->checkoutService->execute(
            userId:     auth()->id(),
            planKey:    $key,
            successUrl: route('dashboard'),
            cancelUrl:  route('onboarding'),
        );

        $this->redirect($result->checkoutUrl);
    }

    public function render()
    {
        $rawPlans = $this->plansService->execute();

        return view('livewire.onboarding-component', [
            'plans'     => $this->presenter->presentPlans($rawPlans),
            'planCount' => $this->presenter->planCount($rawPlans),
            'userName'  => $this->presenter->userName(),
        ]);
    }
}
