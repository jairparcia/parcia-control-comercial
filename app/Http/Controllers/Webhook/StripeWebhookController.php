<?php

namespace App\Http\Controllers\Webhook;

use App\Application\Admin\SyncInvoiceFromStripeEventService;
use App\Application\Subscription\HandleBillingEventService;
use App\Domain\Auth\Contracts\UserRepositoryInterface;
use App\Domain\Subscription\Contracts\SubscriptionRepositoryInterface;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    public function __construct(
        private readonly HandleBillingEventService      $billingEventService,
        private readonly UserRepositoryInterface        $users,
        private readonly SubscriptionRepositoryInterface $subscriptions,
        private readonly SyncInvoiceFromStripeEventService $syncInvoice,
    ) {}

    protected function handleInvoicePaid(array $payload): Response
    {
        $this->syncInvoice->execute($payload['data']['object']);
        $this->forward('subscription.activated', $payload);
        return $this->successMethod();
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $this->syncInvoice->execute($payload['data']['object']);
        $this->forward('payment.failed', $payload);
        return $this->successMethod();
    }

    protected function handleInvoiceFinalized(array $payload): Response
    {
        $this->syncInvoice->execute($payload['data']['object']);
        return new Response('Webhook Handled.', 200);
    }

    protected function handleInvoiceUpdated(array $payload): Response
    {
        $this->syncInvoice->execute($payload['data']['object']);
        return new Response('Webhook Handled.', 200);
    }

    protected function handleInvoiceVoided(array $payload): Response
    {
        $this->syncInvoice->execute($payload['data']['object']);
        return new Response('Webhook Handled.', 200);
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
