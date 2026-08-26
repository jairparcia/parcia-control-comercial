<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;
use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Results\CustomerDetailResult;
use App\Domain\Admin\Results\SubscriptionSummaryResult;

class GetCustomerDetailService
{
    public function __construct(
        private readonly CustomerAdminRepositoryInterface  $customers,
        private readonly CustomerProviderGatewayInterface  $gateway,
        private readonly SubscriptionAdminRepositoryInterface $subscriptions,
    ) {}

    public function execute(int $userId): CustomerDetailResult
    {
        $user = $this->customers->findById($userId);

        if (! $user) {
            throw new \RuntimeException("Customer #{$userId} not found.");
        }

        $subscription = $this->subscriptions->findByUserId($userId);

        $invoiceHistory  = [];
        $totalSpentCents = 0;
        $currency        = $subscription?->currency ?? 'MXN';
        $upcomingInvoice = null;

        if ($user->stripe_id) {
            $invoiceHistory = $this->gateway->getInvoiceHistory($user->stripe_id);

            foreach ($invoiceHistory as $item) {
                $totalSpentCents += $item->amountCents;
                $currency         = strtoupper($item->currency);
            }

            if ($subscription) {
                $upcomingInvoice = $this->gateway->getUpcomingInvoice($user->stripe_id, $subscription->stripeId);
            }
        }

        $subscriptionSummary = null;

        if ($subscription) {
            $subscriptionSummary = new SubscriptionSummaryResult(
                stripeSubscriptionId:   $subscription->stripeId,
                planName:               $subscription->planName ?? '—',
                interval:               $subscription->interval ?? 'month',
                unitAmountCents:        $subscription->unitAmount ?? 0,
                currency:               $subscription->currency ?? 'MXN',
                nextBillingDate:        $upcomingInvoice?->nextBillingDate,
                nextBillingAmountCents: $upcomingInvoice?->amountDueCents ?? 0,
            );
        }

        return new CustomerDetailResult(
            id:             $user->id,
            name:           $user->name,
            email:          $user->email,
            description:    $user->description,
            country:        $user->country,
            archived:       (bool) $user->archived,
            memberSince:    new \DateTimeImmutable($user->created_at->toDateTimeString()),
            totalSpentCents: $totalSpentCents,
            currency:       $currency,
            subscription:   $subscriptionSummary,
            paymentHistory: $invoiceHistory,
        );
    }
}
