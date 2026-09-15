<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Entities\StripeCustomerDataDTO;
use Stripe\StripeClient;

class StripeCustomerGateway implements CustomerProviderGatewayInterface
{
    public function __construct(
        private readonly StripeClient $client,
    ) {}

    public function listAll(): array
    {
        $results = [];
        $params  = ['limit' => 100];

        do {
            $page = $this->client->customers->all($params);

            foreach ($page->data as $customer) {
                if (! $customer->email) {
                    continue;
                }

                $country = $customer->address?->country
                    ?? $customer->shipping?->address?->country
                    ?? null;

                $results[] = new StripeCustomerDataDTO(
                    providerCustomerId: $customer->id,
                    email:              $customer->email,
                    name:               $customer->name ?: null,
                    description:        $customer->description ?: null,
                    country:            $country,
                    createdAt:          new \DateTimeImmutable('@' . $customer->created),
                );
            }

            $params['starting_after'] = $page->data ? end($page->data)->id : null;
        } while ($page->has_more);

        return $results;
    }
}
