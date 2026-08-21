<?php

namespace App\Http\Controllers\Webhook;

use App\Application\Subscription\HandleBillingEventService;
use App\Domain\Subscription\Entities\BillingEventInput;
use App\Domain\Subscription\Enums\BillingEvent;
use App\Domain\Subscription\Enums\Plan;
use App\Models\User;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    public function __construct(
        private readonly HandleBillingEventService $billingEventService,
    ) {}

    protected function handleInvoicePaid(array $payload): Response
    {
        $response = parent::handleInvoicePaid($payload);

        $this->dispatch(BillingEvent::SubscriptionActivated, $payload);

        return $response;
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $this->dispatch(BillingEvent::SubscriptionCancelled, $payload);

        return $response;
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $this->dispatch(BillingEvent::SubscriptionUpdated, $payload);

        return $response;
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $response = parent::handleInvoicePaymentFailed($payload);

        $this->dispatch(BillingEvent::PaymentFailed, $payload);

        return $response;
    }

    // Translates a Stripe payload into a provider-agnostic BillingEventInput
    private function dispatch(BillingEvent $event, array $payload): void
    {
        $customerId = $payload['data']['object']['customer'] ?? null;
        $user       = $customerId ? User::where('stripe_id', $customerId)->first() : null;

        $this->billingEventService->execute(new BillingEventInput(
            event: $event,
            userId: $user?->id,
            plan: $user ? $this->resolvePlan($user->subscription()?->stripe_price) : null,
        ));
    }

    private function resolvePlan(?string $stripePrice): ?Plan
    {
        if (! $stripePrice) {
            return null;
        }

        foreach (Plan::cases() as $plan) {
            if (config("plans.{$plan->value}.price_id") === $stripePrice) {
                return $plan;
            }
        }

        return null;
    }
}
