<div class="relative" @click.away="$wire.close()">

    {{-- Bell button --}}
    <button
        type="button"
        wire:click="toggle"
        aria-label="{{ __('notifications.notifications') }}"
        class="
            group relative flex items-center h-11 w-full rounded-md
            text-[#353636] transition-colors duration-150
            {{ $open ? 'bg-[#efefef]' : 'bg-transparent hover:bg-[#f3f3f3]' }}
            {{ $collapsed ? 'justify-center px-0 overflow-visible' : 'gap-3 px-3 overflow-hidden' }}
        "
    >
        {{-- Icon + badge --}}
        <span class="relative shrink-0 flex items-center justify-center w-[20px] h-[20px] transition-transform duration-200 group-hover:-translate-y-[2px]">
            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 01-3.46 0"/>
            </svg>
            @if($unreadCount > 0)
                <span class="absolute -top-[5px] -right-[5px] min-w-[15px] h-[15px] px-[3px] bg-[#e5484d] text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </span>

        @unless($collapsed)
            <span class="text-[12.5px] font-normal leading-tight text-[#353636] whitespace-nowrap overflow-hidden text-ellipsis">
                {{ __('notifications.notifications') }}
            </span>
        @endunless

        {{-- Tooltip in collapsed mode --}}
        @if($collapsed)
            <span class="
                pointer-events-none absolute left-[calc(100%+10px)] top-1/2 -translate-y-1/2 z-[999]
                inline-flex items-center rounded-lg bg-[#2c2c2c] px-3 py-2
                text-[12px] font-normal leading-none text-white whitespace-nowrap
                shadow-[0_4px_14px_rgba(0,0,0,0.25)]
                opacity-0 invisible transition-all duration-150
                group-hover:opacity-100 group-hover:visible
            ">
                {{ __('notifications.notifications') }}
            </span>
        @endif
    </button>

    {{-- Dropdown panel --}}
    @if($open)
        <div
            wire:click.stop
            class="absolute left-full top-0 ml-3 w-[340px] bg-white rounded-xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] border border-[#eaeaea] overflow-hidden z-50"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-[#eaeaea]">
                <span class="text-[13px] font-semibold text-[#353636]">
                    {{ __('notifications.notifications') }}
                </span>
                @if($unreadCount > 0)
                    <button
                        type="button"
                        wire:click="markAllAsRead"
                        class="text-[11.5px] text-[#7c7c86] hover:text-[#353636] transition-colors duration-150"
                    >
                        {{ __('notifications.mark_all_read') }}
                    </button>
                @endif
            </div>

            {{-- List --}}
            <div class="max-h-[400px] overflow-y-auto divide-y divide-[#f3f3f3]">
                @forelse($notifications as $notification)
                    <div
                        wire:key="notif-{{ $notification->id }}"
                        class="
                            flex items-start gap-3 px-4 py-3 transition-colors duration-150 cursor-pointer
                            {{ $notification->isRead ? 'bg-white hover:bg-[#fafafa]' : 'bg-[#fafaf5] hover:bg-[#f5f5ee]' }}
                        "
                        wire:click="markAsRead({{ $notification->id }})"
                    >
                        {{-- Unread dot --}}
                        <span class="mt-[5px] shrink-0 w-[7px] h-[7px] rounded-full {{ $notification->isRead ? 'bg-transparent' : 'bg-[#353636]' }}"></span>

                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] font-semibold text-[#353636] leading-snug">
                                {{ $notification->title }}
                            </p>
                            <p class="text-[12px] font-normal text-[#7c7c86] leading-snug mt-[3px]">
                                {{ $notification->body }}
                            </p>
                            <p class="text-[11px] text-[#b0b0b8] mt-[5px]">
                                {{ $notification->createdAt }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                        <svg class="w-8 h-8 text-[#d0d0d8] mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 01-3.46 0"/>
                        </svg>
                        <p class="text-[12.5px] font-medium text-[#7c7c86]">{{ __('notifications.no_notifications') }}</p>
                        <p class="text-[12px] text-[#b0b0b8] mt-1">{{ __('notifications.no_notifications_desc') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

</div>
