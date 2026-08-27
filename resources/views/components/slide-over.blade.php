@props([
    'title'       => '',
    'maxWidth'    => 'max-w-xl',
    'closeAction' => 'open = false',
])

<template x-teleport="body">
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex justify-end"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            x-show="open"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="{{ $closeAction }}"
        ></div>

        {{-- Panel --}}
        <div
            class="relative z-10 w-full {{ $maxWidth }} h-full bg-[#18181b] border-l border-[#27272a] shadow-2xl flex flex-col"
            x-show="open"
            x-transition:enter="transition-transform ease-in-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition-transform ease-in-out duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-[#27272a] shrink-0">
                <div class="flex items-center gap-3">
                    <h2 class="text-base font-semibold text-white">{{ $title }}</h2>
                    @isset($badge) {{ $badge }} @endisset
                </div>
                <div class="flex items-center gap-2">
                    @isset($actions) {{ $actions }} @endisset
                    <button
                        @click="{{ $closeAction }}"
                        class="p-1.5 text-[#71717a] hover:text-white hover:bg-[#27272a] rounded transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto divide-y divide-[#27272a]">
                {{ $slot }}
            </div>
        </div>
    </div>
</template>
