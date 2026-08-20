<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Contracts\SubscriptionProviderGatewayInterface;

class ImportStripeSubscriptionsService
{
    public function __construct(
        private readonly SubscriptionProviderGatewayInterface  $gateway,
        private readonly SubscriptionAdminRepositoryInterface  $repository,
    ) {}

    public function execute(): int
    {
        $subscriptions = $this->gateway->listAll();

        if (empty($subscriptions)) {
            return 0;
        }

        return $this->repository->insertMissing($subscriptions);
    }
}
