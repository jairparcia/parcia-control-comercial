<?php

namespace App\Infrastructure\Repository\Admin;

use App\Domain\Admin\Contracts\PlanAdminRepository;
use App\Domain\Admin\Entities\CreateAdminPlanInput;
use App\Domain\Admin\Entities\UpdateAdminPlanInput;
use App\Domain\Admin\Results\AdminPlanResult;
use App\Models\SubscriptionPlan;

class EloquentPlanAdminRepository implements PlanAdminRepository
{
    public function all(): array
    {
        return SubscriptionPlan::orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($p) => $this->toResult($p))
            ->all();
    }

    public function findById(int $id): AdminPlanResult
    {
        return $this->toResult(SubscriptionPlan::findOrFail($id));
    }

    public function create(
        CreateAdminPlanInput $input,
        ?string $stripeProductId,
        ?string $stripePriceId,
    ): AdminPlanResult {
        $plan = SubscriptionPlan::create([
            'key'                => $input->key,
            'name'               => $input->name,
            'description'        => $input->description,
            'features'           => $input->features,
            'stripe_product_id'  => $stripeProductId,
            'stripe_price_id'    => $stripePriceId,
            'unit_amount'        => $input->unitAmount,
            'currency'           => $input->currency,
            'interval'           => $input->interval,
            'quota'              => $input->quota,
            'sort_order'         => $input->sortOrder,
            'active'             => true,
        ]);

        return $this->toResult($plan);
    }

    public function update(
        int $id,
        UpdateAdminPlanInput $input,
        ?string $newStripePriceId = null,
    ): AdminPlanResult {
        $plan = SubscriptionPlan::findOrFail($id);

        $data = [
            'name'        => $input->name,
            'description' => $input->description,
            'features'    => $input->features,
            'unit_amount' => $input->unitAmount,
            'currency'    => $input->currency,
            'interval'    => $input->interval,
            'quota'       => $input->quota,
            'sort_order'  => $input->sortOrder,
        ];

        if ($newStripePriceId !== null) {
            $data['stripe_price_id'] = $newStripePriceId;
        }

        $plan->update($data);

        return $this->toResult($plan->fresh());
    }

    public function toggle(int $id): bool
    {
        $plan      = SubscriptionPlan::findOrFail($id);
        $newActive = ! $plan->active;
        $plan->update(['active' => $newActive]);

        return $newActive;
    }

    public function appendLegacyPriceId(int $id, string $oldPriceId): void
    {
        $plan    = SubscriptionPlan::findOrFail($id);
        $legacy  = $plan->legacy_stripe_price_ids ?? [];

        if (! in_array($oldPriceId, $legacy, strict: true)) {
            $legacy[] = $oldPriceId;
            $plan->update(['legacy_stripe_price_ids' => $legacy]);
        }
    }

    private function toResult(SubscriptionPlan $plan): AdminPlanResult
    {
        return new AdminPlanResult(
            id:             $plan->id,
            key:            $plan->key,
            name:           $plan->name,
            description:    $plan->description ?? '',
            features:       $plan->features ?? [],
            quota:          $plan->quota,
            unitAmount:     $plan->unit_amount,
            currency:       $plan->currency,
            interval:       $plan->interval,
            sortOrder:      $plan->sort_order,
            active:         $plan->active,
            stripePriceId:  $plan->stripe_price_id,
            stripeProductId:$plan->stripe_product_id,
        );
    }
}
