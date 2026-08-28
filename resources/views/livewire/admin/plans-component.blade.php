<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-white">Subscription plans</h1>
            <p class="text-sm text-[#71717a] mt-0.5">Manage plans and sync with Stripe automatically.</p>
        </div>
        <button
            @click="$dispatch('open-plan-form', { id: null })"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-[#3f3f46] hover:bg-[#52525b] rounded-lg transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New plan
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-[#18181b] rounded-xl border border-[#27272a] overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#09090b]">
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Plan</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Key</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">Price</th>
                    <th class="text-right px-5 py-3 font-medium text-[#71717a]">Quota</th>
                    <th class="text-center px-5 py-3 font-medium text-[#71717a]">Stripe</th>
                    <th class="text-center px-5 py-3 font-medium text-[#71717a]">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#27272a]">
                @forelse ($plans as $plan)
                    <tr wire:key="plan-{{ $plan->id }}" class="hover:bg-[#27272a]/40 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-medium text-white">{{ $plan->name }}</div>
                            @if ($plan->description)
                                <div class="text-xs text-[#71717a] mt-0.5 line-clamp-1">{{ $plan->description }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <code class="text-xs bg-[#27272a] text-[#a1a1aa] px-2 py-0.5 rounded">{{ $plan->key }}</code>
                        </td>
                        <td class="px-5 py-4 text-right text-[#a1a1aa]">
                            @if ($plan->isFree)
                                <span class="text-[#52525b]">Free</span>
                            @else
                                {{ $plan->formattedPrice }}
                                <span class="text-xs text-[#52525b]">/{{ $plan->formattedInterval }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right text-[#a1a1aa]">
                            {{ $plan->formattedQuota }}
                            <span class="text-xs text-[#52525b]">scans</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if ($plan->stripePriceId)
                                <span title="{{ $plan->stripePriceId }}" class="inline-flex items-center gap-1 text-xs text-emerald-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Linked
                                </span>
                            @else
                                <span class="text-xs text-[#52525b]">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <button
                                wire:click="toggle({{ $plan->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full transition-colors {{ $plan->statusButtonClass }}"
                            >
                                <span class="w-1.5 h-1.5 rounded-full {{ $plan->statusDotClass }}"></span>
                                {{ $plan->statusLabel }}
                            </button>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button
                                @click="$dispatch('open-plan-form', { id: {{ $plan->id }} })"
                                class="text-sm text-[#a1a1aa] hover:text-white font-medium transition-colors"
                            >
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-[#52525b]">
                            No plans yet. Create the first one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Plan form panel --}}
    @livewire('admin.plan-form-panel')
</div>
