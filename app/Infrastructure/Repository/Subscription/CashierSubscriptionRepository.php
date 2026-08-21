<?php

namespace App\Infrastructure\Repository\Subscription;

use App\Domain\Subscription\Contracts\SubscriptionRepository;
use App\Domain\Subscription\Enums\Plan;
use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Domain\Subscription\Results\SubscriptionStatusResult;
use App\Models\User;

class CashierSubscriptionRepository implements SubscriptionRepository
{
    public function getStatus(int $userId): SubscriptionStatusResult
    {
        $user = User::findOrFail($userId);

        if ($user->isInternal()) {
            return new SubscriptionStatusResult(
                plan: Plan::Internal,
                status: SubscriptionStatus::Active,
                renewsAt: null,
                cancelledAt: null,
                pmType: null,
                pmLastFour: null,
            );
        }

        $subscription = $user->subscription();

        if (! $subscription) {
            return new SubscriptionStatusResult(
                plan: Plan::Free,
                status: SubscriptionStatus::Active,
                renewsAt: null,
                cancelledAt: null,
                pmType: null,
                pmLastFour: null,
            );
        }

        return new SubscriptionStatusResult(
            plan: $this->resolvePlan($subscription->stripe_price),
            status: $this->mapStatus($subscription->stripe_status),
            renewsAt: $subscription->ends_at?->format('d M Y'),
            cancelledAt: $subscription->canceled() ? $subscription->ends_at?->format('d M Y') : null,
            pmType: $user->pm_type,
            pmLastFour: $user->pm_last_four,
        );
    }

    public function isActive(int $userId): bool
    {
        // Every registered user is active on at least the free plan.
        // Quota enforcement happens in CheckQuotaService, not here.
        return true;
    }

    private function mapStatus(?string $stripeStatus): SubscriptionStatus
    {
        return match ($stripeStatus) {
            'active'              => SubscriptionStatus::Active,
            'trialing'            => SubscriptionStatus::Trialing,
            'past_due'            => SubscriptionStatus::PastDue,
            'canceled','cancelled' => SubscriptionStatus::Cancelled,
            default               => SubscriptionStatus::None,
        };
    }

    private function resolvePlan(?string $stripePrice): ?Plan
    {
        if (! $stripePrice) {
            return null;
        }

        foreach (Plan::cases() as $plan) {
            if (config("plans.{$plan->value}.price_id") === $stripePrice) {
                return $plan;
            }
        }

        return null;
    }
}
