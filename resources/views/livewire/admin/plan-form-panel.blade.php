<div x-data="{ open: @entangle('panelOpen') }" x-on:keydown.escape.window="open && $wire.close()">
    <x-slide-over :title="$modalTitle" close-action="$wire.close()">
        <div class="px-6 py-5 space-y-4">
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

            {{-- Stripe prices table (edit mode only) --}}
            @if ($isEditing)
                <div class="pt-2">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-medium text-[#a1a1aa]">{{ __('admin.plan_prices') }}</h3>
                        <div class="flex items-center gap-3">
                            <button
                                wire:click="loadPrices"
                                wire:loading.attr="disabled"
                                wire:target="loadPrices,archivePrice,setDefaultPrice,addPrice"
                                class="text-xs text-[#52525b] hover:text-[#a1a1aa] transition-colors"
                            >
                                <span wire:loading.remove wire:target="loadPrices,archivePrice,setDefaultPrice,addPrice">{{ __('common.refresh') }}</span>
                                <span wire:loading wire:target="loadPrices,archivePrice,setDefaultPrice,addPrice">{{ __('common.loading') }}</span>
                            </button>
                            @if (! $showPriceForm)
                                <button
                                    wire:click="openPriceForm"
                                    class="text-xs text-[#a1a1aa] hover:text-white bg-[#27272a] hover:bg-[#3f3f46] border border-[#3f3f46] rounded-md px-2.5 py-1 transition-colors"
                                >
                                    + {{ __('admin.add_price') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($showPriceForm)
                        <div class="mb-3 p-3 bg-[#18181b] border border-[#3f3f46] rounded-lg">
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <div>
                                    <label class="block text-xs font-medium text-[#71717a] mb-1">{{ __('admin.price') }}</label>
                                    <input
                                        wire:model="newPriceAmount"
                                        type="number"
                                        min="1"
                                        placeholder="500"
                                        class="w-full px-2.5 py-1.5 text-xs rounded-md border border-[#3f3f46] bg-[#09090b] text-white placeholder-[#52525b] focus:outline-none focus:ring-1 focus:ring-[#52525b]"
                                    >
                                    @error('newPriceAmount') <p class="text-[10px] text-red-400 mt-0.5">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-[#71717a] mb-1">{{ __('admin.currency') }}</label>
                                    <select
                                        wire:model="newPriceCurrency"
                                        class="w-full px-2.5 py-1.5 text-xs rounded-md border border-[#3f3f46] bg-[#09090b] text-white focus:outline-none focus:ring-1 focus:ring-[#52525b]"
                                    >
                                        <option value="MXN">MXN</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-[#71717a] mb-1">{{ __('admin.interval') }}</label>
                                    <select
                                        wire:model="newPriceInterval"
                                        class="w-full px-2.5 py-1.5 text-xs rounded-md border border-[#3f3f46] bg-[#09090b] text-white focus:outline-none focus:ring-1 focus:ring-[#52525b]"
                                    >
                                        <option value="month">{{ __('common.interval_monthly') }}</option>
                                        <option value="year">{{ __('common.interval_annual') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    wire:click="cancelPriceForm"
                                    class="text-xs text-[#71717a] hover:text-[#a1a1aa] transition-colors px-2 py-1"
                                >
                                    {{ __('common.cancel') }}
                                </button>
                                <button
                                    wire:click="addPrice"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-70 cursor-not-allowed"
                                    wire:target="addPrice"
                                    class="text-xs text-white bg-[#3f3f46] hover:bg-[#52525b] rounded-md px-3 py-1.5 transition-colors"
                                >
                                    <span wire:loading.remove wire:target="addPrice">{{ __('common.save_changes') }}</span>
                                    <span wire:loading wire:target="addPrice">{{ __('common.loading') }}</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if (empty($prices))
                        <p class="text-xs text-[#52525b] py-3 text-center bg-[#18181b] rounded-lg border border-[#27272a]">
                            {{ __('admin.no_plan_prices') }}
                        </p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-[#27272a]">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="border-b border-[#27272a] bg-[#18181b]">
                                        <th class="text-left px-3 py-2 font-medium text-[#71717a]">{{ __('admin.price') }}</th>
                                        <th class="text-left px-3 py-2 font-medium text-[#71717a]">{{ __('admin.frequency') }}</th>
                                        <th class="text-left px-3 py-2 font-medium text-[#71717a]">{{ __('common.status') }}</th>
                                        <th class="text-right px-3 py-2 font-medium text-[#71717a]">{{ __('admin.subs') }}</th>
                                        <th class="text-left px-3 py-2 font-medium text-[#71717a]">{{ __('common.created') }}</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#27272a]">
                                    @foreach ($prices as $price)
                                        <tr class="bg-[#09090b] hover:bg-[#18181b] transition-colors">
                                            <td class="px-3 py-2.5 font-medium text-white">{{ $price['amount'] }}</td>
                                            <td class="px-3 py-2.5 text-[#a1a1aa]">{{ $price['interval'] }}</td>
                                            <td class="px-3 py-2.5">
                                                @if ($price['isDefault'])
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-violet-500/15 text-violet-400 border border-violet-500/20">
                                                        {{ __('admin.price_status_default') }}
                                                    </span>
                                                @elseif (! $price['isActive'])
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#27272a] text-[#71717a] border border-[#3f3f46]">
                                                        {{ __('common.status_archived') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 text-right text-[#a1a1aa]">{{ $price['subscriptions'] }}</td>
                                            <td class="px-3 py-2.5 text-[#52525b]">{{ $price['createdAt'] }}</td>
                                            <td class="px-3 py-2.5">
                                                @if (! $price['isDefault'] && $price['isActive'])
                                                    <div x-data="{ open: false, top: 0, right: 0 }" class="flex justify-end">
                                                        <button
                                                            @click.stop="
                                                                const r = $el.getBoundingClientRect();
                                                                top = r.bottom + 4;
                                                                right = window.innerWidth - r.right;
                                                                open = !open;
                                                            "
                                                            @keydown.escape.window="open = false"
                                                            class="p-1 rounded text-[#52525b] hover:text-[#a1a1aa] hover:bg-[#27272a] transition-colors"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                            </svg>
                                                        </button>
                                                        <template x-teleport="body">
                                                            <div
                                                                x-show="open"
                                                                x-transition:enter="transition ease-out duration-100"
                                                                x-transition:enter-start="opacity-0 scale-95"
                                                                x-transition:enter-end="opacity-100 scale-100"
                                                                x-transition:leave="transition ease-in duration-75"
                                                                x-transition:leave-start="opacity-100 scale-100"
                                                                x-transition:leave-end="opacity-0 scale-95"
                                                                @click.outside="open = false"
                                                                :style="`position:fixed;top:${top}px;right:${right}px;z-index:9999`"
                                                                class="bg-[#18181b] border border-[#3f3f46] rounded-lg shadow-lg overflow-hidden"
                                                                style="display:none"
                                                            >
                                                                <button
                                                                    wire:click="setDefaultPrice('{{ $price['stripeId'] }}')"
                                                                    wire:loading.attr="disabled"
                                                                    @click="open = false"
                                                                    class="block whitespace-nowrap text-left px-3 py-2 text-xs text-[#a1a1aa] hover:bg-[#27272a] hover:text-white transition-colors"
                                                                >
                                                                    {{ __('admin.set_as_default') }}
                                                                </button>
                                                                <button
                                                                    wire:click="archivePrice('{{ $price['stripeId'] }}')"
                                                                    wire:loading.attr="disabled"
                                                                    wire:confirm="{{ __('admin.archive_price_confirm') }}"
                                                                    @click="open = false"
                                                                    class="block whitespace-nowrap text-left px-3 py-2 text-xs text-red-400 hover:bg-[#27272a] hover:text-red-300 transition-colors"
                                                                >
                                                                    {{ __('admin.archive_price') }}
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <x-slot:footer>
            <div class="px-6 py-4 flex items-center justify-end gap-2">
                <button
                    wire:click="close"
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
            </div>
        </x-slot:footer>
    </x-slide-over>
</div>
