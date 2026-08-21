<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Subscription\Contracts\PaymentGateway;
use App\Domain\Subscription\Entities\CreateCheckoutSessionInput;
use App\Domain\Subscription\Enums\Plan;
use App\Domain\Subscription\Results\PlanInfo;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
    public function __construct(private readonly StripeClient $stripe) {}

    public function createCheckoutUrl(CreateCheckoutSessionInput $input): string
    {
        $user    = User::findOrFail($input->userId);
        $priceId = config("plans.{$input->plan->value}.price_id");

        return $user
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => $input->successUrl,
                'cancel_url'  => $input->cancelUrl,
            ])
            ->url;
    }

    public function billingPortalUrl(int $userId, string $returnUrl): string
    {
        return User::findOrFail($userId)->billingPortalUrl($returnUrl);
    }

    public function getAvailablePlans(): array
    {
        return Cache::remember('billing.available_plans', 3600, function () {
            $plans = [];

            foreach (Plan::cases() as $plan) {
                if ($plan === Plan::Internal) {
                    continue;
                }

                $priceId = config("plans.{$plan->value}.price_id");

                if (! $priceId) {
                    continue;
                }

                try {
                    $price = $this->stripe->prices->retrieve($priceId, ['expand' => ['product']]);

                    $plans[] = new PlanInfo(
                        key: $plan->value,
                        name: $price->product->name,
                        formattedPrice: $this->formatPrice($price->unit_amount, $price->currency),
                        interval: $price->recurring->interval,
                        currency: strtoupper($price->currency),
                    );
                } catch (\Throwable) {
                    // Price not found or Stripe unreachable — skip this plan gracefully
                }
            }

            return $plans;
        });
    }

    private function formatPrice(int $unitAmount, string $currency): string
    {
        // Stripe stores amount in smallest currency unit (centavos for MXN, cents for USD…)
        // Zero-decimal currencies are the exception
        $zeroDecimal = ['bif', 'clp', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'xaf', 'xof'];

        $amount = in_array(strtolower($currency), $zeroDecimal)
            ? $unitAmount
            : $unitAmount / 100;

        $symbols = ['mxn' => 'MX$', 'usd' => 'US$', 'eur' => '€', 'gbp' => '£'];
        $symbol  = $symbols[strtolower($currency)] ?? strtoupper($currency) . ' ';

        $formatted = ($amount == floor($amount))
            ? number_format((int) $amount)
            : number_format($amount, 2);

        return $symbol . $formatted;
    }
}
