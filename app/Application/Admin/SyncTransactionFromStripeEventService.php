<?php

namespace App\Application\Admin;

use App\Domain\Admin\Contracts\TransactionAdminRepositoryInterface;
use App\Domain\Admin\Entities\ProviderTransactionDataDTO;

class SyncTransactionFromStripeEventService
{
    public function __construct(
        private readonly TransactionAdminRepositoryInterface $repository,
    ) {}

    public function execute(array $charge): void
    {
        $this->repository->upsert($this->toDTO($charge));
    }

    private function toDTO(array $charge): ProviderTransactionDataDTO
    {
        $methodDetails = $charge['payment_method_details'] ?? [];
        $card          = $methodDetails['card'] ?? [];
        $cardBrand     = $card['brand'] ?? null;
        $billing       = $charge['billing_details'] ?? [];

        return new ProviderTransactionDataDTO(
            stripeId:            $charge['id'],
            amountCents:         (int) ($charge['amount'] ?? 0),
            amountRefundedCents: (int) ($charge['amount_refunded'] ?? 0),
            currency:            strtoupper($charge['currency'] ?? 'usd'),
            status:              $this->resolveStatus($charge),
            paymentMethodType:   $methodDetails['type'] ?? null,
            cardBrand:           $cardBrand ? ucfirst($cardBrand) : null,
            cardLast4:           ($card['last4'] ?? null) ?: null,
            description:         ($charge['description'] ?? null) ?: null,
            customerName:        ($billing['name'] ?? null) ?: null,
            customerEmail:       ($billing['email'] ?? null) ?: null,
            stripeCustomerId:    is_string($charge['customer'] ?? null) ? $charge['customer'] : null,
            createdAt:           new \DateTimeImmutable('@' . ($charge['created'] ?? time())),
        );
    }

    private function resolveStatus(array $charge): string
    {
        if ($charge['refunded'] ?? false) {
            return 'refunded';
        }

        if (($charge['amount_refunded'] ?? 0) > 0) {
            return 'partially_refunded';
        }

        return $charge['status'] ?? 'unknown';
    }
}
