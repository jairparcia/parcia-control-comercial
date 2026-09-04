<aside
    class="
        {{ $collapsed ? 'w-[80px]' : 'w-[240px]' }}
        min-h-screen h-screen sticky top-0
        bg-[#f9f9f9] border-r border-[#eaeaea]
        flex flex-col justify-between
        px-4 pt-4 pb-3
        shrink-0 z-10
        transition-all duration-200 ease-in-out
        overflow-visible
    "
>
    <div class="flex flex-col min-h-0 flex-1">

        {{-- HEADER --}}
        <header class="mb-2">
            <div class="flex items-center justify-between min-h-[36px] gap-2 {{ $collapsed ? 'justify-center' : '' }}">

                <button
                    type="button"
                    wire:click="toggle"
                    class="flex items-center gap-[11px] bg-transparent border-0 p-0 {{ $collapsed ? 'justify-center w-full cursor-ew-resize' : 'cursor-default min-w-0' }}"
                >
                    {{-- Logo mark --}}
                    <div class="w-7 h-7 rounded-lg bg-[#353636] flex items-center justify-center shrink-0">
                        <svg class="w-[15px] h-[15px] fill-[#f9f9f9]" viewBox="0 0 15 15">
                            <rect x="1" y="1" width="5.5" height="5.5" rx="1"/>
                            <rect x="8.5" y="1" width="5.5" height="5.5" rx="1"/>
                            <rect x="8.5" y="8.5" width="5.5" height="5.5" rx="1"/>
                            <rect x="1" y="8.5" width="5.5" height="5.5" rx="1"/>
                        </svg>
                    </div>

                    @unless($collapsed)
                        <span class="text-[22px] font-bold tracking-[-0.03em] text-[#353636] leading-none whitespace-nowrap">
                            Parcia
                        </span>
                    @endunless
                </button>

                @unless($collapsed)
                    <div class="flex items-center gap-2 shrink-0">
                        <x-language-switcher />
                        <button
                            type="button"
                            wire:click="toggle"
                            title="{{ __('common.collapse_sidebar') }}"
                            class="w-[30px] h-[30px] rounded-[7px] flex items-center justify-center bg-transparent border-0 shrink-0 cursor-ew-resize text-[#7c7c86] hover:bg-[#eaeaea] transition-colors duration-150"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <line x1="9" y1="3" x2="9" y2="21"/>
                            </svg>
                        </button>
                    </div>
                @endunless
            </div>
        </header>

        {{-- DIVIDER --}}
        <div class="border-t border-[#eaeaea] mb-3"></div>

        {{-- NAV --}}
        <nav class="flex flex-col gap-[3px]">
            @foreach ($items as $item)
                <a
                    href="{{ $item['route'] }}"
                    aria-label="{{ $item['label'] }}"
                    wire:navigate
                    class="
                        group relative flex items-center h-11 rounded-md
                        text-[#353636] text-[12.5px] font-normal
                        transition-colors duration-150 no-underline
                        {{ $collapsed ? 'justify-center px-0 overflow-visible' : 'gap-3 px-3 overflow-hidden' }}
                        {{ $item['isActive'] ? 'bg-[#efefef]' : 'bg-transparent hover:bg-[#f3f3f3]' }}
                    "
                >
                    {{-- Icon --}}
                    <span class="shrink-0 flex items-center justify-center w-[20px] h-[20px] transition-transform duration-200 group-hover:-translate-y-[2px]">
                        @if ($item['icon'] === 'home')
                            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                <polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        @elseif ($item['icon'] === 'creators')
                            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                        @elseif ($item['icon'] === 'billing')
                            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                        @elseif ($item['icon'] === 'settings')
                            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/>
                            </svg>
                        @elseif ($item['icon'] === 'admin')
                            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        @endif
                    </span>

                    {{-- Label --}}
                    @unless($collapsed)
                        <span class="text-[12.5px] font-normal leading-none text-[#353636] whitespace-nowrap overflow-hidden text-ellipsis">
                            {{ $item['label'] }}
                        </span>
                    @endunless

                    {{-- Tooltip when collapsed --}}
                    @if($collapsed)
                        <span class="
                            pointer-events-none absolute left-[calc(100%+10px)] top-1/2 -translate-y-1/2 z-[999]
                            inline-flex items-center rounded-lg bg-[#2c2c2c] px-3 py-2
                            text-[12px] font-normal leading-none text-white whitespace-nowrap
                            shadow-[0_4px_14px_rgba(0,0,0,0.25)]
                            opacity-0 invisible transition-all duration-150
                            group-hover:opacity-100 group-hover:visible
                        ">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>
    </div>

    {{-- BOTTOM: Admin + Usuario --}}
    <div class="shrink-0 flex flex-col gap-0">

        {{-- Admin link (internal only) --}}
        @if($adminRoute)
            <div class="pb-2">
                <a
                    href="{{ $adminRoute }}"
                    wire:navigate
                    aria-label="Admin"
                    class="
                        group relative flex items-center h-11 rounded-md
                        text-[#353636] text-[12.5px] font-normal
                        transition-colors duration-150 no-underline
                        {{ $collapsed ? 'justify-center px-0 overflow-visible' : 'gap-3 px-3 overflow-hidden' }}
                        {{ $isAdminActive ? 'bg-[#efefef]' : 'bg-transparent hover:bg-[#f3f3f3]' }}
                    "
                >
                    <span class="shrink-0 flex items-center justify-center w-[20px] h-[20px] transition-transform duration-200 group-hover:-translate-y-[2px]">
                        <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </span>
                    @unless($collapsed)
                        <span class="text-[12.5px] font-normal leading-none text-[#353636] whitespace-nowrap overflow-hidden text-ellipsis">
                            Admin
                        </span>
                    @endunless
                    @if($collapsed)
                        <span class="pointer-events-none absolute left-[calc(100%+10px)] top-1/2 -translate-y-1/2 z-[999] inline-flex items-center rounded-lg bg-[#2c2c2c] px-3 py-2 text-[12px] font-normal leading-none text-white whitespace-nowrap shadow-[0_4px_14px_rgba(0,0,0,0.25)] opacity-0 invisible transition-all duration-150 group-hover:opacity-100 group-hover:visible">
                            Admin
                        </span>
                    @endif
                </a>
            </div>
        @endif

        {{-- FOOTER USUARIO --}}
        <div class="pt-3 border-t border-[#eaeaea]">
            <div class="flex items-center {{ $collapsed ? 'justify-center' : 'gap-[10px]' }} min-w-0">
                <div class="w-[38px] h-[38px] rounded-[10px] bg-[#e9dfcf] text-[#5c4a36] flex items-center justify-center text-[13px] font-semibold shrink-0">
                    {{ $userInitials }}
                </div>

                @unless($collapsed)
                    <div class="min-w-0 flex-1">
                        <p class="text-[12px] font-semibold leading-[1.3] text-[#353636] whitespace-nowrap overflow-hidden text-ellipsis">
                            {{ $userName }}
                        </p>
                        <p class="text-[11px] font-light leading-[1.3] text-[#7c7c86] mt-[2px] whitespace-nowrap overflow-hidden text-ellipsis">
                            {{ $userPlan }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button
                            type="submit"
                            title="{{ __('common.log_out') }}"
                            class="w-[28px] h-[28px] rounded-[7px] flex items-center justify-center text-[#7c7c86] hover:text-[#353636] hover:bg-[#eaeaea] transition-colors duration-150"
                        >
                            <svg class="w-[15px] h-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
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
