<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;
use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Contracts\SubscriptionProviderGatewayInterface;

class SyncCustomerFromStripeService
{
    public function __construct(
        private readonly CustomerAdminRepositoryInterface     $customers,
        private readonly CustomerProviderGatewayInterface     $customerGateway,
        private readonly SubscriptionAdminRepositoryInterface $subscriptions,
        private readonly SubscriptionProviderGatewayInterface $subscriptionGateway,
    ) {}

    public function execute(int $userId): void
    {
        $user = $this->customers->findById($userId);

        if (! $user) {
            return;
        }

        if (! $user->stripe_id) {
            $stripeCustomer = $this->customerGateway->findByEmail($user->email);

            if ($stripeCustomer) {
                $this->customers->insertMissing([$stripeCustomer]);
                $user = $this->customers->findById($userId);
            }
        }

        if ($user?->stripe_id) {
            $subs = $this->subscriptionGateway->listByCustomerId($user->stripe_id);

            if (! empty($subs)) {
                $this->subscriptions->insertMissing($subs);
            }
        }
    }
}
