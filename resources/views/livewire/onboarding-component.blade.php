<div class="min-h-screen flex flex-col">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <header class="flex items-center justify-center pt-10 pb-2">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-[#5b69e2] flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-[17px] font-bold tracking-[-0.02em] text-[#1a1f36]">Parcia Plugin</span>
        </div>
    </header>

    {{-- ── Hero ─────────────────────────────────────────────────────────── --}}
    <section class="text-center px-6 pt-10 pb-8 max-w-2xl mx-auto">
        <h1 class="text-[32px] font-bold tracking-[-0.03em] text-[#1a1f36] leading-tight">
            {{ __('portal.welcome', ['name' => $userName]) }}
        </h1>
        <p class="mt-4 text-[15px] text-[#7c7c86] leading-relaxed">
            {{ __('portal.plugin_intro') }}
            <strong class="text-[#353636] font-medium">{{ __('portal.plugin_intro_bold') }}</strong>
            {{ __('portal.plugin_intro_end') }}
        </p>
        <p class="mt-2 text-[13.5px] text-[#9ca3af]">
            {{ __('portal.choose_plan_onboard') }}
        </p>
    </section>

    {{-- ── Plans ───────────────────────────────────────────────────────── --}}
    <section class="flex-1 px-6 pb-14 max-w-5xl mx-auto w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ $planCount }} gap-4">

            @foreach ($plans as $plan)
                <div class="relative bg-white rounded-2xl border flex flex-col gap-5 p-6 {{ $plan->cardBorderClass }}">

                    @if ($plan->isPro)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#5b69e2] text-white text-[10.5px] font-semibold px-3 py-1 rounded-full whitespace-nowrap">
                            {{ __('portal.most_popular') }}
                        </span>
                    @endif

                    {{-- Name and price --}}
                    <div>
                        <p class="text-[12.5px] font-semibold uppercase tracking-widest text-[#9ca3af]">
                            {{ $plan->name }}
                        </p>
                        <div class="flex items-baseline gap-1 mt-1.5">
                            <span class="text-[34px] font-bold tracking-[-0.04em] text-[#1a1f36]">
                                {{ $plan->formattedPrice }}
                            </span>
                            <span class="text-[13px] text-[#9ca3af]">/{{ $plan->formattedIntervalLabel }}</span>
                        </div>
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-2.5 flex-1">
                        @foreach ($plan->features as $feature)
                            <li class="flex items-start gap-2 text-[13px] text-[#4b5563]">
                                <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $plan->featureCheckClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    {{-- CTA --}}
                    @if ($plan->isFree)
                        <button
                            wire:click="startFree"
                            wire:loading.attr="disabled"
                            class="w-full py-3 rounded-xl text-[13.5px] font-semibold border border-[#e2e4ea] text-[#7c7c86] hover:bg-[#f3f4f6] transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="startFree">{{ __('portal.start_free') }}</span>
                            <span wire:loading wire:target="startFree">{{ __('portal.loading') }}</span>
                        </button>
                    @else
                        <button
                            wire:click="choosePlan('{{ $plan->key }}')"
                            wire:loading.attr="disabled"
                            class="w-full py-3 rounded-xl text-[13.5px] font-semibold transition-colors disabled:opacity-50 {{ $plan->buttonClass }}">
                            <span wire:loading.remove wire:target="choosePlan">{{ __('portal.choose_name', ['name' => $plan->name]) }}</span>
                            <span wire:loading wire:target="choosePlan">{{ __('portal.redirecting') }}</span>
                        </button>
                    @endif
                </div>
            @endforeach

        </div>

        <p class="text-center text-[12px] text-[#9ca3af] mt-8">
            {{ __('portal.portal_notice') }}
        </p>
    </section>

</div>
