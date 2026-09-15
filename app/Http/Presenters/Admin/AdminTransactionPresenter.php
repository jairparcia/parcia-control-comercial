<?php

namespace App\Http\Presenters\Admin;

use App\Domain\Admin\Results\AdminTransactionResult;
use App\Domain\Admin\Results\TransactionDetailResult;
use Carbon\Carbon;

class AdminTransactionPresenter
{
    /** @return AdminTransactionViewModel[] */
    public function presentAll(array $transactions): array
    {
        return array_map(fn ($t) => $this->present($t), $transactions);
    }

    private function present(AdminTransactionResult $t): AdminTransactionViewModel
    {
        return new AdminTransactionViewModel(
            stripeId:               $t->stripeId,
            formattedAmount:        $this->formatAmount($t->amountCents, $t->currency),
            formattedAmountRefunded: $t->amountRefundedCents > 0
                ? $this->formatAmount($t->amountRefundedCents, $t->currency)
                : '',
            status:                 $t->status,
            statusLabel:            $this->statusLabel($t->status),
            statusBadgeClass:       $this->statusBadgeClass($t->status),
            paymentMethod:          $this->paymentMethod($t->paymentMethodType, $t->cardBrand, $t->cardLast4),
            description:            $t->description ?? '—',
            customerName:           $t->customerName ?? '—',
            customerEmail:          $t->customerEmail ?? '',
            date:                   Carbon::instance($t->createdAt)->locale('es')->isoFormat('D MMM YYYY'),
        );
    }

    public function presentDetail(TransactionDetailResult $detail): TransactionDetailViewModel
    {
        $cur = $detail->currency;

        $events = array_map(fn ($e) => [
            'description' => $e->description,
            'time'        => Carbon::instance($e->happenedAt)->locale('es')->isoFormat('D MMM HH:mm'),
        ], $detail->events);

        return new TransactionDetailViewModel(
            stripeId:        $detail->stripeId,
            formattedAmount: $this->formatAmount($detail->amountCents, $cur),
            status:          $detail->status,
            statusLabel:     $this->statusLabel($detail->status),
            statusBadgeClass: $this->statusBadgeClass($detail->status),
            customerName:    $detail->customerName,
            customerEmail:   $detail->customerEmail,
            formattedFees:   $this->formatAmount($detail->stripeFeesCents, $cur),
            formattedNet:    $this->formatAmount($detail->netAmountCents, $cur),
            paymentMethodId: $detail->paymentMethodId,
            cardDisplay:     $detail->cardLast4 ? '•••• ' . $detail->cardLast4 : null,
            cardExpiry:      $detail->cardExpMonth && $detail->cardExpYear
                ? $detail->cardExpMonth . ' / ' . $detail->cardExpYear
                : null,
            cardFingerprint: $detail->cardFingerprint,
            cardType:        $this->cardTypeLabel($detail->cardFunding, $detail->cardBrand),
            cardIssuer:      $detail->cardIssuer,
            cardCountry:     $detail->cardCountry,
            cvcCheckLabel:   $this->cvcCheckLabel($detail->cvcCheck),
            billingName:     $detail->billingName,
            billingEmail:    $detail->billingEmail,
            billingCountry:  $detail->billingCountry,
            subscriptionId:  $detail->subscriptionId,
            planName:        $detail->planName,
            priceId:         $detail->priceId,
            invoiceNumber:   $detail->invoiceNumber,
            paymentIntentId: $detail->paymentIntentId,
            events:          $events,
            date:            Carbon::instance($detail->createdAt)->locale('es')->isoFormat('D MMM YYYY'),
            feeDetails:      array_map(fn ($f) => [
                'description' => $f->description,
                'amount'      => $this->formatAmount($f->amountCents, $f->currency),
            ], $detail->feeDetails),
        );
    }

    private function cardTypeLabel(?string $funding, ?string $brand): ?string
    {
        if (! $brand) {
            return null;
        }

        $fundingLabel = match ($funding) {
            'credit'  => 'tarjeta de crédito',
            'debit'   => 'tarjeta de débito',
            'prepaid' => 'prepago',
            default   => 'tarjeta',
        };

        return $fundingLabel . ' ' . $brand;
    }

    private function cvcCheckLabel(?string $check): ?string
    {
        return match ($check) {
            'pass'        => 'Superada',
            'fail'        => 'Fallida',
            'unavailable' => 'No disponible',
            'unchecked'   => 'No verificado',
            default       => null,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'succeeded'           => 'Exitoso',
            'pending'             => 'Pendiente',
            'failed'              => 'Fallido',
            'refunded'            => 'Reembolsado',
            'partially_refunded'  => 'Parcialmente reembolsado',
            default               => ucfirst($status),
        };
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'succeeded'          => 'bg-emerald-900/30 text-emerald-400',
            'pending'            => 'bg-blue-900/30 text-blue-400',
            'failed'             => 'bg-red-900/30 text-red-400',
            'refunded'           => 'bg-[#27272a] text-[#71717a]',
            'partially_refunded' => 'bg-amber-900/30 text-amber-400',
            default              => 'bg-[#27272a] text-[#71717a]',
        };
    }

    private function paymentMethod(?string $type, ?string $brand, ?string $last4): string
    {
        if ($brand && $last4) {
            return $brand . ' ••••' . $last4;
        }

        if ($type) {
            return match ($type) {
                'card'          => 'Card',
                'oxxo'          => 'OXXO',
                'bank_transfer' => 'Bank transfer',
                'sepa_debit'    => 'SEPA debit',
                default         => ucfirst(str_replace('_', ' ', $type)),
            };
        }

        return '—';
    }

    private function formatAmount(int $amountCents, string $currency): string
    {
        $symbol = match ($currency) {
            'MXN'   => 'MX$',
            'USD'   => 'US$',
            default => $currency . ' ',
        };

        return $symbol . number_format($amountCents / 100, 2, '.', ',');
    }
}
