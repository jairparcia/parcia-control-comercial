<?php

namespace App\Http\Presenters\Admin;

use App\Domain\Admin\Results\AdminPlanResult;

class AdminPlanPresenter
{
    public function present(AdminPlanResult $plan): AdminPlanViewModel
    {
        $isFree = $plan->unitAmount === 0;

        $symbol = match ($plan->currency) {
            'MXN'   => 'MX$',
            'USD'   => 'US$',
            default => $plan->currency . ' ',
        };

        return new AdminPlanViewModel(
            id:                $plan->id,
            key:               $plan->key,
            name:              $plan->name,
            description:       $plan->description,
            features:          $plan->features,
            quota:             $plan->quota,
            unitAmount:        $plan->unitAmount,
            currency:          $plan->currency,
            interval:          $plan->interval,
            sortOrder:         $plan->sortOrder,
            active:            $plan->active,
            stripePriceId:     $plan->stripePriceId,
            stripeProductId:   $plan->stripeProductId,
            isFree:            $isFree,
            formattedPrice:    $isFree ? 'Free' : $symbol . number_format($plan->unitAmount / 100, 0, '.', ','),
            formattedQuota:    number_format($plan->quota),
            formattedInterval: $plan->interval === 'month' ? 'mo' : 'yr',
            statusLabel:       $plan->active ? 'Active' : 'Inactive',
            statusButtonClass: $plan->active
                ? 'bg-emerald-900/30 text-emerald-400 hover:bg-emerald-900/50'
                : 'bg-[#27272a] text-[#71717a] hover:bg-[#3f3f46]',
            statusDotClass:    $plan->active ? 'bg-emerald-400' : 'bg-[#52525b]',
        );
    }

    /** @return AdminPlanViewModel[] */
    public function presentAll(array $plans): array
    {
        return array_map(fn ($plan) => $this->present($plan), $plans);
    }

    public function modalTitle(bool $isEditing): string
    {
        return $isEditing ? 'Edit plan' : 'New plan';
    }

    public function keyFieldClass(bool $isEditing): string
    {
        return $isEditing ? 'opacity-40 cursor-not-allowed' : '';
    }
}
