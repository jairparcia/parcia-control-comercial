<?php

namespace App\Infrastructure\Repository\Admin;

use App\Domain\Admin\Contracts\TransactionAdminRepositoryInterface;
use App\Domain\Admin\Entities\ProviderTransactionDataDTO;
use App\Domain\Admin\Results\AdminTransactionResult;
use App\Models\Transaction;
use App\Models\User;

class EloquentTransactionAdminRepository implements TransactionAdminRepositoryInterface
{
    public function all(string $statusFilter = 'all'): array
    {
        $query = Transaction::query()->orderByDesc('stripe_created_at');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        return $query->get()
            ->map(fn (Transaction $tx) => $this->toResult($tx))
            ->all();
    }

    public function insertMissing(array $transactions): int
    {
        $count = 0;

        foreach ($transactions as $dto) {
            $userId = User::where('stripe_id', $dto->stripeCustomerId)->value('id');

            $inserted = Transaction::insertOrIgnore([[
                'user_id'               => $userId,
                'stripe_id'             => $dto->stripeId,
                'stripe_customer_id'    => $dto->stripeCustomerId,
                'amount_cents'          => $dto->amountCents,
                'amount_refunded_cents' => $dto->amountRefundedCents,
                'currency'              => $dto->currency,
                'status'                => $dto->status,
                'payment_method_type'   => $dto->paymentMethodType,
                'card_brand'            => $dto->cardBrand,
                'card_last4'            => $dto->cardLast4,
                'description'           => $dto->description,
                'customer_name'         => $dto->customerName,
                'customer_email'        => $dto->customerEmail,
                'stripe_created_at'     => $dto->createdAt->format('Y-m-d H:i:s'),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]]);

            $count += $inserted;
        }

        return $count;
    }

    public function upsert(ProviderTransactionDataDTO $transaction): void
    {
        $userId = User::where('stripe_id', $transaction->stripeCustomerId)->value('id');

        Transaction::updateOrCreate(
            ['stripe_id' => $transaction->stripeId],
            [
                'user_id'               => $userId,
                'stripe_customer_id'    => $transaction->stripeCustomerId,
                'amount_cents'          => $transaction->amountCents,
                'amount_refunded_cents' => $transaction->amountRefundedCents,
                'currency'              => $transaction->currency,
                'status'                => $transaction->status,
                'payment_method_type'   => $transaction->paymentMethodType,
                'card_brand'            => $transaction->cardBrand,
                'card_last4'            => $transaction->cardLast4,
                'description'           => $transaction->description,
                'customer_name'         => $transaction->customerName,
                'customer_email'        => $transaction->customerEmail,
                'stripe_created_at'     => $transaction->createdAt->format('Y-m-d H:i:s'),
            ],
        );
    }

    private function toResult(Transaction $tx): AdminTransactionResult
    {
        return new AdminTransactionResult(
            stripeId:            $tx->stripe_id,
            amountCents:         $tx->amount_cents,
            amountRefundedCents: $tx->amount_refunded_cents,
            currency:            $tx->currency,
            status:              $tx->status,
            paymentMethodType:   $tx->payment_method_type,
            cardBrand:           $tx->card_brand,
            cardLast4:           $tx->card_last4,
            description:         $tx->description,
            customerName:        $tx->customer_name,
            customerEmail:       $tx->customer_email,
            stripeCustomerId:    $tx->stripe_customer_id,
            createdAt:           new \DateTimeImmutable(
                $tx->stripe_created_at?->toIso8601String() ?? 'now'
            ),
            id:                  $tx->id,
            userId:              $tx->user_id,
        );
    }
}
