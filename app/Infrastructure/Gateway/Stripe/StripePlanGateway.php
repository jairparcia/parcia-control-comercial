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

        $this->stripe->products->update($productId, ['default_price' => $newPrice->id]);

        return $newPrice->id;
    }

    public function addPriceToProduct(string $productId, int $unitAmount, string $currency, string $interval): string
    {
        $price = $this->stripe->prices->create([
            'product'     => $productId,
            'unit_amount' => $unitAmount,
            'currency'    => strtolower($currency),
            'recurring'   => ['interval' => $interval],
        ]);

        $this->stripe->products->update($productId, ['default_price' => $price->id]);

        return $price->id;
    }

    public function deactivatePlan(string $priceId): void
    {
        $this->stripe->prices->update($priceId, ['active' => false]);
    }

    public function listProductPrices(string $productId): array
    {
        $response = $this->stripe->prices->all([
            'product' => $productId,
            'limit'   => 100,
        ]);

        return array_map(fn ($price) => $price->toArray(), $response->data);
    }

    public function retrievePrice(string $priceId): array
    {
        return $this->stripe->prices->retrieve($priceId)->toArray();
    }

    public function archivePrice(string $priceId): void
    {
        $this->stripe->prices->update($priceId, ['active' => false]);
    }

    public function setDefaultPrice(string $productId, string $priceId): void
    {
        $this->stripe->products->update($productId, ['default_price' => $priceId]);
    }
}
