<?php

namespace App\Infrastructure\Repository\Admin;

use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;
use App\Domain\Admin\Results\AdminSubscriptionResult;
use App\Models\Subscription;
use App\Models\User;

class EloquentSubscriptionAdminRepository implements SubscriptionAdminRepositoryInterface
{
    public function all(string $statusFilter = 'active'): array
    {
        return Subscription::query()
            ->with(['user', 'plan'])
            ->when($statusFilter !== 'all', fn ($q) => $q->where('stripe_status', $statusFilter))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Subscription $sub) => $this->toResult($sub))
            ->all();
    }

    public function insertMissing(array $subscriptions): int
    {
        $inserted = 0;

        foreach ($subscriptions as $sub) {
            $user = $this->resolveUser($sub);

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

    public function markCanceled(string $stripeSubscriptionId): void
    {
        Subscription::where('stripe_id', $stripeSubscriptionId)
            ->update([
                'stripe_status' => 'canceled',
                'ends_at'       => now(),
            ]);
    }

    public function markScheduledCancellation(string $stripeSubscriptionId, \DateTimeImmutable $endsAt): void
    {
        Subscription::where('stripe_id', $stripeSubscriptionId)
            ->update(['ends_at' => $endsAt->format('Y-m-d H:i:s')]);
    }

    private function resolveUser(ProviderSubscriptionDataDTO $sub): ?User
    {
        $user = User::where('stripe_id', $sub->providerCustomerId)->first();

        if ($user) {
            return $user;
        }

        if (! $sub->customerEmail) {
            return null;
        }

        $user = User::where('email', $sub->customerEmail)->first();

        if ($user) {
            $user->update(['stripe_id' => $sub->providerCustomerId]);

            return $user;
        }

        return User::create([
            'name'      => $sub->customerName ?? $sub->customerEmail,
            'email'     => $sub->customerEmail,
            'stripe_id' => $sub->providerCustomerId,
            'role'      => 'external',
        ]);
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
