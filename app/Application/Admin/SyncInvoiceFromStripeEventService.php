<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\InvoiceAdminRepositoryInterface;
use App\Domain\Admin\Entities\ProviderInvoiceDataDTO;

class SyncInvoiceFromStripeEventService
{
    public function __construct(
        private readonly InvoiceAdminRepositoryInterface $repository,
    ) {}

    public function execute(array $invoice): void
    {
        $this->repository->upsert($this->toDTO($invoice));
    }

    private function toDTO(array $invoice): ProviderInvoiceDataDTO
    {
        [$interval, $intervalCount] = $this->resolveInterval($invoice);

        return new ProviderInvoiceDataDTO(
            stripeId:         $invoice['id'],
            invoiceNumber:    ($invoice['number'] ?? null) ?: null,
            totalCents:       (int) ($invoice['total'] ?? 0),
            currency:         strtoupper($invoice['currency'] ?? 'usd'),
            status:           $invoice['status'] ?? 'draft',
            interval:         $interval,
            intervalCount:    $intervalCount,
            customerName:     ($invoice['customer_name'] ?? null) ?: null,
            customerEmail:    ($invoice['customer_email'] ?? null) ?: null,
            stripeCustomerId: is_string($invoice['customer'] ?? null) ? $invoice['customer'] : null,
            dueDate:          isset($invoice['due_date']) && $invoice['due_date']
                ? new \DateTimeImmutable('@' . $invoice['due_date'])
                : null,
            createdAt:        new \DateTimeImmutable('@' . ($invoice['created'] ?? time())),
        );
    }

    private function resolveInterval(array $invoice): array
    {
        $firstLine = ($invoice['lines']['data'] ?? [])[0] ?? null;

        if ($firstLine === null) {
            return [null, 1];
        }

        $price = is_array($firstLine['price'] ?? null) ? $firstLine['price'] : null;

        if ($price !== null && isset($price['recurring']['interval'])) {
            return [
                $price['recurring']['interval'],
                (int) ($price['recurring']['interval_count'] ?? 1),
            ];
        }

        $plan = is_array($firstLine['plan'] ?? null) ? $firstLine['plan'] : null;

        if ($plan !== null && isset($plan['interval'])) {
            return [
                $plan['interval'],
                (int) ($plan['interval_count'] ?? 1),
            ];
        }

        return [null, 1];
    }
}
