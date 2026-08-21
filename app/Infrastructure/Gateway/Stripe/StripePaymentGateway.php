<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Auth\Contracts\UserRepository;
use App\Domain\Subscription\Contracts\PaymentGateway;
use App\Domain\Subscription\Contracts\SubscriptionPlanRepository;
use App\Domain\Subscription\Entities\CreateCheckoutSessionInput;
use Stripe\StripeClient;

class StripePaymentGateway implements PaymentGateway
{
    public function __construct(
        private readonly SubscriptionPlanRepository $plans,
        private readonly UserRepository $users,
    ) {}

    public function createCheckoutUrl(CreateCheckoutSessionInput $input): string
    {
        $stripePriceId = $this->plans->findStripePriceId($input->planKey);
        $user          = $this->users->findById($input->userId);

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
