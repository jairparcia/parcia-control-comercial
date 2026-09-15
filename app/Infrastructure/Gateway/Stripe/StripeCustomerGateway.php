<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Entities\StripeCustomerDataDTO;
use App\Domain\Admin\Results\PaymentHistoryItemResult;
use App\Domain\Admin\Results\SubscriptionInvoiceItemResult;
use App\Domain\Admin\Results\UpcomingInvoiceResult;
use Stripe\StripeClient;

class StripeCustomerGateway implements CustomerProviderGatewayInterface
{
    public function __construct(
        private readonly StripeClient $client,
    ) {}

    public function listAll(): array
    {
        $results = [];
        $params  = ['limit' => 100];

        do {
            $page = $this->client->customers->all($params);

            foreach ($page->data as $customer) {
                if (! $customer->email) {
                    continue;
                }

                $country = $customer->address?->country
                    ?? $customer->shipping?->address?->country
                    ?? null;

                $results[] = new StripeCustomerDataDTO(
                    providerCustomerId: $customer->id,
                    email:              $customer->email,
                    name:               $customer->name ?: null,
                    description:        $customer->description ?: null,
                    country:            $country,
                    createdAt:          new \DateTimeImmutable('@' . $customer->created),
                );
            }

            $params['starting_after'] = $page->data ? end($page->data)->id : null;
        } while ($page->has_more);

        return $results;
    }

    public function findByEmail(string $email): ?StripeCustomerDataDTO
    {
        $page = $this->client->customers->all(['email' => $email, 'limit' => 1]);

        if (empty($page->data)) {
            return null;
        }

        $customer = $page->data[0];

        $country = $customer->address?->country
            ?? $customer->shipping?->address?->country
            ?? null;

        return new StripeCustomerDataDTO(
            providerCustomerId: $customer->id,
            email:              $customer->email,
            name:               $customer->name ?: null,
            description:        $customer->description ?: null,
            country:            $country,
            createdAt:          new \DateTimeImmutable('@' . $customer->created),
        );
    }

    public function getInvoiceHistory(string $stripeCustomerId): array
    {
        $results = [];
        $params  = ['customer' => $stripeCustomerId, 'status' => 'paid', 'limit' => 24];

        $page = $this->client->invoices->all($params);

        foreach ($page->data as $invoice) {
            $planDescription = null;

            if ($invoice->lines->data) {
                $line            = $invoice->lines->data[0];
                $planDescription = $line->plan?->nickname ?? $line->description ?? null;
            }

            $results[] = new PaymentHistoryItemResult(
                amountCents:     $invoice->amount_paid,
                currency:        strtoupper($invoice->currency),
                paidAt:          new \DateTimeImmutable('@' . ($invoice->status_transitions->paid_at ?? $invoice->created)),
                planDescription: $planDescription,
            );
        }

        return $results;
    }

    public function getUpcomingInvoice(string $stripeCustomerId, string $stripeSubscriptionId): ?UpcomingInvoiceResult
    {
        try {
            [$invoice, $subscription] = [
                $this->client->invoices->createPreview([
                    'customer'     => $stripeCustomerId,
                    'subscription' => $stripeSubscriptionId,
                ]),
                $this->client->subscriptions->retrieve($stripeSubscriptionId),
            ];

            $nextBillingTs = (int) ($invoice->next_payment_attempt
                ?? $subscription->current_period_end
                ?? $invoice->period_end
                ?? 0);

            if (! $nextBillingTs) {
                return null;
            }

            $periodStartTs = (int) ($subscription->current_period_start ?? 0);

            $line        = $invoice->lines->data[0] ?? null;
            $description = $line?->plan?->nickname ?? $line?->description ?? '';
            $quantity    = (int) ($line?->quantity ?? 1);
            $lineAmount  = (int) ($line?->amount ?? 0);
            $unitAmount  = $quantity > 0
                ? (int) round($lineAmount / $quantity)
                : (int) ($line?->price?->unit_amount ?? 0);

            $subtotal        = (int) ($invoice->subtotal ?? 0);
            $tax             = (int) ($invoice->tax ?? 0);
            $total           = (int) ($invoice->total ?? 0);
            $amountPaid      = (int) ($invoice->amount_paid ?? 0);
            $amountDue       = (int) ($invoice->amount_due ?? 0);
            $amountRemaining = (int) ($invoice->amount_remaining ?? $total);

            return new UpcomingInvoiceResult(
                periodStart:          $periodStartTs > 0
                    ? new \DateTimeImmutable('@' . $periodStartTs)
                    : new \DateTimeImmutable('@' . ($nextBillingTs - 2592000)),
                nextBillingDate:      new \DateTimeImmutable('@' . $nextBillingTs),
                description:          $description,
                quantity:             $quantity,
                unitAmountCents:      $unitAmount,
                amountDueCents:       $lineAmount ?: $amountDue,
                currency:             strtoupper($invoice->currency ?? 'MXN'),
                subtotalCents:        $subtotal,
                taxCents:             $tax,
                totalCents:           $total,
                amountPaidCents:      $amountPaid,
                amountRemainingCents: $amountRemaining,
            );
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    public function getSubscriptionInvoices(string $stripeSubscriptionId): array
    {
        try {
            $results = [];
            $page    = $this->client->invoices->all([
                'subscription' => $stripeSubscriptionId,
                'limit'        => 24,
            ]);

            foreach ($page->data as $invoice) {
                $interval = 'month';

                if ($invoice->lines->data) {
                    $line     = $invoice->lines->data[0];
                    $interval = $line->plan?->interval
                        ?? $line->price?->recurring?->interval
                        ?? 'month';
                }

                $results[] = new SubscriptionInvoiceItemResult(
                    invoiceNumber: $invoice->number ?? '',
                    amountCents:   (int) ($invoice->amount_paid ?: $invoice->total),
                    currency:      strtoupper($invoice->currency),
                    interval:      $interval,
                    status:        $invoice->status ?? 'open',
                    createdAt:     new \DateTimeImmutable('@' . $invoice->created),
                );
            }

            return $results;
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }
}
