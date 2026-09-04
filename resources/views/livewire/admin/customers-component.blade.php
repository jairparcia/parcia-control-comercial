<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-white">{{ __('admin.customers') }}</h1>
            <p class="text-sm text-[#71717a] mt-0.5">{{ __('admin.customers_subtitle') }}</p>
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
            <option value="inactive">{{ __('common.status_inactive') }}</option>
            <option value="archived">{{ __('common.status_archived') }}</option>
            <option value="all">{{ __('common.all_statuses') }}</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-[#18181b] rounded-xl border border-[#27272a] overflow-hidden overflow-x-auto">
        <table class="w-full text-sm min-w-[900px]">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#09090b]">
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.name') }}</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.email') }}</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.status') }}</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.description') }}</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.country') }}</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">{{ __('common.created') }}</th>
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
                                @click="$dispatch('open-customer-panel', { id: {{ $customer->id }} })"
                                title="{{ __('common.more_details') }}"
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
                            {{ __('admin.no_customers') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Customer detail panel --}}
    @livewire('admin.customer-detail-panel')
</div>
