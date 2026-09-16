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
            cvcCheckPassed:  $detail->cvcCheck === 'pass',
            cvcCheckClass:   match ($detail->cvcCheck) {
                'pass'  => 'text-emerald-400',
                'fail'  => 'text-red-400',
                default => 'text-[#a1a1aa]',
            },
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
            'credit'  => __('common.card_credit'),
            'debit'   => __('common.card_debit'),
            'prepaid' => __('common.card_prepaid'),
            default   => __('common.card_default'),
        };

        return $fundingLabel . ' ' . $brand;
    }

    private function cvcCheckLabel(?string $check): ?string
    {
        return match ($check) {
            'pass'        => __('common.cvc_passed'),
            'fail'        => __('common.cvc_failed'),
            'unavailable' => __('common.cvc_unavailable'),
            'unchecked'   => __('common.cvc_unchecked'),
            default       => null,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'succeeded'           => __('common.tx_successful'),
            'pending'             => __('common.tx_pending'),
            'failed'              => __('common.tx_failed'),
            'refunded'            => __('common.tx_refunded'),
            'partially_refunded'  => __('common.tx_partially_refunded'),
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
                'card'          => __('common.pm_card'),
                'oxxo'          => 'OXXO',
                'bank_transfer' => __('common.pm_bank_transfer'),
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
