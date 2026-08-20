<?php

namespace App\Http\Presenters\Admin;

use App\Domain\Admin\Results\AdminSubscriptionResult;
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
            endsAt:                  $sub->endsAt ? $this->formatDate($sub->endsAt) : null,
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
