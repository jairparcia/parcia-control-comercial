<div
    x-data="{ open: @entangle('panelOpen') }"
    x-on:keydown.escape.window="open && !$wire.cancelModalOpen && $wire.close()"
>
    <x-slide-over :title="__('admin.subscription_details')" max-width="max-w-2xl" close-action="$wire.close()">

        {{-- Badge: status --}}
        <x-slot:badge>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
        </x-slot:badge>

        {{-- Actions: Cancel --}}
        <x-slot:actions>
            <button
                wire:click="openCancelModal"
                wire:loading.attr="disabled"
                title="{{ __('admin.cancel_subscription') }}"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-red-400 hover:text-white hover:bg-red-900/30 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                {{ __('common.cancel') }}
            </button>
        </x-slot:actions>

        {{-- ─── Section 1: General info ─────────────────────────────── --}}
        <div class="px-6 py-5 space-y-4">
            <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">{{ __('admin.general_information') }}</p>

            <div class="divide-y divide-[#27272a]">
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">{{ __('common.customer') }}</span>
                    <div class="text-right">
                        <p class="text-sm text-white font-medium">{{ $userName }}</p>
                        <p class="text-xs text-[#71717a] mt-0.5">{{ $userEmail }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">{{ __('common.status') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">{{ __('common.plan') }}</span>
                    <span class="text-sm text-[#a1a1aa]">{{ $planName }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">{{ __('admin.billing_interval') }}</span>
                    <span class="text-sm text-[#a1a1aa]">{{ $interval }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">{{ __('common.amount') }}</span>
                    <span class="text-sm text-white font-medium">{{ $formattedAmount }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">{{ __('admin.subscribed_since') }}</span>
                    <span class="text-sm text-[#a1a1aa]">{{ $subscribedAt }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">{{ __('admin.current_period') }}</span>
                    <span class="text-sm text-[#a1a1aa]">{{ $currentPeriod }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">Stripe ID</span>
                    <span class="text-xs text-[#52525b] font-mono">{{ $stripeId }}</span>
                </div>
            </div>
        </div>

        {{-- ─── Section 2: Upcoming invoice ─────────────────────────── --}}
        @if ($upcomingInvoice)
            <div class="px-6 py-5 space-y-4 border-t border-[#27272a]">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">{{ __('admin.upcoming_invoice') }}</p>
                    <span class="text-xs text-[#71717a]">{{ __('admin.due_date', ['date' => $upcomingInvoice['nextBillingDate']]) }}</span>
                </div>

                {{-- Line items table --}}
                <div class="rounded-lg border border-[#27272a] overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#09090b] border-b border-[#27272a]">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.description') }}</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('admin.qty') }}</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('admin.unit_price') }}</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-[#27272a]">
                                <td class="px-4 py-3 text-[#a1a1aa]">{{ $upcomingInvoice['description'] }}</td>
                                <td class="px-4 py-3 text-right text-[#a1a1aa]">{{ $upcomingInvoice['quantity'] }}</td>
                                <td class="px-4 py-3 text-right text-[#a1a1aa]">{{ $upcomingInvoice['unitAmount'] }}</td>
                                <td class="px-4 py-3 text-right text-white">{{ $upcomingInvoice['lineAmount'] }}</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Totals summary --}}
                    <div class="px-4 py-3 space-y-1.5 bg-[#09090b]">
                        <div class="flex justify-between text-xs text-[#71717a]">
                            <span>{{ __('admin.subtotal') }}</span>
                            <span>{{ $upcomingInvoice['subtotal'] }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-[#71717a]">
                            <span>{{ __('admin.tax') }}</span>
                            <span>{{ $upcomingInvoice['tax'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-white pt-1.5 border-t border-[#27272a]">
                            <span>{{ __('admin.total') }}</span>
                            <span>{{ $upcomingInvoice['total'] }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-[#71717a]">
                            <span>{{ __('admin.amount_paid') }}</span>
                            <span>{{ $upcomingInvoice['amountPaid'] }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-medium text-emerald-400">
                            <span>{{ __('admin.amount_remaining') }}</span>
                            <span>{{ $upcomingInvoice['amountRemaining'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ─── Section 3: Invoice history ──────────────────────────── --}}
        <div class="px-6 py-5 space-y-4 border-t border-[#27272a]">
            <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">{{ __('admin.invoices') }}</p>

            @if (count($invoices) > 0)
                <div class="rounded-lg border border-[#27272a] overflow-hidden overflow-x-auto">
                    <table class="w-full text-sm min-w-[480px]">
                        <thead>
                            <tr class="bg-[#09090b] border-b border-[#27272a]">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.status') }}</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.amount') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('admin.frequency') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('admin.invoice_no') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#27272a]">
                            @foreach ($invoices as $invoice)
                                <tr class="hover:bg-[#27272a]/40 transition-colors">
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $invoice['statusBadgeClass'] }}">
                                            {{ $invoice['statusLabel'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-white font-medium">{{ $invoice['amount'] }}</td>
                                    <td class="px-4 py-3 text-[#a1a1aa]">{{ $invoice['interval'] }}</td>
                                    <td class="px-4 py-3 text-xs text-[#52525b] font-mono">{{ $invoice['number'] }}</td>
                                    <td class="px-4 py-3 text-[#a1a1aa] whitespace-nowrap">{{ $invoice['date'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-[#52525b]">{{ __('admin.no_invoices') }}</p>
            @endif
        </div>

    </x-slide-over>

    {{-- Cancel modal --}}
    @if ($cancelModalOpen)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center px-4"
            x-data
            x-on:keydown.escape.window="$wire.closeCancelModal()"
        >
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeCancelModal"></div>

            <div class="relative z-10 w-full max-w-md bg-[#18181b] border border-[#27272a] rounded-2xl shadow-2xl">
                <div class="flex items-start justify-between px-6 pt-6 pb-4 border-b border-[#27272a]">
                    <div>
                        <h2 class="text-base font-semibold text-white">{{ __('admin.cancel_subscription') }}</h2>
                        <p class="text-xs text-[#71717a] mt-0.5 font-mono">{{ $stripeId }}</p>
                    </div>
                    <button wire:click="closeCancelModal" class="text-[#71717a] hover:text-white transition-colors -mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">
                    {{-- Timing --}}
                    <div>
                        <p class="text-xs font-medium text-[#a1a1aa] uppercase tracking-wide mb-3">{{ __('common.cancellation_timing') }}</p>
                        <div class="space-y-2">
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                {{ $cancelTiming === 'immediately' ? 'border-red-500/50 bg-red-900/10' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                <input type="radio" wire:model.live="cancelTiming" value="immediately" class="mt-0.5 accent-red-500">
                                <div>
                                    <p class="text-sm font-medium text-white">{{ __('common.cancel_immediately_label') }}</p>
                                    <p class="text-xs text-[#71717a] mt-0.5">{{ __('common.sub_ends_now_desc') }}</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                {{ $cancelTiming === 'period_end' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                <input type="radio" wire:model.live="cancelTiming" value="period_end" class="mt-0.5 accent-white">
                                <div>
                                    <p class="text-sm font-medium text-white">{{ __('common.cancel_at_period_end') }}</p>
                                    <p class="text-xs text-[#71717a] mt-0.5">
                                        {!! __('common.access_continues_until', ['date' => '<span class="text-[#a1a1aa]">'.e($cancelPeriodEnd).'</span>']) !!}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Refund (only for immediate cancellation) --}}
                    @if ($cancelTiming === 'immediately')
                        <div>
                            <p class="text-xs font-medium text-[#a1a1aa] uppercase tracking-wide mb-3">{{ __('common.refund') }}</p>
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                    {{ $cancelRefundType === 'none' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                    <input type="radio" wire:model.live="cancelRefundType" value="none" class="mt-0.5 accent-white">
                                    <div>
                                        <p class="text-sm font-medium text-white">{{ __('common.no_refund') }}</p>
                                        <p class="text-xs text-[#71717a] mt-0.5">{{ __('common.no_refund_desc') }}</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                    {{ $cancelRefundType === 'full' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                    <input type="radio" wire:model.live="cancelRefundType" value="full" class="mt-0.5 accent-white">
                                    <div>
                                        <p class="text-sm font-medium text-white">
                                            {!! __('common.last_payment_amount', ['amount' => '<span class="text-[#a1a1aa]">'.e($cancelLastPayment).'</span>']) !!}
                                        </p>
                                        <p class="text-xs text-[#71717a] mt-0.5">{{ __('common.refund_full_desc') }}</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                    {{ $cancelRefundType === 'prorated' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                    <input type="radio" wire:model.live="cancelRefundType" value="prorated" class="mt-0.5 accent-white">
                                    <div>
                                        <p class="text-sm font-medium text-white">
                                            {!! __('common.prorated_amount', ['amount' => '<span class="text-[#a1a1aa]">'.e($cancelProratedAmount).'</span>']) !!}
                                        </p>
                                        <p class="text-xs text-[#71717a] mt-0.5">
                                            {{ __('common.prorated_desc', ['days' => $cancelProratedDays, 'unit' => $cancelProratedDays === 1 ? __('common.day') : __('common.days')]) }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-[#27272a]">
                    <button
                        wire:click="closeCancelModal"
                        class="px-4 py-2 text-sm font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors"
                    >
                        {{ __('common.keep_subscription') }}
                    </button>
                    <button
                        wire:click="confirmCancel"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
                    >
                        <span wire:loading.remove wire:target="confirmCancel">
                            {{ $cancelTiming === 'immediately' ? __('common.cancel_immediately_btn') : __('common.schedule_cancellation') }}
                        </span>
                        <span wire:loading wire:target="confirmCancel">{{ __('common.processing') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
