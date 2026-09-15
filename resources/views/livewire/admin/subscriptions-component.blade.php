<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-white">Subscriptions</h1>
            <p class="text-sm text-[#71717a] mt-0.5">Active and historical subscriber records.</p>
        </div>
        <button
            wire:click="import"
            wire:loading.attr="disabled"
            wire:loading.class="opacity-60 cursor-not-allowed"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#3f3f46] hover:bg-[#52525b] rounded-lg transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span wire:loading.remove wire:target="import">Import from Stripe</span>
            <span wire:loading wire:target="import">Importing…</span>
        </button>
    </div>

    {{-- Filter bar --}}
    <div class="flex items-center gap-2 mb-4">
        <span class="text-sm text-[#71717a]">Filter by</span>
        <select
            wire:model.live="statusFilter"
            class="bg-[#27272a] border border-[#3f3f46] text-sm text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#52525b] cursor-pointer"
        >
            <option value="active">Active</option>
            <option value="trialing">Trialing</option>
            <option value="past_due">Past due</option>
            <option value="canceled">Canceled</option>
            <option value="all">All statuses</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-[#18181b] rounded-xl border border-[#27272a] overflow-hidden overflow-x-auto">
        <table class="w-full text-sm min-w-[960px]">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#09090b]">
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Customer</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Status</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Plan</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Payment method</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">Monthly avg.</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">Annual avg.</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Since</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#27272a]">
                @forelse ($subscriptions as $sub)
                    <tr
                        wire:key="sub-{{ $sub->id }}"
                        class="hover:bg-[#27272a]/40 transition-colors {{ $sub->canceledAt ? 'opacity-50' : '' }}"
                    >
                        <td class="px-5 py-4">
                            <div class="font-medium text-white">{{ $sub->userName }}</div>
                            <div class="text-xs text-[#71717a] mt-0.5">{{ $sub->userEmail }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full {{ $sub->statusBadgeClass }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sub->statusDotClass }}"></span>
                                {{ $sub->statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-[#a1a1aa]">{{ $sub->planName }}</td>
                        <td class="px-5 py-4 text-[#a1a1aa]">{{ $sub->paymentMethod }}</td>
                        <td class="px-5 py-4 text-right text-[#a1a1aa]">{{ $sub->formattedMonthlyAverage }}</td>
                        <td class="px-5 py-4 text-right text-[#a1a1aa]">{{ $sub->formattedAnnualAverage }}</td>
                        <td class="px-5 py-4 text-[#a1a1aa]">
                            {{ $sub->subscribedAt }}
                            @if ($sub->canceledAt)
                                <div class="text-xs text-red-500/70 mt-0.5">Canceled {{ $sub->canceledAt }}</div>
                            @elseif ($sub->endsAt)
                                <div class="text-xs text-amber-500/70 mt-0.5">Cancels on {{ $sub->endsAt }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if ($sub->stripeId && ! $sub->canceledAt && ! $sub->endsAt)
                                <button
                                    wire:click="openCancelModal('{{ $sub->stripeId }}')"
                                    wire:loading.attr="disabled"
                                    title="Cancel subscription"
                                    class="p-1.5 text-[#52525b] hover:text-red-400 hover:bg-red-900/20 rounded transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-[#52525b]">
                            No subscriptions yet. Use "Import from Stripe" to load existing records.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cancel modal --}}
    @if ($cancelModalOpen)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            x-data
            x-on:keydown.escape.window="$wire.closeCancelModal()"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeCancelModal"></div>

            {{-- Dialog --}}
            <div class="relative z-10 w-full max-w-md bg-[#18181b] border border-[#27272a] rounded-2xl shadow-2xl">
                {{-- Header --}}
                <div class="flex items-start justify-between px-6 pt-6 pb-4 border-b border-[#27272a]">
                    <div>
                        <h2 class="text-base font-semibold text-white">Cancel subscription</h2>
                        <p class="text-xs text-[#71717a] mt-0.5 font-mono">{{ $cancelStripeId }}</p>
                    </div>
                    <button wire:click="closeCancelModal" class="text-[#71717a] hover:text-white transition-colors -mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">
                    {{-- Cancellation timing --}}
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

                    {{-- Refund (solo para cancelación inmediata) --}}
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
