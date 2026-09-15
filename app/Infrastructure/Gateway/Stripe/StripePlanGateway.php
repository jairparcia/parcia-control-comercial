<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Admin\Contracts\PlanProviderGatewayInterface;
use App\Domain\Admin\Results\ProviderPlanIds;
use Stripe\StripeClient;

class StripePlanGateway implements PlanProviderGatewayInterface
{
    public function __construct(
        private readonly StripeClient $stripe,
    ) {}

    public function createPlan(string $name, int $unitAmount, string $currency, string $interval): ProviderPlanIds
    {
        $product = $this->stripe->products->create(['name' => $name]);

        $price = $this->stripe->prices->create([
            'product'    => $product->id,
            'unit_amount' => $unitAmount,
            'currency'   => strtolower($currency),
            'recurring'  => ['interval' => $interval],
        ]);

        return new ProviderPlanIds(
            productId: $product->id,
            priceId:   $price->id,
        );
    }

    public function updatePlanName(string $productId, string $name): void
    {
        $this->stripe->products->update($productId, ['name' => $name]);
    }

    public function replacePlanPrice(
        string $productId,
        string $oldPriceId,
        int $unitAmount,
        string $currency,
        string $interval,
    ): string {
        $newPrice = $this->stripe->prices->create([
            'product'     => $productId,
            'unit_amount' => $unitAmount,
            'currency'    => strtolower($currency),
            'recurring'   => ['interval' => $interval],
        ]);

        $this->stripe->prices->update($oldPriceId, ['active' => false]);

        return $newPrice->id;
    }

    public function deactivatePlan(string $priceId): void
    {
        $this->stripe->prices->update($priceId, ['active' => false]);
    }
}
