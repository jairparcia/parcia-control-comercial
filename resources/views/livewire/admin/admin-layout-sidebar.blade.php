<aside
    class="
        {{ $collapsed ? 'w-[68px]' : 'w-[240px]' }}
        min-h-screen h-screen sticky top-0
        bg-[#18181b] border-r border-[#27272a]
        flex flex-col justify-between
        px-3 pt-4 pb-3
        shrink-0 z-10
        transition-all duration-200 ease-in-out
        overflow-visible
    "
>
    <div class="flex flex-col min-h-0 flex-1">

        {{-- HEADER --}}
        <header class="mb-3">
            <div class="flex items-center justify-between min-h-[36px] gap-2 {{ $collapsed ? 'justify-center' : '' }}">

                <button
                    type="button"
                    wire:click="toggle"
                    class="flex items-center gap-[10px] bg-transparent border-0 p-0 {{ $collapsed ? 'justify-center w-full cursor-ew-resize' : 'cursor-default min-w-0' }}"
                >
                    <div class="w-7 h-7 rounded-lg bg-[#f9f9f9] flex items-center justify-center shrink-0">
                        <svg class="w-[15px] h-[15px] fill-[#18181b]" viewBox="0 0 15 15">
                            <rect x="1" y="1" width="5.5" height="5.5" rx="1"/>
                            <rect x="8.5" y="1" width="5.5" height="5.5" rx="1"/>
                            <rect x="8.5" y="8.5" width="5.5" height="5.5" rx="1"/>
                            <rect x="1" y="8.5" width="5.5" height="5.5" rx="1"/>
                        </svg>
                    </div>

                    @unless($collapsed)
                        <div class="min-w-0">
                            <span class="text-[15px] font-semibold tracking-[-0.02em] text-white leading-none whitespace-nowrap">
                                Parcia
                            </span>
                            <span class="ml-2 text-[10px] font-medium tracking-wide text-[#71717a] uppercase leading-none">
                                Admin
                            </span>
                        </div>
                    @endunless
                </button>

                @unless($collapsed)
                    <button
                        type="button"
                        wire:click="toggle"
                        title="Collapse sidebar"
                        class="w-[28px] h-[28px] rounded-[7px] flex items-center justify-center bg-transparent border-0 shrink-0 cursor-ew-resize text-[#52525b] hover:text-[#a1a1aa] hover:bg-[#27272a] transition-colors duration-150"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="9" y1="3" x2="9" y2="21"/>
                        </svg>
                    </button>
                @endunless
            </div>
        </header>

        <div class="border-t border-[#27272a] mb-3"></div>

        {{-- NAV --}}
        <nav class="flex flex-col gap-[3px]">
            @foreach ($items as $item)
                <a
                    href="{{ $item['route'] }}"
                    wire:navigate
                    aria-label="{{ $item['label'] }}"
                    class="
                        group relative flex items-center h-10 rounded-md
                        text-[12.5px] font-normal
                        transition-colors duration-150 no-underline
                        {{ $collapsed ? 'justify-center px-0 overflow-visible' : 'gap-3 px-3 overflow-hidden' }}
                        {{ $item['isActive']
                            ? 'bg-[#27272a] text-white'
                            : 'text-[#a1a1aa] hover:bg-[#27272a] hover:text-white' }}
                    "
                >
                    <span class="shrink-0 flex items-center justify-center w-[18px] h-[18px]">
                        @if ($item['icon'] === 'plans')
                            <svg class="w-[16px] h-[16px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="5" rx="1"/>
                                <rect x="2" y="10" width="20" height="5" rx="1"/>
                                <rect x="2" y="17" width="20" height="5" rx="1"/>
                            </svg>
                        @elseif ($item['icon'] === 'subscriptions')
                            <svg class="w-[16px] h-[16px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                                <path d="M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                        @elseif ($item['icon'] === 'customers')
                            <svg class="w-[16px] h-[16px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        @elseif ($item['icon'] === 'invoices')
                            <svg class="w-[16px] h-[16px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                        @endif
                    </span>

                    @unless($collapsed)
                        <span class="whitespace-nowrap overflow-hidden text-ellipsis leading-none">
                            {{ $item['label'] }}
                        </span>
                    @endunless

                    @if($collapsed)
                        <span class="pointer-events-none absolute left-[calc(100%+10px)] top-1/2 -translate-y-1/2 z-[999] inline-flex items-center rounded-lg bg-[#27272a] border border-[#3f3f46] px-3 py-2 text-[12px] font-normal leading-none text-white whitespace-nowrap shadow-[0_4px_14px_rgba(0,0,0,0.4)] opacity-0 invisible transition-all duration-150 group-hover:opacity-100 group-hover:visible">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    {{-- BOTTOM --}}
    <div class="shrink-0 flex flex-col gap-0">

        {{-- Back to portal --}}
        <div class="pb-2">
            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                class="group relative flex items-center h-10 rounded-md gap-3 px-3 text-[#52525b] hover:text-[#a1a1aa] hover:bg-[#27272a] transition-colors duration-150 no-underline {{ $collapsed ? 'justify-center px-0' : '' }}"
            >
                <span class="shrink-0 flex items-center justify-center w-[18px] h-[18px]">
                    <svg class="w-[15px] h-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </span>
                @unless($collapsed)
                    <span class="text-[12px] whitespace-nowrap leading-none">Back to portal</span>
                @endunless
                @if($collapsed)
                    <span class="pointer-events-none absolute left-[calc(100%+10px)] top-1/2 -translate-y-1/2 z-[999] inline-flex items-center rounded-lg bg-[#27272a] border border-[#3f3f46] px-3 py-2 text-[12px] font-normal leading-none text-white whitespace-nowrap shadow-[0_4px_14px_rgba(0,0,0,0.4)] opacity-0 invisible transition-all duration-150 group-hover:opacity-100 group-hover:visible">
                        Back to portal
                    </span>
                @endif
            </a>
        </div>

        <div class="border-t border-[#27272a] pt-3">
            <div class="flex items-center {{ $collapsed ? 'justify-center' : 'gap-[10px]' }} min-w-0">
                <div class="w-[34px] h-[34px] rounded-[8px] bg-[#27272a] text-[#a1a1aa] flex items-center justify-center text-[12px] font-semibold shrink-0">
                    {{ $userInitials }}
                </div>

                @unless($collapsed)
                    <div class="min-w-0 flex-1">
                        <p class="text-[12px] font-medium leading-[1.3] text-white whitespace-nowrap overflow-hidden text-ellipsis">
                            {{ $userName }}
                        </p>
                        <p class="text-[11px] leading-[1.3] text-[#52525b] mt-[2px] whitespace-nowrap overflow-hidden text-ellipsis">
                            {{ $userEmail }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button
                            type="submit"
                            title="Log out"
                            class="w-[26px] h-[26px] rounded-[6px] flex items-center justify-center text-[#52525b] hover:text-[#a1a1aa] hover:bg-[#27272a] transition-colors duration-150"
                        >
                            <svg class="w-[14px] h-[14px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                        </button>
                    </form>
                @endunless
            </div>
        </div>
    </div>
</aside>
