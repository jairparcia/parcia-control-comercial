<?php

namespace App\Http\Presenters\Admin;

use App\Domain\Admin\Results\AdminCustomerResult;
use App\Domain\Admin\Results\CustomerDetailResult;
use Carbon\Carbon;

class AdminCustomerPresenter
{
    /** @return AdminCustomerViewModel[] */
    public function presentAll(array $customers): array
    {
        return array_map(fn ($c) => $this->present($c), $customers);
    }

    public function presentDetail(CustomerDetailResult $detail): AdminCustomerDetailViewModel
    {
        $sub = $detail->subscription;

        $mrr = null;
        if ($sub) {
            $mrr = $sub->interval === 'year'
                ? (int) round($sub->unitAmountCents / 12)
                : $sub->unitAmountCents;
        }

        return new AdminCustomerDetailViewModel(
            name:         $detail->name,
            email:        $detail->email,
            memberSince:  $this->formatDate($detail->memberSince),
            totalSpent:   $this->formatAmount($detail->totalSpentCents, $detail->currency),
            mrr:          $mrr !== null
                ? $this->formatAmount($mrr, $sub->currency) . '/mo'
                : '—',
            description:  $detail->description,
            country:      $detail->country,
            archived:     $detail->archived,
            hasSub:       $sub !== null,
            subStripeId:  $sub?->stripeSubscriptionId ?? '',
            subPlanName:  $sub?->planName ?? '',
            subInterval:  $sub
                ? ($sub->interval === 'year' ? 'Billed annually' : 'Billed monthly')
                : '',
            subNextDate:   $sub?->nextBillingDate
                ? $this->formatDate($sub->nextBillingDate)
                : '—',
            subNextAmount: $sub
                ? $this->formatAmount($sub->nextBillingAmountCents, $sub->currency)
                : '—',
            payments: array_map(fn ($p) => [
                'amount' => $this->formatAmount($p->amountCents, $p->currency),
                'date'   => $this->formatDate($p->paidAt),
                'plan'   => $p->planDescription ?? '—',
            ], $detail->paymentHistory),
        );
    }

    private function present(AdminCustomerResult $customer): AdminCustomerViewModel
    {
        if ($customer->archived) {
            $statusLabel = 'Archived';
            $statusColor = 'text-amber-400 bg-amber-400/10';
        } elseif ($customer->hasActiveSub) {
            $statusLabel = 'Active';
            $statusColor = 'text-emerald-400 bg-emerald-400/10';
        } else {
            $statusLabel = 'No subscription';
            $statusColor = 'text-[#71717a] bg-[#27272a]';
        }

        return new AdminCustomerViewModel(
            id:          $customer->id,
            name:        $customer->name,
            email:       $customer->email,
            description: $customer->description ?? '—',
            country:     $customer->country ?? '—',
            archived:    $customer->archived,
            statusLabel: $statusLabel,
            statusColor: $statusColor,
            createdAt:   Carbon::instance($customer->createdAt)->locale('es')->isoFormat('D MMM YYYY'),
        );
    }

    private function formatDate(\DateTimeImmutable $date): string
    {
        return Carbon::instance($date)->locale('es')->isoFormat('D MMM YYYY');
    }

    private function formatAmount(int $cents, string $currency): string
    {
        $symbol = match (strtoupper($currency)) {
            'MXN'   => '$',
            'USD'   => 'US$',
            'EUR'   => '€',
            default => strtoupper($currency) . ' ',
        };

        return $symbol . number_format($cents / 100, 2);
    }
}
