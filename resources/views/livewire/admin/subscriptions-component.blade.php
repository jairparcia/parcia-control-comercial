<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-white">{{ __('admin.subscriptions') }}</h1>
            <p class="text-sm text-[#71717a] mt-0.5">{{ __('admin.subscriptions_subtitle') }}</p>
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
            <span wire:loading.remove wire:target="import">{{ __('common.import_stripe') }}</span>
            <span wire:loading wire:target="import">{{ __('common.importing') }}</span>
        </button>
    </div>

    {{-- Filter bar --}}
    <div class="flex items-center gap-2 mb-4">
        <span class="text-sm text-[#71717a]">{{ __('common.filter_by') }}</span>
        <select
            wire:model.live="statusFilter"
            class="bg-[#27272a] border border-[#3f3f46] text-sm text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#52525b] cursor-pointer"
        >
            <option value="active">{{ __('common.status_active') }}</option>
            <option value="trialing">{{ __('common.status_trialing') }}</option>
            <option value="past_due">{{ __('common.status_past_due') }}</option>
            <option value="canceled">{{ __('common.status_canceled') }}</option>
            <option value="all">{{ __('common.all_statuses') }}</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-[#18181b] rounded-xl border border-[#27272a] overflow-hidden overflow-x-auto">
        <table class="w-full text-sm min-w-[960px]">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#09090b]">
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.customer') }}</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.status') }}</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Plan</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.payment_method') }}</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">{{ __('admin.monthly_avg') }}</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">{{ __('admin.annual_avg') }}</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.since') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#27272a]">
                @forelse ($subscriptions as $sub)
                    <tr
                        wire:key="sub-{{ $sub->id }}"
                        class="hover:bg-[#27272a]/40 transition-colors cursor-pointer {{ $sub->canceledAt ? 'opacity-50' : '' }}"
                        x-data
                        @click="$dispatch('open-subscription-panel', { stripeId: '{{ $sub->stripeId }}' })"
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
                                <div class="text-xs text-red-500/70 mt-0.5">{{ __('admin.canceled_date') }} {{ $sub->canceledAt }}</div>
                            @elseif ($sub->endsAt)
                                <div class="text-xs text-amber-500/70 mt-0.5">{{ __('admin.cancels_on') }} {{ $sub->endsAt }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <svg class="w-4 h-4 text-[#52525b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-[#52525b]">
                            {{ __('admin.no_subscriptions') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @livewire('admin.subscription-detail-panel')
</div>
