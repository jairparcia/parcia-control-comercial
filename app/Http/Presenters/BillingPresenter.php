<?php

namespace App\Http\Presenters;

use App\Domain\Subscription\Results\PlanInfo;
use App\Domain\Subscription\Results\SubscriptionStatusResult;

class BillingPresenter
{
    /**
     * @param PlanInfo[] $availablePlans
     */
    public function __construct(
        private readonly SubscriptionStatusResult $status,
        private readonly array $availablePlans = [],
    ) {}

    // ── Subscription state ────────────────────────────────────────────

    public function hasSubscription(): bool
    {
        return $this->status->plan !== null;
    }

    public function planLabel(): string
    {
        return $this->status->plan
            ? 'Plan ' . $this->status->plan->label()
            : 'Sin plan';
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function statusLabel(): string
    {
        return $this->status->status->label();
    }

    public function statusColor(): string
    {
        return match ($this->status->status->value) {
            'active', 'trialing' => 'text-[#16a34a]',
            'past_due'           => 'text-[#d97706]',
            default              => 'text-[#dc2626]',
        };
    }

    public function statusBg(): string
    {
        return match ($this->status->status->value) {
            'active', 'trialing' => 'bg-[#dcfce7]',
            'past_due'           => 'bg-[#fef3c7]',
            default              => 'bg-[#fee2e2]',
        };
    }

    public function renewsAt(): string
    {
        return $this->status->renewsAt ?? '—';
    }

    public function hasPaymentMethod(): bool
    {
        return filled($this->status->pmType);
    }

    public function pmType(): string
    {
        return $this->status->pmType ?? '';
    }

    public function pmLastFour(): string
    {
        return $this->status->pmLastFour ?? '';
    }

    // ── Plans ─────────────────────────────────────────────────────────
    // Names and prices come from the payment provider (via PlanInfo).
    // Features are UI/marketing concerns — kept here as the presenter owns display logic.

    public function plans(): array
    {
        $paidPlans  = array_values(array_filter($this->availablePlans, fn ($p) => ! $p->isFree));
        $currentKey = $this->status->plan?->value;
        $keys       = array_column($paidPlans, 'key');
        $highlight  = $this->resolveHighlight($keys);

        return array_map(function (PlanInfo $plan) use ($currentKey, $highlight) {
            return [
                'key'       => $plan->key,
                'name'      => $plan->name,
                'price'     => $plan->formattedPrice,
                'period'    => $plan->interval === 'year' ? 'año' : 'mes',
                'quota'     => number_format($plan->quota) . ' escaneos/mes',
                'features'  => $plan->features,
                'current'   => $plan->key === $currentKey,
                'highlight' => $plan->key === $highlight,
            ];
        }, $paidPlans);
    }

    private function resolveHighlight(array $keys): ?string
    {
        if (in_array('pro', $keys)) {
            return 'pro';
        }

        return $keys[1] ?? $keys[0] ?? null;
    }
}
