<?php

namespace App\Http\Presenters\Admin;

use App\Domain\Admin\Results\AdminInvoiceResult;

class AdminInvoicePresenter
{
    /** @return AdminInvoiceViewModel[] */
    public function presentAll(array $invoices): array
    {
        return array_map(fn (AdminInvoiceResult $i) => $this->present($i), $invoices);
    }

    private function present(AdminInvoiceResult $invoice): AdminInvoiceViewModel
    {
        return new AdminInvoiceViewModel(
            stripeId:         $invoice->stripeId,
            invoiceNumber:    $invoice->invoiceNumber ?? '—',
            formattedTotal:   $this->formatAmount($invoice->totalCents, $invoice->currency),
            status:           $invoice->status,
            statusLabel:      $this->statusLabel($invoice->status),
            statusBadgeClass: $this->statusBadgeClass($invoice->status),
            frequency:        $this->formatFrequency($invoice->interval, $invoice->intervalCount),
            customerName:     $invoice->customerName ?? '—',
            customerEmail:    $invoice->customerEmail ?? '',
            dueDate:              $invoice->dueDate ? $this->formatDate($invoice->dueDate) : '—',
            date:                 $this->formatDate($invoice->createdAt),
            userId:               $invoice->userId,
            stripeSubscriptionId: $invoice->stripeSubscriptionId,
        );
    }

    private function formatAmount(int $cents, string $currency): string
    {
        $amount = number_format($cents / 100, 2, '.', ',');
        $prefix = match (strtoupper($currency)) {
            'MXN'   => 'MX$',
            'USD'   => 'US$',
            default => strtoupper($currency) . ' ',
        };

        return $prefix . $amount;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'paid'          => 'Pagada',
            'open'          => 'Pendiente',
            'draft'         => 'Borrador',
            'uncollectible' => 'Incobrable',
            'void'          => 'Anulada',
            default         => ucfirst($status),
        };
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'paid'          => 'bg-emerald-900/30 text-emerald-400',
            'open'          => 'bg-blue-900/30 text-blue-400',
            'draft'         => 'bg-[#27272a] text-[#71717a]',
            'uncollectible' => 'bg-red-900/30 text-red-400',
            'void'          => 'bg-amber-900/30 text-amber-400',
            default         => 'bg-[#27272a] text-[#71717a]',
        };
    }

    private function formatFrequency(?string $interval, int $count): string
    {
        if (! $interval) {
            return '—';
        }

        if ($interval === 'month' && $count === 1) return 'Mensual';
        if ($interval === 'year'  && $count === 1) return 'Anual';
        if ($interval === 'week'  && $count === 1) return 'Semanal';
        if ($interval === 'day'   && $count === 1) return 'Diario';
        if ($interval === 'month' && $count === 3) return 'Trimestral';
        if ($interval === 'month' && $count === 6) return 'Semestral';

        $label = match ($interval) {
            'month' => 'mes',
            'year'  => 'año',
            'week'  => 'semana',
            'day'   => 'día',
            default => $interval,
        };

        return "Cada {$count} {$label}" . ($count > 1 ? 'es' : '');
    }

    private function formatDate(\DateTimeImmutable $date): string
    {
        $months = ['ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sep.', 'oct.', 'nov.', 'dic.'];
        $month  = $months[(int) $date->format('n') - 1];

        return $date->format('j') . ' ' . $month . ' ' . $date->format('Y');
    }
}
