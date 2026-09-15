<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Auth\Contracts\UserRepositoryInterface;
use App\Domain\Subscription\Contracts\PaymentGatewayInterface;
use App\Domain\Subscription\Contracts\SubscriptionPlanRepositoryInterface;
use App\Domain\Subscription\Entities\CreateCheckoutSessionInputDTO;

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly SubscriptionPlanRepositoryInterface $plans,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function createCheckoutUrl(CreateCheckoutSessionInputDTO $input): string
    {
        $stripePriceId = $this->plans->findStripePriceId($input->planKey);
        $user          = $this->users->findById($input->userId);

        if ($user->subscribed('default')) {
            $user->subscription('default')->swap($stripePriceId);
            return $input->successUrl;
        }

        return $user
            ->newSubscription('default', $stripePriceId)
            ->checkout([
                'success_url' => $input->successUrl,
                'cancel_url'  => $input->cancelUrl,
            ])
            ->url;
    }

    public function billingPortalUrl(int $userId, string $returnUrl): string
    {
        return $this->users->findById($userId)->billingPortalUrl($returnUrl);
    }
}
