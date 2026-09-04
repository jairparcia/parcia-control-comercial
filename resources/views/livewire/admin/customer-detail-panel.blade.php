<div
    x-data="{ open: @entangle('panelOpen') }"
    x-on:keydown.escape.window="open && $wire.closePanel()"
>
    <x-slide-over :title="__('admin.customer_details')" max-width="max-w-xl" close-action="$wire.closePanel()">

        {{-- Badge: Archived --}}
        <x-slot:badge>
            @if ($panelArchived)
                <span class="px-2 py-0.5 rounded-full text-xs font-medium text-amber-400 bg-amber-400/10">{{ __('common.status_archived') }}</span>
            @endif
        </x-slot:badge>

        {{-- Actions: Sync from Stripe --}}
        <x-slot:actions>
            <button
                wire:click="syncCustomer"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-not-allowed"
                wire:target="syncCustomer"
                title="{{ __('admin.sync_from_stripe') }}"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors"
            >
                <svg wire:loading.remove wire:target="syncCustomer" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="syncCustomer" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span wire:loading.remove wire:target="syncCustomer">{{ __('admin.sync_from_stripe') }}</span>
                <span wire:loading wire:target="syncCustomer">{{ __('admin.syncing') }}</span>
            </button>
        </x-slot:actions>

        {{-- Section 1: Personal information --}}
        <div class="px-6 py-5 space-y-4">
            <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">{{ __('admin.personal_information') }}</p>

            <div class="space-y-3">
                <div>
                    <p class="text-xs text-[#71717a] mb-0.5">{{ __('common.name') }}</p>
                    <p class="text-sm text-white font-medium">{{ $panelName }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#71717a] mb-0.5">{{ __('common.email') }}</p>
                    <p class="text-sm text-[#a1a1aa]">{{ $panelEmail }}</p>
                </div>
                <div>
                    <p class="text-xs text-[#71717a] mb-0.5">{{ __('admin.customer_since') }}</p>
                    <p class="text-sm text-[#a1a1aa]">{{ $panelMemberSince }}</p>
                </div>
            </div>

            <div class="space-y-3 pt-1">
                <div>
                    <label class="text-xs text-[#71717a] block mb-1">{{ __('common.country') }}</label>
                    <input
                        type="text"
                        wire:model="editCountry"
                        maxlength="2"
                        placeholder="MX"
                        class="w-full bg-[#27272a] border border-[#3f3f46] text-sm text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#52525b] uppercase placeholder:normal-case placeholder:text-[#52525b]"
                    >
                </div>
                <div>
                    <label class="text-xs text-[#71717a] block mb-1">{{ __('common.description') }}</label>
                    <textarea
                        wire:model="editDescription"
                        rows="3"
                        placeholder="{{ __('admin.internal_notes') }}"
                        class="w-full bg-[#27272a] border border-[#3f3f46] text-sm text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#52525b] resize-none placeholder:text-[#52525b]"
                    ></textarea>
                </div>
                <button
                    wire:click="saveCustomer"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    wire:target="saveCustomer"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-[#3f3f46] hover:bg-[#52525b] rounded-lg transition-colors"
                >
                    <span wire:loading.remove wire:target="saveCustomer">{{ __('common.save_changes') }}</span>
                    <span wire:loading wire:target="saveCustomer">{{ __('common.saving') }}</span>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
                <div class="bg-[#09090b] rounded-lg px-4 py-3 border border-[#27272a]">
                    <p class="text-xs text-[#71717a] mb-1">{{ __('admin.total_spent') }}</p>
                    <p class="text-sm font-semibold text-white">{{ $panelTotalSpent }}</p>
                </div>
                <div class="bg-[#09090b] rounded-lg px-4 py-3 border border-[#27272a]">
                    <p class="text-xs text-[#71717a] mb-1">{{ __('admin.mrr') }}</p>
                    <p class="text-sm font-semibold text-white">{{ $panelMrr }}</p>
                </div>
            </div>
        </div>

        {{-- Section 2: Subscription --}}
        <div class="px-6 py-5 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">{{ __('common.subscription') }}</p>

                @if ($hasSub)
                    <div x-data="{ menuOpen: false }" class="relative">
                        <button
                            @click="menuOpen = !menuOpen"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors"
                        >
                            {{ __('admin.more_options') }}
                            <svg class="w-3 h-3 transition-transform duration-150" :class="menuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div
                            x-show="menuOpen"
                            @click.outside="menuOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-1.5 w-52 bg-[#27272a] border border-[#3f3f46] rounded-xl shadow-xl z-10 py-1 origin-top-right"
                        >
                            <button
                                @click="menuOpen = false; $wire.openCancelModal('{{ $panelSubStripeId }}')"
                                class="w-full text-left px-4 py-2.5 text-sm text-red-400 hover:bg-[#3f3f46] transition-colors"
                            >
                                {{ __('admin.cancel_sub_menu') }}
                            </button>
                            <button disabled class="w-full text-left px-4 py-2.5 text-sm text-[#52525b] cursor-not-allowed">
                                {{ __('admin.suspend_sub_menu') }}
                                <span class="text-xs ml-1 opacity-60">— {{ __('admin.coming_soon') }}</span>
                            </button>
                            <button disabled class="w-full text-left px-4 py-2.5 text-sm text-[#52525b] cursor-not-allowed">
                                {{ __('admin.view_sub_menu') }}
                                <span class="text-xs ml-1 opacity-60">— {{ __('admin.coming_soon') }}</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            @if ($hasSub)
                <div class="rounded-lg border border-[#27272a] overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#09090b] border-b border-[#27272a]">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.plan') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.billing') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('admin.next_billing') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-4 py-3 font-medium text-white">{{ $panelSubPlanName }}</td>
                                <td class="px-4 py-3 text-[#a1a1aa]">{{ $panelSubInterval }}</td>
                                <td class="px-4 py-3 text-[#a1a1aa]">
                                    {{ $panelSubNextDate }}
                                    @if ($panelSubNextAmount !== '—')
                                        <span class="block text-xs text-[#71717a] mt-0.5">{{ $panelSubNextAmount }}</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-[#52525b]">{{ __('admin.no_active_subscription') }}</p>
            @endif
        </div>

        {{-- Section 3: Payment history --}}
        <div class="px-6 py-5 space-y-3">
            <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">{{ __('admin.payment_history') }}</p>

            @if (count($panelPayments) > 0)
                <div class="rounded-lg border border-[#27272a] overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#09090b] border-b border-[#27272a]">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.amount') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.date') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">{{ __('common.plan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#27272a]">
                            @foreach ($panelPayments as $payment)
                                <tr class="hover:bg-[#27272a]/40 transition-colors">
                                    <td class="px-4 py-3 font-medium text-white">{{ $payment['amount'] }}</td>
                                    <td class="px-4 py-3 text-[#71717a]">{{ $payment['date'] }}</td>
                                    <td class="px-4 py-3 text-[#71717a]">{{ $payment['plan'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-[#52525b]">{{ __('admin.no_payment_history') }}</p>
            @endif
        </div>

        {{-- Section 4: Actions --}}
        <div class="px-6 py-5 space-y-3">
            <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">{{ __('admin.actions') }}</p>

            @if ($panelArchived)
                <button
                    wire:click="restoreCustomer"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    wire:target="restoreCustomer"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-emerald-400 hover:text-white hover:bg-emerald-600 border border-emerald-600/40 rounded-lg transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span wire:loading.remove wire:target="restoreCustomer">{{ __('admin.restore_customer') }}</span>
                    <span wire:loading wire:target="restoreCustomer">{{ __('admin.restoring') }}</span>
                </button>
            @else
                <button
                    wire:click="archiveCustomer"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    wire:target="archiveCustomer"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-amber-400 hover:text-white hover:bg-amber-600 border border-amber-600/40 rounded-lg transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8"/>
                    </svg>
                    <span wire:loading.remove wire:target="archiveCustomer">{{ __('admin.archive_customer') }}</span>
                    <span wire:loading wire:target="archiveCustomer">{{ __('admin.archiving') }}</span>
                </button>
            @endif
        </div>

    </x-slide-over>

    {{-- Archive confirmation modal --}}
    @if ($archiveModalOpen)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center px-4"
            x-data
            x-on:keydown.escape.window="$wire.closeArchiveModal()"
        >
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeArchiveModal"></div>

            <div class="relative z-10 w-full max-w-md bg-[#18181b] border border-[#27272a] rounded-2xl shadow-2xl">
                <div class="px-6 pt-6 pb-5">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-400/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white">{{ __('admin.archive_customer_confirm') }}</h2>
                            <p class="text-xs text-[#71717a] mt-0.5">{{ $panelName }}</p>
                        </div>
                    </div>

                    <div class="bg-amber-400/5 border border-amber-400/20 rounded-xl px-4 py-3 mb-5">
                        <p class="text-sm text-amber-300 leading-relaxed">
                            {{ __('admin.archive_warning') }} <span class="font-semibold">{{ __('admin.archive_warning_bold') }}</span> ({{ $panelSubPlanName }}). {{ __('admin.archive_warning_detail') }}
                        </p>
                        <p class="text-xs text-amber-400/70 mt-2">
                            {{ __('admin.archive_stop_billing') }}
                        </p>
                    </div>

                    <p class="text-sm text-[#a1a1aa]">{{ __('admin.archive_confirm_question') }}</p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-[#27272a]">
                    <button wire:click="closeArchiveModal" class="px-4 py-2 text-sm font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors">
                        {{ __('common.cancel') }}
                    </button>
                    <button
                        wire:click="confirmArchive"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        wire:target="confirmArchive"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors"
                    >
                        <span wire:loading.remove wire:target="confirmArchive">{{ __('admin.archive_confirm_btn') }}</span>
                        <span wire:loading wire:target="confirmArchive">{{ __('admin.archiving') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

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
                        <h2 class="text-base font-semibold text-white">{{ __('admin.cancel_sub_menu') }}</h2>
                        <p class="text-xs text-[#71717a] mt-0.5 font-mono">{{ $cancelStripeId }}</p>
                    </div>
                    <button wire:click="closeCancelModal" class="text-[#71717a] hover:text-white transition-colors -mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">
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
                                    <p class="text-xs text-[#71717a] mt-0.5">{!! __('common.access_continues_until', ['date' => '<span class="text-[#a1a1aa]">'.e($cancelPeriodEnd).'</span>']) !!}</p>
                                </div>
                            </label>
                        </div>
                    </div>

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
                                        <p class="text-sm font-medium text-white">{!! __('common.last_payment_amount', ['amount' => '<span class="text-[#a1a1aa]">'.e($cancelLastPayment).'</span>']) !!}</p>
                                        <p class="text-xs text-[#71717a] mt-0.5">{{ __('common.refund_full_desc') }}</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                    {{ $cancelRefundType === 'prorated' ? 'border-[#52525b] bg-[#27272a]/50' : 'border-[#27272a] hover:border-[#3f3f46]' }}">
                                    <input type="radio" wire:model.live="cancelRefundType" value="prorated" class="mt-0.5 accent-white">
                                    <div>
                                        <p class="text-sm font-medium text-white">{!! __('common.prorated_amount', ['amount' => '<span class="text-[#a1a1aa]">'.e($cancelProratedAmount).'</span>']) !!}</p>
                                        <p class="text-xs text-[#71717a] mt-0.5">{{ __('common.prorated_desc', ['days' => $cancelProratedDays, 'unit' => $cancelProratedDays === 1 ? __('common.day') : __('common.days')]) }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-[#27272a]">
                    <button wire:click="closeCancelModal" class="px-4 py-2 text-sm font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors">
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
