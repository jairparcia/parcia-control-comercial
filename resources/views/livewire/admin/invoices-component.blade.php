<div>
    {{-- ── Header ─────────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-white tracking-tight">Invoices</h1>
            <p class="text-sm text-[#71717a] mt-0.5">All invoices from your Stripe account.</p>
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

    {{-- ── Status filter ───────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-4">
        <span class="text-sm text-[#71717a]">Filter by</span>
        <select
            wire:model.live="statusFilter"
            class="bg-[#27272a] border border-[#3f3f46] text-sm text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#52525b] cursor-pointer"
        >
            <option value="paid">Paid</option>
            <option value="open">Open</option>
            <option value="draft">Draft</option>
            <option value="uncollectible">Uncollectible</option>
            <option value="void">Void</option>
            <option value="all">All statuses</option>
        </select>
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-[#27272a] overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#18181b]">
                    <th class="text-left px-4 py-3 text-xs font-medium text-[#52525b] uppercase tracking-wide">Invoice</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-[#52525b] uppercase tracking-wide">Customer</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-[#52525b] uppercase tracking-wide">Total</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-[#52525b] uppercase tracking-wide">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-[#52525b] uppercase tracking-wide">Frequency</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-[#52525b] uppercase tracking-wide">Due date</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-[#52525b] uppercase tracking-wide">Created</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#27272a]">
                @forelse ($invoices as $inv)
                    <tr class="bg-[#09090b] hover:bg-[#18181b] transition-colors duration-100
                        {{ in_array($inv->status, ['void', 'uncollectible']) ? 'opacity-60' : '' }}">

                        {{-- Invoice number --}}
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-[#a1a1aa]">{{ $inv->invoiceNumber }}</span>
                        </td>

                        {{-- Customer --}}
                        <td class="px-4 py-3">
                            <p class="text-white font-medium leading-none">{{ $inv->customerName }}</p>
                            @if ($inv->customerEmail)
                                <p class="text-[#52525b] text-xs mt-1 leading-none">{{ $inv->customerEmail }}</p>
                            @endif
                        </td>

                        {{-- Total --}}
                        <td class="px-4 py-3 text-right">
                            <span class="text-white font-medium tabular-nums">{{ $inv->formattedTotal }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $inv->statusBadgeClass }}">
                                {{ $inv->statusLabel }}
                            </span>
                        </td>

                        {{-- Frequency --}}
                        <td class="px-4 py-3 text-[#a1a1aa]">{{ $inv->frequency }}</td>

                        {{-- Due date --}}
                        <td class="px-4 py-3 text-[#71717a] text-xs tabular-nums">{{ $inv->dueDate }}</td>

                        {{-- Created --}}
                        <td class="px-4 py-3 text-[#71717a] text-xs tabular-nums">{{ $inv->date }}</td>

                        {{-- Actions --}}
                        <td class="px-4 py-3">
                            <div
                                class="relative"
                                x-data="{ open: false }"
                                @click.outside="open = false"
                            >
                                <button
                                    @click.stop="open = !open"
                                    class="p-1.5 text-[#52525b] hover:text-white hover:bg-[#27272a] rounded transition-colors"
                                    title="Actions"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="5"  r="1.5"/>
                                        <circle cx="12" cy="12" r="1.5"/>
                                        <circle cx="12" cy="19" r="1.5"/>
                                    </svg>
                                </button>

                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute right-0 mt-1 w-44 bg-[#18181b] border border-[#3f3f46] rounded-lg shadow-xl z-20 py-1"
                                    style="display: none"
                                >
                                    @if ($inv->userId)
                                        <button
                                            @click="$dispatch('open-customer-panel', { id: {{ $inv->userId }} }); open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-[#a1a1aa] hover:text-white hover:bg-[#27272a] transition-colors flex items-center gap-2"
                                        >
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Ver cliente
                                        </button>
                                    @endif

                                    @if ($inv->stripeSubscriptionId)
                                        <button
                                            @click="$dispatch('open-subscription-panel', { stripeId: '{{ $inv->stripeSubscriptionId }}' }); open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-[#a1a1aa] hover:text-white hover:bg-[#27272a] transition-colors flex items-center gap-2"
                                        >
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Ver suscripción
                                        </button>
                                    @endif

                                    @if (! $inv->userId && ! $inv->stripeSubscriptionId)
                                        <p class="px-4 py-2 text-xs text-[#52525b]">No hay acciones disponibles.</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-[#52525b] text-sm">
                            No invoices found. Use "Import from Stripe" to load existing records.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Panels reutilizados de otras secciones --}}
    @livewire('admin.customer-detail-panel')
    @livewire('admin.subscription-detail-panel')
</div>
