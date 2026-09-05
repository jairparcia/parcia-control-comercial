<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-white">Transactions</h1>
            <p class="text-sm text-[#71717a] mt-0.5">Charges processed through Stripe.</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="flex items-center gap-2 mb-4">
        <span class="text-sm text-[#71717a]">Filter by</span>
        <select
            wire:model.live="statusFilter"
            class="bg-[#27272a] border border-[#3f3f46] text-sm text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#52525b] cursor-pointer"
        >
            <option value="all">All statuses</option>
            <option value="succeeded">Successful</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
            <option value="partially_refunded">Partially refunded</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-[#18181b] rounded-xl border border-[#27272a] overflow-hidden overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#09090b]">
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Customer</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">Amount</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Status</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Payment method</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Description</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Date</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#27272a]">
                @forelse ($transactions as $tx)
                    <tr
                        wire:key="tx-{{ $tx->stripeId }}"
                        class="hover:bg-[#27272a]/40 transition-colors cursor-pointer {{ in_array($tx->status, ['failed', 'refunded']) ? 'opacity-60' : '' }}"
                        x-data
                        @click="$dispatch('open-transaction-panel', { chargeId: '{{ $tx->stripeId }}' })"
                    >
                        <td class="px-5 py-4">
                            <div class="font-medium text-white">{{ $tx->customerName }}</div>
                            @if ($tx->customerEmail)
                                <div class="text-xs text-[#71717a] mt-0.5">{{ $tx->customerEmail }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="{{ in_array($tx->status, ['refunded', 'partially_refunded']) ? 'line-through text-[#52525b]' : 'text-white font-medium' }}">
                                {{ $tx->formattedAmount }}
                            </span>
                            @if ($tx->formattedAmountRefunded)
                                <div class="text-xs text-amber-400 mt-0.5">−{{ $tx->formattedAmountRefunded }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $tx->statusBadgeClass }}">
                                {{ $tx->statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-[#a1a1aa]">{{ $tx->paymentMethod }}</td>
                        <td class="px-5 py-4 text-[#a1a1aa] max-w-[220px] truncate">{{ $tx->description }}</td>
                        <td class="px-5 py-4 text-[#a1a1aa] whitespace-nowrap">{{ $tx->date }}</td>
                        <td class="px-5 py-4">
                            <svg class="w-4 h-4 text-[#52525b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-[#52525b]">
                            No transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @livewire('admin.transaction-detail-panel')
</div>
