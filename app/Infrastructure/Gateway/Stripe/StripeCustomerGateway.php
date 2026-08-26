<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Entities\StripeCustomerDataDTO;
use App\Domain\Admin\Results\PaymentHistoryItemResult;
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
            $invoice = $this->client->invoices->createPreview([
                'customer'     => $stripeCustomerId,
                'subscription' => $stripeSubscriptionId,
            ]);

            $periodEnd = (int) ($invoice->period_end ?? 0);

            $date = $periodEnd > 0
                ? new \DateTimeImmutable('@' . $periodEnd)
                : null;

            if (! $date) {
                return null;
            }

            return new UpcomingInvoiceResult(
                nextBillingDate: $date,
                amountDueCents:  $invoice->amount_due ?? 0,
                currency:        strtoupper($invoice->currency ?? 'MXN'),
            );
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}
