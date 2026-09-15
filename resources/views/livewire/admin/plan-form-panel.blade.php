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
