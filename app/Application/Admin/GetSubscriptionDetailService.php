<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Results\SubscriptionDetailResult;

class GetSubscriptionDetailService
{
    public function __construct(
        private readonly SubscriptionAdminRepositoryInterface $subscriptions,
        private readonly CustomerProviderGatewayInterface     $gateway,
    ) {}

    public function execute(string $stripeSubscriptionId): ?SubscriptionDetailResult
    {
        $sub = $this->subscriptions->findByStripeId($stripeSubscriptionId);

        if (! $sub) {
            return null;
        }

        $upcomingInvoice = null;

        if ($sub->stripeCustomerId) {
            $upcomingInvoice = $this->gateway->getUpcomingInvoice(
                $sub->stripeCustomerId,
                $sub->stripeId,
            );
        }

        $invoices = $this->gateway->getSubscriptionInvoices($sub->stripeId);

        return new SubscriptionDetailResult(
            stripeId:         $sub->stripeId,
            stripeCustomerId: $sub->stripeCustomerId ?? '',
            userId:           $sub->userId,
            userName:         $sub->userName,
            userEmail:        $sub->userEmail,
            status:           $sub->status,
            planName:         $sub->planName ?? '',
            interval:         $sub->interval ?? 'month',
            unitAmountCents:  $sub->unitAmount ?? 0,
            currency:         $sub->currency ?? 'MXN',
            subscribedAt:     $sub->subscribedAt,
            upcomingInvoice:  $upcomingInvoice,
            invoices:         $invoices,
        );
    }
}
