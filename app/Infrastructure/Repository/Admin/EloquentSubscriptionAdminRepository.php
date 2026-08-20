<?php

namespace App\Infrastructure\Repository\Admin;

use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;
use App\Domain\Admin\Results\AdminSubscriptionResult;
use App\Models\Subscription;
use App\Models\User;

class EloquentSubscriptionAdminRepository implements SubscriptionAdminRepositoryInterface
{
    public function all(): array
    {
        return Subscription::query()
            ->with(['user', 'plan'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Subscription $sub) => $this->toResult($sub))
            ->all();
    }

    public function insertMissing(array $subscriptions): int
    {
        $inserted = 0;

        foreach ($subscriptions as $sub) {
            $user = User::where('stripe_id', $sub->providerCustomerId)->first();

            if (! $user) {
                continue;
            }

            $affected = Subscription::insertOrIgnore([
                'user_id'        => $user->id,
                'type'           => $sub->type,
                'stripe_id'      => $sub->providerSubscriptionId,
                'stripe_status'  => $sub->status,
                'stripe_price'   => $sub->priceId,
                'trial_ends_at'  => $sub->trialEndsAt,
                'ends_at'        => $sub->endsAt,
                'created_at'     => $sub->createdAt,
                'updated_at'     => now(),
            ]);

            $inserted += $affected;
        }

        return $inserted;
    }

    private function toResult(Subscription $sub): AdminSubscriptionResult
    {
        return new AdminSubscriptionResult(
            id:           $sub->id,
            stripeId:     $sub->stripe_id,
            status:       $sub->stripe_status,
            userName:     $sub->user->name,
            userEmail:    $sub->user->email,
            pmType:       $sub->user->pm_type,
            pmLastFour:   $sub->user->pm_last_four,
            planName:     $sub->plan?->name,
            planKey:      $sub->plan?->key,
            unitAmount:   $sub->plan?->unit_amount,
            currency:     $sub->plan?->currency,
            interval:     $sub->plan?->interval,
            subscribedAt: new \DateTimeImmutable($sub->created_at->toDateTimeString()),
            endsAt:       $sub->ends_at ? new \DateTimeImmutable($sub->ends_at->toDateTimeString()) : null,
        );
    }
}
