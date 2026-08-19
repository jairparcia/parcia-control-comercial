<?php

namespace App\Infrastructure\Repository\Subscription;

use App\Domain\Subscription\Contracts\SubscriptionPlanRepository;
use App\Domain\Subscription\Results\PlanInfo;
use App\Models\SubscriptionPlan;
use RuntimeException;

class EloquentSubscriptionPlanRepository implements SubscriptionPlanRepository
{
    public function findAllActive(): array
    {
        return SubscriptionPlan::where('active', true)
            ->where('key', '!=', 'internal')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($plan) => new PlanInfo(
                key:            $plan->key,
                name:           $plan->name,
                formattedPrice: $this->formatPrice($plan->unit_amount, $plan->currency),
                interval:       $plan->interval,
                currency:       $plan->currency,
                quota:          $plan->quota,
                isFree:         $plan->unit_amount === 0,
                features:       $plan->features ?? [],
            ))
            ->all();
    }

    public function findStripePriceId(string $planKey): string
    {
        $record = SubscriptionPlan::where('key', $planKey)
            ->whereNotNull('stripe_price_id')
            ->first();

        if (! $record) {
            throw new RuntimeException("No active Stripe price found for plan [{$planKey}].");
        }

        return $record->stripe_price_id;
    }

    private function formatPrice(int $unitAmount, string $currency): string
    {
        $zeroDecimal = ['bif', 'clp', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'xaf', 'xof'];

        $amount = in_array(strtolower($currency), $zeroDecimal)
            ? $unitAmount
            : $unitAmount / 100;

        $symbols = ['mxn' => 'MX$', 'usd' => 'US$', 'eur' => '€', 'gbp' => '£'];
        $symbol  = $symbols[strtolower($currency)] ?? strtoupper($currency) . ' ';

        $formatted = ($amount == floor($amount))
            ? number_format((int) $amount)
            : number_format($amount, 2);

        return $amount === 0.0 ? 'Gratis' : $symbol . $formatted;
    }
}
