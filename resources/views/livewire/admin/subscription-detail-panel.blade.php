<div
    x-data="{ open: @entangle('panelOpen') }"
    x-on:keydown.escape.window="open && !$wire.cancelModalOpen && $wire.close()"
>
    <x-slide-over title="Subscription details" max-width="max-w-2xl" close-action="$wire.close()">

        {{-- Badge: status --}}
        <x-slot:badge>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
        </x-slot:badge>

        {{-- Actions: Cancel --}}
        <x-slot:actions>
            <button
                wire:click="openCancelModal"
                wire:loading.attr="disabled"
                title="Cancel subscription"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-red-400 hover:text-white hover:bg-red-900/30 rounded-lg transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Cancel
            </button>
        </x-slot:actions>

        {{-- ─── Section 1: General info ─────────────────────────────── --}}
        <div class="px-6 py-5 space-y-4">
            <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">General information</p>

            <div class="divide-y divide-[#27272a]">
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">Customer</span>
                    <div class="text-right">
                        <p class="text-sm text-white font-medium">{{ $userName }}</p>
                        <p class="text-xs text-[#71717a] mt-0.5">{{ $userEmail }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">Status</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">Plan</span>
                    <span class="text-sm text-[#a1a1aa]">{{ $planName }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">Billing interval</span>
                    <span class="text-sm text-[#a1a1aa]">{{ $interval }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">Amount</span>
                    <span class="text-sm text-white font-medium">{{ $formattedAmount }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">Subscribed since</span>
                    <span class="text-sm text-[#a1a1aa]">{{ $subscribedAt }}</span>
                </div>
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-xs text-[#71717a]">Current period</span>
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
                    <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">Upcoming invoice</p>
                    <span class="text-xs text-[#71717a]">Due {{ $upcomingInvoice['nextBillingDate'] }}</span>
                </div>

                {{-- Line items table --}}
                <div class="rounded-lg border border-[#27272a] overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#09090b] border-b border-[#27272a]">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Description</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-[#71717a]">Qty</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-[#71717a]">Unit price</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-[#71717a]">Amount</th>
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
                            <span>Subtotal</span>
                            <span>{{ $upcomingInvoice['subtotal'] }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-[#71717a]">
                            <span>Tax</span>
                            <span>{{ $upcomingInvoice['tax'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-white pt-1.5 border-t border-[#27272a]">
                            <span>Total</span>
                            <span>{{ $upcomingInvoice['total'] }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-[#71717a]">
                            <span>Amount paid</span>
                            <span>{{ $upcomingInvoice['amountPaid'] }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-medium text-emerald-400">
                            <span>Amount remaining</span>
                            <span>{{ $upcomingInvoice['amountRemaining'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ─── Section 3: Invoice history ──────────────────────────── --}}
        <div class="px-6 py-5 space-y-4 border-t border-[#27272a]">
            <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">Invoices</p>

            @if (count($invoices) > 0)
                <div class="rounded-lg border border-[#27272a] overflow-hidden overflow-x-auto">
                    <table class="w-full text-sm min-w-[480px]">
                        <thead>
                            <tr class="bg-[#09090b] border-b border-[#27272a]">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Status</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-[#71717a]">Amount</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Frequency</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Invoice no.</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Date</th>
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
                <p class="text-sm text-[#52525b]">No invoices found.</p>
            @endif
        </div>

    </x-slide-over>

    {{-- Cancel modal (nested, lives inside the panel component) --}}
    @if ($cancelModalOpen)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center px-4"
            x-data
            x-on:keydown.escape.window="$wire.closeCancelModal()"
        >
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeCancelModal"></div>

            <div class="relative z-10 w-full max-w-md bg-[#18181b] border border-[#27272a] rounded-2xl shadow-2xl">
                {{-- Header --}}
                <div class="flex items-start justify-between px-6 pt-6 pb-4 border-b border-[#27272a]">
                    <div>
                        <h2 class="text-base font-semibold text-white">Cancel subscription</h2>
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
                        <p class="text-xs font-medium text-[#a1a1aa] uppercase tracking-wide mb-3">Cancellation timing</p>
                        <div class="space-y-2">
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                {{ $cancelTiming === 'immediately' ? 'border-red-500/50 bg-red-900/10' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                <input type="radio" wire:model.live="cancelTiming" value="immediately" class="mt-0.5 accent-red-500">
                                <div>
                                    <p class="text-sm font-medium text-white">Immediately</p>
                                    <p class="text-xs text-[#71717a] mt-0.5">Subscription ends now. Customer loses access right away.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                {{ $cancelTiming === 'period_end' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                <input type="radio" wire:model.live="cancelTiming" value="period_end" class="mt-0.5 accent-white">
                                <div>
                                    <p class="text-sm font-medium text-white">At end of current period</p>
                                    <p class="text-xs text-[#71717a] mt-0.5">Access continues until <span class="text-[#a1a1aa]">{{ $cancelPeriodEnd }}</span>.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Refund (only for immediate cancellation) --}}
                    @if ($cancelTiming === 'immediately')
                        <div>
                            <p class="text-xs font-medium text-[#a1a1aa] uppercase tracking-wide mb-3">Refund</p>
                            <div class="space-y-2">
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                    {{ $cancelRefundType === 'none' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                    <input type="radio" wire:model.live="cancelRefundType" value="none" class="mt-0.5 accent-white">
                                    <div>
                                        <p class="text-sm font-medium text-white">No refund</p>
                                        <p class="text-xs text-[#71717a] mt-0.5">No money is returned to the customer.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                    {{ $cancelRefundType === 'full' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                    <input type="radio" wire:model.live="cancelRefundType" value="full" class="mt-0.5 accent-white">
                                    <div>
                                        <p class="text-sm font-medium text-white">Last payment — <span class="text-[#a1a1aa]">{{ $cancelLastPayment }}</span></p>
                                        <p class="text-xs text-[#71717a] mt-0.5">Refund the full amount of the most recent invoice.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                    {{ $cancelRefundType === 'prorated' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                    <input type="radio" wire:model.live="cancelRefundType" value="prorated" class="mt-0.5 accent-white">
                                    <div>
                                        <p class="text-sm font-medium text-white">Prorated — <span class="text-[#a1a1aa]">{{ $cancelProratedAmount }}</span></p>
                                        <p class="text-xs text-[#71717a] mt-0.5">Refund for {{ $cancelProratedDays }} unused {{ $cancelProratedDays === 1 ? 'day' : 'days' }} remaining in the period.</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-[#27272a]">
                    <button
                        wire:click="closeCancelModal"
                        class="px-4 py-2 text-sm font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors"
                    >
                        Keep subscription
                    </button>
                    <button
                        wire:click="confirmCancel"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
                    >
                        <span wire:loading.remove wire:target="confirmCancel">
                            {{ $cancelTiming === 'immediately' ? 'Cancel immediately' : 'Schedule cancellation' }}
                        </span>
                        <span wire:loading wire:target="confirmCancel">Processing…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
