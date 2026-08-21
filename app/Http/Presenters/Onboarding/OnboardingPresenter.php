<?php

namespace App\Http\Presenters\Onboarding;

use App\Domain\Subscription\Results\PlanInfo;

class OnboardingPresenter
{
    /** @param PlanInfo[] $plans @return OnboardingPlanViewModel[] */
    public function presentPlans(array $plans): array
    {
        return array_map(fn ($plan) => $this->present($plan), $plans);
    }

    private function present(PlanInfo $plan): OnboardingPlanViewModel
    {
        $isPro = $plan->key === 'pro';

        return new OnboardingPlanViewModel(
            key:                    $plan->key,
            name:                   $plan->name,
            formattedPrice:         $plan->formattedPrice,
            formattedIntervalLabel: $plan->interval === 'year' ? 'año' : 'mes',
            isFree:                 $plan->isFree,
            isPro:                  $isPro,
            features:               $plan->features,
            cardBorderClass:        $isPro
                ? 'border-[#5b69e2] ring-1 ring-[#5b69e2]'
                : 'border-[#e2e4ea]',
            featureCheckClass:      $plan->isFree ? 'text-[#9ca3af]' : 'text-[#16a34a]',
            buttonClass:            $isPro
                ? 'bg-[#5b69e2] text-white hover:bg-[#4a58d0]'
                : 'bg-[#1a1f36] text-white hover:bg-[#2d3452]',
        );
    }

    public function planCount(array $plans): int
    {
        return count($plans);
    }

    public function userName(): string
    {
        return auth()->user()?->name ?? 'Usuario';
    }
}
