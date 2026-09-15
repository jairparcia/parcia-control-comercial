<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Admin\Contracts\InvoiceProviderGatewayInterface;
use App\Domain\Admin\Entities\ProviderInvoiceDataDTO;
use Stripe\StripeClient;

class StripeInvoiceGateway implements InvoiceProviderGatewayInterface
{
    public function __construct(
        private readonly StripeClient $client,
    ) {}

    public function listAll(int $limit = 100): array
    {
        $results = [];

        // List gives us IDs and basic metadata; price details require individual retrieval.
        $page = $this->client->invoices->all(['limit' => min($limit, 100)]);

        foreach ($page->data as $summary) {
            $results[] = $this->buildDTO($summary->id);
        }

        return array_values(array_filter($results));
    }

    private function buildDTO(string $invoiceId): ?ProviderInvoiceDataDTO
    {
        try {
            $invoice = $this->client->invoices->retrieve($invoiceId, [
                'expand' => ['lines.data.price'],
            ]);
        } catch (\Throwable) {
            return null;
        }

        $customerId    = is_string($invoice->customer) ? $invoice->customer : ($invoice->customer?->id ?? null);
        $customerName  = $invoice->customer_name ?: null;
        $customerEmail = $invoice->customer_email ?: null;

        [$interval, $intervalCount] = $this->resolveInterval($invoice);

        return new ProviderInvoiceDataDTO(
            stripeId:         $invoice->id,
            invoiceNumber:    $invoice->number ?: null,
            totalCents:       (int) ($invoice->total ?? 0),
            currency:         strtoupper($invoice->currency),
            status:           $invoice->status,
            interval:         $interval,
            intervalCount:    $intervalCount,
            customerName:     $customerName,
            customerEmail:    $customerEmail,
            stripeCustomerId: $customerId,
            dueDate:          $invoice->due_date ? new \DateTimeImmutable('@' . $invoice->due_date) : null,
            createdAt:        new \DateTimeImmutable('@' . $invoice->created),
        );
    }

    private function resolveInterval(object $invoice): array
    {
        $firstLine = $invoice->lines?->data[0] ?? null;

        // Price as an expanded object (comes from expand: ['lines.data.price']).
        $price = is_object($firstLine?->price) ? $firstLine->price : null;

        // Price returned as a string ID — retrieve it individually.
        if ($price === null && is_string($firstLine?->price) && $firstLine->price) {
            try {
                $price = $this->client->prices->retrieve($firstLine->price);
            } catch (\Throwable) {}
        }

        if ($price?->recurring !== null) {
            return [
                $price->recurring->interval,
                (int) ($price->recurring->interval_count ?? 1),
            ];
        }

        // Fallback: deprecated plan field on the line item.
        if (is_object($firstLine?->plan) && $firstLine->plan->interval) {
            return [
                $firstLine->plan->interval,
                (int) ($firstLine->plan->interval_count ?? 1),
            ];
        }

        return [null, 1];
    }
}
