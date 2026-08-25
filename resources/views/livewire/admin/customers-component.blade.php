<div>
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

    {{-- Table --}}
    <div class="bg-[#18181b] rounded-xl border border-[#27272a] overflow-hidden overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead>
                <tr class="border-b border-[#27272a] bg-[#09090b]">
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Name</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Email</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Description</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Country</th>
                    <th class="text-left px-5 py-3 font-medium text-[#71717a]">Created</th>
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
                        <td class="px-5 py-4 text-[#71717a] max-w-xs truncate">{{ $customer->description }}</td>
                        <td class="px-5 py-4 text-[#a1a1aa] uppercase text-xs font-medium tracking-wide">{{ $customer->country }}</td>
                        <td class="px-5 py-4 text-[#71717a]">{{ $customer->createdAt }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-[#52525b]">
                            No customers yet. Use "Import from Stripe" to load existing records.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
