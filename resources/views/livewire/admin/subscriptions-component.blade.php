<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
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

    {{-- Table --}}
    <div class="bg-[#18181b] rounded-xl border border-[#27272a] overflow-hidden overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#09090b]">
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Customer</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Status</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Plan</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Payment method</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">Monthly avg.</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">Annual avg.</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Since</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#27272a]">
                @forelse ($subscriptions as $sub)
                    <tr wire:key="sub-{{ $sub->id }}" class="hover:bg-[#27272a]/40 transition-colors">
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
                        <td class="px-5 py-4 text-[#a1a1aa]">
                            {{ $sub->planName }}
                        </td>
                        <td class="px-5 py-4 text-[#a1a1aa]">
                            {{ $sub->paymentMethod }}
                        </td>
                        <td class="px-5 py-4 text-right text-[#a1a1aa]">
                            {{ $sub->formattedMonthlyAverage }}
                        </td>
                        <td class="px-5 py-4 text-right text-[#a1a1aa]">
                            {{ $sub->formattedAnnualAverage }}
                        </td>
                        <td class="px-5 py-4 text-[#a1a1aa]">
                            {{ $sub->subscribedAt }}
                            @if ($sub->endsAt)
                                <div class="text-xs text-[#52525b] mt-0.5">Ends {{ $sub->endsAt }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-[#52525b]">
                            No subscriptions yet. Use "Import from Stripe" to load existing records.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
