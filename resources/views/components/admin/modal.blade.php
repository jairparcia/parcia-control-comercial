@props([
    'title',
    'size'    => 'max-w-lg',
    'onClose' => "\$set('showModal', false)",
])

<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black/70" wire:click="{{ $onClose }}"></div>
    <div class="flex min-h-full items-center justify-center px-4 py-8">
        <div class="relative w-full {{ $size }} bg-[#18181b] border border-[#27272a] rounded-2xl shadow-2xl p-6 z-10">

            <h2 class="text-lg font-semibold text-white mb-5">{{ $title }}</h2>

            {{ $slot }}

            @if ($footer->isNotEmpty())
                <div class="flex justify-end gap-3 mt-6">
                    {{ $footer }}
                </div>
            @endif

        </div>
    </div>
</div>
