<?php

namespace App\Http\Controllers\Webhook;

use App\Application\Subscription\HandleBillingEventService;
use App\Domain\Auth\Contracts\UserRepositoryInterface;
use App\Domain\Subscription\Contracts\SubscriptionRepositoryInterface;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    public function __construct(
        private readonly HandleBillingEventService $billingEventService,
        private readonly UserRepositoryInterface $users,
        private readonly SubscriptionRepositoryInterface $subscriptions,
    ) {}

    protected function handleInvoicePaid(array $payload): Response
    {
        $response = parent::handleInvoicePaid($payload);
        $this->forward('subscription.activated', $payload);
        return $response;
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);
        $this->forward('subscription.cancelled', $payload);
        return $response;
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);
        $this->forward('subscription.updated', $payload);
        return $response;
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $response = parent::handleInvoicePaymentFailed($payload);
        $this->forward('payment.failed', $payload);
        return $response;
    }

    // Resolves user and plan from the raw Stripe payload, then forwards to the application service.
    private function forward(string $event, array $payload): void
    {
        $customerId = $payload['data']['object']['customer'] ?? null;
        $user       = $customerId ? $this->users->findByStripeCustomerId($customerId) : null;
        $planKey    = $user ? $this->subscriptions->getStatus($user->id)->plan?->value : null;

        $this->billingEventService->execute(
            event:   $event,
            userId:  $user?->id,
            planKey: $planKey,
        );
    }
}
