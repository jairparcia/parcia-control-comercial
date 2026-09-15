<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Contracts\SubscriptionProviderGatewayInterface;
use App\Domain\Admin\Entities\CancelSubscriptionInputDTO;

class CancelSubscriptionService
{
    public function __construct(
        private readonly SubscriptionProviderGatewayInterface $gateway,
        private readonly SubscriptionAdminRepositoryInterface  $repository,
    ) {}

    public function execute(string $stripeSubscriptionId, bool $immediately, string $refundType): void
    {
        $input = new CancelSubscriptionInputDTO(
            stripeSubscriptionId: $stripeSubscriptionId,
            immediately:          $immediately,
            refundType:           $immediately ? $refundType : 'none',
        );

        $result = $this->gateway->cancel($input);

        if ($result->immediate) {
            $this->repository->markCanceled($stripeSubscriptionId);
        } elseif ($result->scheduledEndsAt) {
            $this->repository->markScheduledCancellation($stripeSubscriptionId, $result->scheduledEndsAt);
        }
    }
}
