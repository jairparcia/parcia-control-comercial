<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-white">Subscription plans</h1>
            <p class="text-sm text-[#71717a] mt-0.5">Manage plans and sync with Stripe automatically.</p>
        </div>
        <button
            wire:click="openCreate"
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
                                wire:click="openEdit({{ $plan->id }})"
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

    {{-- Modal create / edit --}}
    @if ($showModal)
        <x-admin.modal :title="$modalTitle">
            <div class="space-y-4">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-[#a1a1aa] mb-1">Name</label>
                    <input
                        wire:model="formName"
                        type="text"
                        placeholder="Pro"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white placeholder-[#52525b] focus:outline-none focus:ring-2 focus:ring-[#52525b]"
                    >
                    @error('formName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Key (read-only on edit) --}}
                <div>
                    <label class="block text-sm font-medium text-[#a1a1aa] mb-1">
                        Key
                        @if ($isEditing)
                            <span class="text-[#52525b] font-normal">(not editable)</span>
                        @else
                            <span class="text-[#52525b] font-normal">— unique slug, e.g. <code class="text-[#71717a]">pro</code></span>
                        @endif
                    </label>
                    <input
                        wire:model="formKey"
                        type="text"
                        placeholder="pro"
                        @if ($isEditing) readonly @endif
                        class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white placeholder-[#52525b] focus:outline-none focus:ring-2 focus:ring-[#52525b] {{ $keyFieldClass }}"
                    >
                    @error('formKey') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-[#a1a1aa] mb-1">Description</label>
                    <textarea
                        wire:model="formDescription"
                        rows="2"
                        placeholder="Best for small teams..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white placeholder-[#52525b] focus:outline-none focus:ring-2 focus:ring-[#52525b] resize-none"
                    ></textarea>
                </div>

                {{-- Features (one per line) --}}
                <div>
                    <label class="block text-sm font-medium text-[#a1a1aa] mb-1">
                        Features
                        <span class="text-[#52525b] font-normal">— one per line</span>
                    </label>
                    <textarea
                        wire:model="formFeatures"
                        rows="4"
                        placeholder="TikTok & Instagram&#10;CSV export&#10;Priority support"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white placeholder-[#52525b] focus:outline-none focus:ring-2 focus:ring-[#52525b] resize-none font-mono"
                    ></textarea>
                    <p class="text-xs text-[#52525b] mt-1">Shown on the onboarding and billing pages.</p>
                </div>

                {{-- Monthly quota --}}
                <div>
                    <label class="block text-sm font-medium text-[#a1a1aa] mb-1">Monthly quota (scans)</label>
                    <input
                        wire:model="formQuota"
                        type="number"
                        min="0"
                        placeholder="500"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white placeholder-[#52525b] focus:outline-none focus:ring-2 focus:ring-[#52525b]"
                    >
                    @error('formQuota') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Price, currency, interval --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-[#a1a1aa] mb-1">Price</label>
                        <input
                            wire:model="formUnitAmount"
                            type="number"
                            min="0"
                            placeholder="500"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white placeholder-[#52525b] focus:outline-none focus:ring-2 focus:ring-[#52525b]"
                        >
                        @error('formUnitAmount') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#a1a1aa] mb-1">Currency</label>
                        <select
                            wire:model="formCurrency"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white focus:outline-none focus:ring-2 focus:ring-[#52525b]"
                        >
                            <option value="MXN">MXN</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#a1a1aa] mb-1">Interval</label>
                        <select
                            wire:model="formInterval"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white focus:outline-none focus:ring-2 focus:ring-[#52525b]"
                        >
                            <option value="month">Monthly</option>
                            <option value="year">Yearly</option>
                        </select>
                    </div>
                </div>

                {{-- Display order --}}
                <div>
                    <label class="block text-sm font-medium text-[#a1a1aa] mb-1">Display order</label>
                    <input
                        wire:model="formSortOrder"
                        type="number"
                        min="0"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-[#3f3f46] bg-[#09090b] text-white placeholder-[#52525b] focus:outline-none focus:ring-2 focus:ring-[#52525b]"
                    >
                </div>

                @if (! $isEditing && $formUnitAmount > 0)
                    <p class="text-xs text-amber-400 bg-amber-900/20 border border-amber-900/30 rounded-lg px-3 py-2">
                        A product and price will be created in Stripe automatically on save.
                    </p>
                @elseif ($isEditing)
                    <p class="text-xs text-[#71717a] bg-[#27272a] rounded-lg px-3 py-2">
                        If you change the price, a new Stripe price will be created and the previous one archived.
                    </p>
                @endif
            </div>

            <x-slot:footer>
                <button
                    wire:click="$set('showModal', false)"
                    class="px-4 py-2 text-sm font-medium text-[#a1a1aa] hover:text-white hover:bg-[#27272a] rounded-lg transition-colors"
                >
                    Cancel
                </button>
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-70 cursor-not-allowed"
                    class="px-4 py-2 text-sm font-medium text-white bg-[#3f3f46] hover:bg-[#52525b] rounded-lg transition-colors"
                >
                    <span wire:loading.remove wire:target="save">Save</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </x-slot:footer>
        </x-admin.modal>
    @endif
</div>
