<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Admin\Contracts\SubscriptionProviderGatewayInterface;
use App\Domain\Admin\Entities\CancelSubscriptionInputDTO;
use App\Domain\Admin\Entities\CancelSubscriptionResultDTO;
use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;
use App\Domain\Admin\Results\SubscriptionCancellationInfoResult;
use Stripe\StripeClient;

class StripeSubscriptionGateway implements SubscriptionProviderGatewayInterface
{
    public function __construct(
        private readonly StripeClient $client,
    ) {}

    public function listAll(): array
    {
        $results = [];
        $params  = ['limit' => 100, 'expand' => ['data.customer']];

        do {
            $page = $this->client->subscriptions->all($params);

            foreach ($page->data as $sub) {
                $priceId = $sub->items->data[0]->price->id ?? null;

                $customer = $sub->customer;
                $customerId    = is_string($customer) ? $customer : $customer->id;
                $customerEmail = is_string($customer) ? null : ($customer->email ?? null);
                $customerName  = is_string($customer) ? null : ($customer->name ?? null);

                $results[] = new ProviderSubscriptionDataDTO(
                    providerSubscriptionId: $sub->id,
                    providerCustomerId:     $customerId,
                    customerEmail:          $customerEmail,
                    customerName:           $customerName,
                    status:                 $sub->status,
                    priceId:                $priceId,
                    type:                   'default',
                    trialEndsAt:            $sub->trial_end ? new \DateTimeImmutable('@' . $sub->trial_end) : null,
                    endsAt:                 $sub->cancel_at ? new \DateTimeImmutable('@' . $sub->cancel_at) : null,
                    createdAt:              new \DateTimeImmutable('@' . $sub->created),
                );
            }

            $params['starting_after'] = $page->data ? end($page->data)->id : null;
        } while ($page->has_more);

        return $results;
    }

    public function getCancellationInfo(string $stripeSubscriptionId): SubscriptionCancellationInfoResult
    {
        $sub = $this->client->subscriptions->retrieve($stripeSubscriptionId, [
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        $now         = time();
        $periodStart = (int) ($sub->current_period_start ?? 0);
        $periodEnd   = (int) ($sub->current_period_end   ?? 0);

        if ($periodEnd === 0) {
            $periodEnd = $this->estimateNextPeriodEnd($sub);
        }

        $totalSeconds     = $periodEnd > $periodStart ? $periodEnd - $periodStart : 0;
        $remainingSeconds = $periodEnd > $now ? $periodEnd - $now : 0;

        $lastPaymentAmount = $sub->latest_invoice?->amount_paid ?? 0;

        $proratedAmount = $totalSeconds > 0
            ? (int) round(($remainingSeconds / $totalSeconds) * $lastPaymentAmount)
            : 0;

        $proratedDays = $remainingSeconds > 0 ? (int) ceil($remainingSeconds / 86400) : 0;

        $periodEndDate = $periodEnd > 0
            ? new \DateTimeImmutable('@' . $periodEnd)
            : new \DateTimeImmutable('now');

        return new SubscriptionCancellationInfoResult(
            stripeSubscriptionId: $stripeSubscriptionId,
            periodEnd:            $periodEndDate,
            lastPaymentAmount:    $lastPaymentAmount,
            lastPaymentCurrency:  strtoupper($sub->currency ?? 'usd'),
            proratedAmount:       $proratedAmount,
            proratedDays:         $proratedDays,
        );
    }

    public function cancel(CancelSubscriptionInputDTO $input): CancelSubscriptionResultDTO
    {
        if (! $input->immediately) {
            $updated = $this->client->subscriptions->update($input->stripeSubscriptionId, [
                'cancel_at_period_end' => true,
            ]);

            $cancelAt = (int) ($updated->cancel_at ?? 0);

            return new CancelSubscriptionResultDTO(
                immediate:        false,
                scheduledEndsAt:  $cancelAt > 0 ? new \DateTimeImmutable('@' . $cancelAt) : null,
            );
        }

        if ($input->refundType !== 'none') {
            $sub = $this->client->subscriptions->retrieve($input->stripeSubscriptionId, [
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $paymentIntentId = $sub->latest_invoice?->payment_intent?->id;

            if ($paymentIntentId) {
                $refundParams = ['payment_intent' => $paymentIntentId];

                if ($input->refundType === 'prorated') {
                    $info = $this->getCancellationInfo($input->stripeSubscriptionId);
                    $refundParams['amount'] = $info->proratedAmount;
                }

                if (! isset($refundParams['amount']) || $refundParams['amount'] > 0) {
                    $this->client->refunds->create($refundParams);
                }
            }
        }

        $this->client->subscriptions->cancel($input->stripeSubscriptionId);

        return new CancelSubscriptionResultDTO(immediate: true, scheduledEndsAt: null);
    }

    private function estimateNextPeriodEnd(\Stripe\Subscription $sub): int
    {
        $anchor        = (int) ($sub->billing_cycle_anchor ?? 0);
        $item          = $sub->items->data[0] ?? null;
        $interval      = $item?->price->recurring->interval ?? 'month';
        $intervalCount = (int) ($item?->price->recurring->interval_count ?? 1);

        if ($anchor === 0) {
            return 0;
        }

        $anchorDate = new \DateTimeImmutable('@' . $anchor);
        $now        = new \DateTimeImmutable();
        $modifier   = "+{$intervalCount} {$interval}";
        $next       = $anchorDate;

        while ($next <= $now) {
            $next = $next->modify($modifier);
        }

        return $next->getTimestamp();
    }
}
