<?php

namespace App\Infrastructure\Repository\Admin;

use App\Domain\Admin\Contracts\InvoiceAdminRepositoryInterface;
use App\Domain\Admin\Entities\ProviderInvoiceDataDTO;
use App\Domain\Admin\Results\AdminInvoiceResult;
use App\Models\Invoice;
use App\Models\User;

class EloquentInvoiceAdminRepository implements InvoiceAdminRepositoryInterface
{
    public function all(string $statusFilter = 'paid'): array
    {
        return Invoice::query()
            ->with(['user.subscriptions' => fn ($q) => $q->whereIn('stripe_status', ['active', 'trialing'])])
            ->when($statusFilter !== 'all', fn ($q) => $q->where('status', $statusFilter))
            ->orderByDesc('stripe_created_at')
            ->get()
            ->map(fn (Invoice $inv) => $this->toResult($inv))
            ->all();
    }

    public function insertMissing(array $invoices): int
    {
        $inserted = 0;

        foreach ($invoices as $dto) {
            $user = $dto->stripeCustomerId
                ? User::where('stripe_id', $dto->stripeCustomerId)->first()
                : null;

            $affected = Invoice::insertOrIgnore([
                'user_id'                => $user?->id,
                'stripe_id'              => $dto->stripeId,
                'stripe_customer_id'     => $dto->stripeCustomerId,
                'invoice_number'         => $dto->invoiceNumber,
                'total_cents'            => $dto->totalCents,
                'currency'               => $dto->currency,
                'status'                 => $dto->status,
                'billing_interval'       => $dto->interval,
                'billing_interval_count' => $dto->intervalCount,
                'customer_name'          => $dto->customerName,
                'customer_email'         => $dto->customerEmail,
                'due_date'               => $dto->dueDate?->format('Y-m-d H:i:s'),
                'stripe_created_at'      => $dto->createdAt->format('Y-m-d H:i:s'),
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            $inserted += $affected;
        }

        return $inserted;
    }

    public function upsert(ProviderInvoiceDataDTO $dto): void
    {
        $user = $dto->stripeCustomerId
            ? User::where('stripe_id', $dto->stripeCustomerId)->first()
            : null;

        Invoice::updateOrCreate(
            ['stripe_id' => $dto->stripeId],
            [
                'user_id'                => $user?->id,
                'stripe_customer_id'     => $dto->stripeCustomerId,
                'invoice_number'         => $dto->invoiceNumber,
                'total_cents'            => $dto->totalCents,
                'currency'               => $dto->currency,
                'status'                 => $dto->status,
                'billing_interval'       => $dto->interval,
                'billing_interval_count' => $dto->intervalCount,
                'customer_name'          => $dto->customerName,
                'customer_email'         => $dto->customerEmail,
                'due_date'               => $dto->dueDate?->format('Y-m-d H:i:s'),
                'stripe_created_at'      => $dto->createdAt->format('Y-m-d H:i:s'),
            ],
        );
    }

    private function toResult(Invoice $inv): AdminInvoiceResult
    {
        $activeSub = $inv->user?->subscriptions->first();

        return new AdminInvoiceResult(
            stripeId:             $inv->stripe_id,
            invoiceNumber:        $inv->invoice_number,
            totalCents:           $inv->total_cents,
            currency:             $inv->currency,
            status:               $inv->status,
            interval:             $inv->billing_interval,
            intervalCount:        (int) $inv->billing_interval_count,
            customerName:         $inv->customer_name,
            customerEmail:        $inv->customer_email,
            stripeCustomerId:     $inv->stripe_customer_id,
            dueDate:              $inv->due_date ? new \DateTimeImmutable($inv->due_date->toDateTimeString()) : null,
            createdAt:            new \DateTimeImmutable($inv->stripe_created_at->toDateTimeString()),
            id:                   $inv->id,
            userId:               $inv->user_id,
            stripeSubscriptionId: $activeSub?->stripe_id,
        );
    }
}
