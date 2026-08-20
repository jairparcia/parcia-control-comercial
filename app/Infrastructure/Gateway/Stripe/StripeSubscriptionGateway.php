<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Admin\Contracts\SubscriptionProviderGatewayInterface;
use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;
use Stripe\StripeClient;

class StripeSubscriptionGateway implements SubscriptionProviderGatewayInterface
{
    public function __construct(
        private readonly StripeClient $client,
    ) {}

    public function listAll(): array
    {
        $results = [];
        $params  = ['limit' => 100, 'expand' => ['data.customer']];

        do {
            $page = $this->client->subscriptions->all($params);

            foreach ($page->data as $sub) {
                $priceId = $sub->items->data[0]->price->id ?? null;

                $results[] = new ProviderSubscriptionDataDTO(
                    providerSubscriptionId: $sub->id,
                    providerCustomerId:     is_string($sub->customer) ? $sub->customer : $sub->customer->id,
                    status:                 $sub->status,
                    priceId:                $priceId,
                    type:                   'default',
                    trialEndsAt:            $sub->trial_end ? new \DateTimeImmutable('@' . $sub->trial_end) : null,
                    endsAt:                 $sub->cancel_at ? new \DateTimeImmutable('@' . $sub->cancel_at) : null,
                    createdAt:              new \DateTimeImmutable('@' . $sub->created),
                );
            }

            $params['starting_after'] = $page->data ? end($page->data)->id : null;
        } while ($page->has_more);

        return $results;
    }
}
