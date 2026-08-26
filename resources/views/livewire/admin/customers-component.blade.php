<div
    x-data="{ open: @entangle('panelOpen') }"
    x-on:keydown.escape.window="open && $wire.closePanel()"
>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-white">Customers</h1>
            <p class="text-sm text-[#71717a] mt-0.5">All registered users and subscribers.</p>
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
            <option value="inactive">Inactive</option>
            <option value="archived">Archived</option>
            <option value="all">All statuses</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-[#18181b] rounded-xl border border-[#27272a] overflow-hidden overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#09090b]">
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Name</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Email</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Status</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Description</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Country</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Created</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#27272a]">
                @forelse ($customers as $customer)
                    <tr
                        wire:key="customer-{{ $customer->id }}"
                        class="hover:bg-[#27272a]/40 transition-colors"
                    >
                        <td class="px-5 py-4 font-medium text-white">{{ $customer->name }}</td>
                        <td class="px-5 py-4 text-[#a1a1aa]">{{ $customer->email }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $customer->statusColor }}">
                                {{ $customer->statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-[#71717a] max-w-xs truncate">{{ $customer->description }}</td>
                        <td class="px-5 py-4 text-[#a1a1aa] uppercase text-xs font-medium tracking-wide">{{ $customer->country }}</td>
                        <td class="px-5 py-4 text-[#71717a]">{{ $customer->createdAt }}</td>
                        <td class="px-5 py-4">
                            <button
                                wire:click="openPanel({{ $customer->id }})"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-40"
                                wire:target="openPanel({{ $customer->id }})"
                                title="More details"
                                class="p-1.5 text-[#52525b] hover:text-white hover:bg-[#27272a] rounded transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-[#52525b]">
                            No customers found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Slide-over panel --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex justify-end"
        >
            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                x-show="open"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="$wire.closePanel()"
            ></div>

            {{-- Panel --}}
            <div
                class="relative z-10 w-full max-w-xl h-full bg-[#18181b] border-l border-[#27272a] shadow-2xl flex flex-col"
                x-show="open"
                x-transition:enter="transition-transform ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform ease-in-out duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
            >
                {{-- Panel header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-[#27272a] shrink-0">
                    <div class="flex items-center gap-3">
                        <h2 class="text-base font-semibold text-white">Customer details</h2>
                        @if ($panelArchived)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium text-amber-400 bg-amber-400/10">Archived</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            wire:click="syncCustomer"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-not-allowed"
                            wire:target="syncCustomer"
                            title="Sync from Stripe"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors"
                        >
                            <svg wire:loading.remove wire:target="syncCustomer" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <svg wire:loading wire:target="syncCustomer" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span wire:loading.remove wire:target="syncCustomer">Sync from Stripe</span>
                            <span wire:loading wire:target="syncCustomer">Syncing…</span>
                        </button>
                        <button
                            @click="$wire.closePanel()"
                            class="p-1.5 text-[#71717a] hover:text-white hover:bg-[#27272a] rounded transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Panel body --}}
                <div class="flex-1 overflow-y-auto divide-y divide-[#27272a]">

                    {{-- Section 1: Personal information --}}
                    <div class="px-6 py-5 space-y-4">
                        <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">Personal information</p>

                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-[#71717a] mb-0.5">Name</p>
                                <p class="text-sm text-white font-medium">{{ $panelName }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#71717a] mb-0.5">Email</p>
                                <p class="text-sm text-[#a1a1aa]">{{ $panelEmail }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#71717a] mb-0.5">Customer since</p>
                                <p class="text-sm text-[#a1a1aa]">{{ $panelMemberSince }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-1">
                            <div>
                                <label class="text-xs text-[#71717a] block mb-1">Country</label>
                                <input
                                    type="text"
                                    wire:model="editCountry"
                                    maxlength="2"
                                    placeholder="MX"
                                    class="w-full bg-[#27272a] border border-[#3f3f46] text-sm text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#52525b] uppercase placeholder:normal-case placeholder:text-[#52525b]"
                                >
                            </div>
                            <div>
                                <label class="text-xs text-[#71717a] block mb-1">Description</label>
                                <textarea
                                    wire:model="editDescription"
                                    rows="3"
                                    placeholder="Internal notes about this customer…"
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
                                <span wire:loading.remove wire:target="saveCustomer">Save changes</span>
                                <span wire:loading wire:target="saveCustomer">Saving…</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div class="bg-[#09090b] rounded-lg px-4 py-3 border border-[#27272a]">
                                <p class="text-xs text-[#71717a] mb-1">Total spent</p>
                                <p class="text-sm font-semibold text-white">{{ $panelTotalSpent }}</p>
                            </div>
                            <div class="bg-[#09090b] rounded-lg px-4 py-3 border border-[#27272a]">
                                <p class="text-xs text-[#71717a] mb-1">MRR</p>
                                <p class="text-sm font-semibold text-white">{{ $panelMrr }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Subscription --}}
                    <div class="px-6 py-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">Subscription</p>

                            @if ($hasSub)
                                <div x-data="{ menuOpen: false }" class="relative">
                                    <button
                                        @click="menuOpen = !menuOpen"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors"
                                    >
                                        More options
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
                                            Cancel subscription
                                        </button>
                                        <button disabled class="w-full text-left px-4 py-2.5 text-sm text-[#52525b] cursor-not-allowed">
                                            Suspend subscription
                                            <span class="text-xs ml-1 opacity-60">— coming soon</span>
                                        </button>
                                        <button disabled class="w-full text-left px-4 py-2.5 text-sm text-[#52525b] cursor-not-allowed">
                                            View subscription
                                            <span class="text-xs ml-1 opacity-60">— coming soon</span>
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
                                            <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Plan</th>
                                            <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Billing</th>
                                            <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Next billing</th>
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
                            <p class="text-sm text-[#52525b]">No active subscription.</p>
                        @endif
                    </div>

                    {{-- Section 3: Payment history --}}
                    <div class="px-6 py-5 space-y-3">
                        <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">Payment history</p>

                        @if (count($panelPayments) > 0)
                            <div class="rounded-lg border border-[#27272a] overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-[#09090b] border-b border-[#27272a]">
                                            <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Amount</th>
                                            <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Date</th>
                                            <th class="text-left px-4 py-2.5 text-xs font-medium text-[#71717a]">Plan</th>
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
                            <p class="text-sm text-[#52525b]">No payment history.</p>
                        @endif
                    </div>

                    {{-- Section 4: Danger zone --}}
                    <div class="px-6 py-5 space-y-3">
                        <p class="text-[10px] font-semibold text-[#52525b] uppercase tracking-widest">Actions</p>

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
                                <span wire:loading.remove wire:target="restoreCustomer">Restore customer</span>
                                <span wire:loading wire:target="restoreCustomer">Restoring…</span>
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
                                <span wire:loading.remove wire:target="archiveCustomer">Archive customer</span>
                                <span wire:loading wire:target="archiveCustomer">Archiving…</span>
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </template>

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
                    {{-- Warning icon --}}
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-400/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white">Archive customer?</h2>
                            <p class="text-xs text-[#71717a] mt-0.5">{{ $panelName }}</p>
                        </div>
                    </div>

                    <div class="bg-amber-400/5 border border-amber-400/20 rounded-xl px-4 py-3 mb-5">
                        <p class="text-sm text-amber-300 leading-relaxed">
                            This customer has an <span class="font-semibold">active subscription</span> ({{ $panelSubPlanName }}). Archiving will not cancel their Stripe subscription — they will continue to be billed.
                        </p>
                        <p class="text-xs text-amber-400/70 mt-2">
                            If you also want to stop billing, cancel the subscription first.
                        </p>
                    </div>

                    <p class="text-sm text-[#a1a1aa]">Are you sure you want to archive this customer?</p>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-[#27272a]">
                    <button
                        wire:click="closeArchiveModal"
                        class="px-4 py-2 text-sm font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="confirmArchive"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-60 cursor-not-allowed"
                        wire:target="confirmArchive"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors"
                    >
                        <span wire:loading.remove wire:target="confirmArchive">Yes, archive customer</span>
                        <span wire:loading wire:target="confirmArchive">Archiving…</span>
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

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-[#27272a]">
                    <button wire:click="closeCancelModal" class="px-4 py-2 text-sm font-medium text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] rounded-lg transition-colors">
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
