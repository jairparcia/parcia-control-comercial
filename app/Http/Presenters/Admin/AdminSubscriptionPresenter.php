<?php

namespace App\Http\Presenters\Admin;

use App\Domain\Admin\Results\AdminSubscriptionResult;
use App\Domain\Admin\Results\SubscriptionCancellationInfoResult;
use App\Domain\Admin\Results\SubscriptionDetailResult;
use App\Domain\Admin\Results\SubscriptionInvoiceItemResult;
use Carbon\Carbon;

class AdminSubscriptionPresenter
{
    /** @return AdminSubscriptionViewModel[] */
    public function presentAll(array $subscriptions): array
    {
        return array_map(fn ($s) => $this->present($s), $subscriptions);
    }

    private function present(AdminSubscriptionResult $sub): AdminSubscriptionViewModel
    {
        return new AdminSubscriptionViewModel(
            id:                      $sub->id,
            stripeId:                $sub->stripeId,
            statusLabel:             $this->statusLabel($sub->status),
            statusBadgeClass:        $this->statusBadgeClass($sub->status),
            statusDotClass:          $this->statusDotClass($sub->status),
            userName:                $sub->userName,
            userEmail:               $sub->userEmail,
            paymentMethod:           $this->paymentMethod($sub->pmType, $sub->pmLastFour),
            planName:                $sub->planName ?? '—',
            formattedMonthlyAverage: $this->formatAmount($this->monthlyAmount($sub), $sub->currency),
            formattedAnnualAverage:  $this->formatAmount($this->annualAmount($sub), $sub->currency),
            subscribedAt:            $this->formatDate($sub->subscribedAt),
            endsAt:                  $sub->endsAt && $sub->status !== 'canceled' ? $this->formatDate($sub->endsAt) : null,
            canceledAt:              $sub->status === 'canceled' && $sub->endsAt ? $this->formatDate($sub->endsAt) : null,
        );
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'active'             => 'Activa',
            'trialing'           => 'Prueba',
            'past_due'           => 'Pago pendiente',
            'canceled'           => 'Cancelada',
            'incomplete'         => 'Incompleta',
            'incomplete_expired' => 'Expirada',
            'unpaid'             => 'Sin pagar',
            'paused'             => 'Pausada',
            default              => ucfirst($status),
        };
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'active'             => 'bg-emerald-900/30 text-emerald-400',
            'trialing'           => 'bg-blue-900/30 text-blue-400',
            'past_due', 'unpaid' => 'bg-red-900/30 text-red-400',
            default              => 'bg-[#27272a] text-[#71717a]',
        };
    }

    private function statusDotClass(string $status): string
    {
        return match ($status) {
            'active'             => 'bg-emerald-400',
            'trialing'           => 'bg-blue-400',
            'past_due', 'unpaid' => 'bg-red-400',
            default              => 'bg-[#52525b]',
        };
    }

    private function paymentMethod(?string $type, ?string $lastFour): string
    {
        if (! $type || ! $lastFour) {
            return '—';
        }

        return ucfirst($type) . ' ••••' . $lastFour;
    }

    private function monthlyAmount(AdminSubscriptionResult $sub): ?int
    {
        if ($sub->unitAmount === null || $sub->interval === null) {
            return null;
        }

        return $sub->interval === 'year'
            ? (int) round($sub->unitAmount / 12)
            : $sub->unitAmount;
    }

    private function annualAmount(AdminSubscriptionResult $sub): ?int
    {
        if ($sub->unitAmount === null || $sub->interval === null) {
            return null;
        }

        return $sub->interval === 'year'
            ? $sub->unitAmount
            : $sub->unitAmount * 12;
    }

    public function presentDetail(SubscriptionDetailResult $detail): SubscriptionDetailViewModel
    {
        $upcoming = null;

        if ($detail->upcomingInvoice) {
            $inv     = $detail->upcomingInvoice;
            $cur     = $inv->currency;
            $upcoming = [
                'description'  => $inv->description,
                'quantity'     => $inv->quantity,
                'unitAmount'   => $this->formatAmount($inv->unitAmountCents, $cur),
                'lineAmount'   => $this->formatAmount($inv->amountDueCents, $cur),
                'subtotal'     => $this->formatAmount($inv->subtotalCents, $cur),
                'tax'          => $this->formatAmount($inv->taxCents, $cur),
                'total'        => $this->formatAmount($inv->totalCents, $cur),
                'amountPaid'   => $this->formatAmount($inv->amountPaidCents, $cur),
                'amountRemaining' => $this->formatAmount($inv->amountRemainingCents, $cur),
                'nextBillingDate' => $this->formatDate($inv->nextBillingDate),
            ];
        }

        $invoices = array_map(
            fn (SubscriptionInvoiceItemResult $inv) => [
                'number'          => $inv->invoiceNumber ?: '—',
                'status'          => $inv->status,
                'statusLabel'     => $this->invoiceStatusLabel($inv->status),
                'statusBadgeClass' => $this->invoiceStatusBadgeClass($inv->status),
                'amount'          => $this->formatAmount($inv->amountCents, $inv->currency),
                'interval'        => $this->intervalLabel($inv->interval),
                'email'           => $detail->userEmail,
                'date'            => $this->formatDate($inv->createdAt),
            ],
            $detail->invoices,
        );

        $nextDate   = $detail->upcomingInvoice ? $this->formatDate($detail->upcomingInvoice->nextBillingDate) : '—';
        $periodStart = $detail->upcomingInvoice ? $this->formatDate($detail->upcomingInvoice->periodStart) : $this->formatDate($detail->subscribedAt);

        $currentPeriod = $periodStart . ' – ' . $nextDate;

        return new SubscriptionDetailViewModel(
            stripeId:          $detail->stripeId,
            userName:          $detail->userName,
            userEmail:         $detail->userEmail,
            statusLabel:       $this->statusLabel($detail->status),
            statusBadgeClass:  $this->statusBadgeClass($detail->status),
            subscribedAt:      $this->formatDate($detail->subscribedAt),
            planName:          $detail->planName,
            interval:          $this->intervalLabel($detail->interval),
            formattedAmount:   $this->formatAmount($detail->unitAmountCents, $detail->currency),
            currentPeriod:     $currentPeriod,
            upcomingInvoice:   $upcoming,
            invoices:          $invoices,
        );
    }

    private function invoiceStatusLabel(string $status): string
    {
        return match ($status) {
            'paid'   => 'Pagada',
            'open'   => 'Abierta',
            'void'   => 'Anulada',
            'draft'  => 'Borrador',
            default  => ucfirst($status),
        };
    }

    private function invoiceStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'paid'  => 'bg-emerald-900/30 text-emerald-400',
            'open'  => 'bg-yellow-900/30 text-yellow-400',
            'void'  => 'bg-[#27272a] text-[#71717a]',
            default => 'bg-[#27272a] text-[#71717a]',
        };
    }

    private function intervalLabel(string $interval): string
    {
        return match ($interval) {
            'month' => 'Mensual',
            'year'  => 'Anual',
            'week'  => 'Semanal',
            'day'   => 'Diario',
            default => ucfirst($interval),
        };
    }

    public function presentCancellationInfo(SubscriptionCancellationInfoResult $info): SubscriptionCancellationInfoViewModel
    {
        return new SubscriptionCancellationInfoViewModel(
            stripeSubscriptionId:    $info->stripeSubscriptionId,
            periodEndFormatted:      $this->formatDate($info->periodEnd),
            lastPaymentFormatted:    $this->formatAmount($info->lastPaymentAmount, $info->lastPaymentCurrency),
            proratedAmountFormatted: $this->formatAmount($info->proratedAmount, $info->lastPaymentCurrency),
            proratedDays:            $info->proratedDays,
        );
    }

    private function formatDate(\DateTimeImmutable $date): string
    {
        return Carbon::instance($date)->locale('es')->isoFormat('D MMM YYYY');
    }

    private function formatAmount(?int $amountCents, ?string $currency): string
    {
        if ($amountCents === null || $currency === null) {
            return '—';
        }

        $symbol = match ($currency) {
            'MXN'   => 'MX$',
            'USD'   => 'US$',
            default => $currency . ' ',
        };

        return $symbol . number_format($amountCents / 100, 0, '.', ',');
    }
}
