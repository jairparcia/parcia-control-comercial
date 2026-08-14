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
            Bienvenido, {{ auth()->user()->name }}
        </h1>
        <p class="mt-4 text-[15px] text-[#7c7c86] leading-relaxed">
            Parcia Plugin te permite <strong class="text-[#353636] font-medium">escanear y guardar perfiles de creadores</strong>
            de TikTok e Instagram directamente desde tu navegador. Toda la información queda
            en tu portal personal, lista para buscar, filtrar y exportar cuando la necesites.
        </p>
        <p class="mt-2 text-[13.5px] text-[#9ca3af]">
            Elige el plan con el que quieres comenzar. Puedes cambiarlo cuando quieras.
        </p>
    </section>

    {{-- ── Planes ───────────────────────────────────────────────────────── --}}
    <section class="flex-1 px-6 pb-14 max-w-5xl mx-auto w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ count($plans) }} gap-4">

            @foreach ($plans as $index => $plan)
                @php
                    $isFree = $plan->key === 'free';
                    $isPro  = $plan->key === 'pro';
                    $features = match ($plan->key) {
                        'free'    => ['25 escaneos al mes', 'TikTok e Instagram', 'Portal web incluido'],
                        'starter' => ['500 escaneos al mes', 'TikTok e Instagram', 'Exportación CSV'],
                        'pro'     => ['2,000 escaneos al mes', 'Todo lo de Starter', 'Webhooks configurables'],
                        'agency'  => ['10,000 escaneos al mes', 'Todo lo de Pro', 'Soporte prioritario'],
                        default   => [],
                    };
                @endphp

                <div class="relative bg-white rounded-2xl border flex flex-col gap-5 p-6
                    {{ $isPro ? 'border-[#5b69e2] ring-1 ring-[#5b69e2]' : 'border-[#e2e4ea]' }}">

                    @if ($isPro)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#5b69e2] text-white text-[10.5px] font-semibold px-3 py-1 rounded-full whitespace-nowrap">
                            Más popular
                        </span>
                    @endif

                    {{-- Nombre y precio --}}
                    <div>
                        <p class="text-[12.5px] font-semibold uppercase tracking-widest text-[#9ca3af]">
                            {{ $plan->name }}
                        </p>
                        <div class="flex items-baseline gap-1 mt-1.5">
                            <span class="text-[34px] font-bold tracking-[-0.04em] text-[#1a1f36]">
                                {{ $plan->formattedPrice }}
                            </span>
                            <span class="text-[13px] text-[#9ca3af]">/{{ $plan->interval === 'year' ? 'año' : 'mes' }}</span>
                        </div>
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-2.5 flex-1">
                        @foreach ($features as $feature)
                            <li class="flex items-start gap-2 text-[13px] text-[#4b5563]">
                                <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $isFree ? 'text-[#9ca3af]' : 'text-[#16a34a]' }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    {{-- CTA --}}
                    @if ($isFree)
                        <button
                            wire:click="startFree"
                            wire:loading.attr="disabled"
                            class="w-full py-3 rounded-xl text-[13.5px] font-semibold border border-[#e2e4ea] text-[#7c7c86] hover:bg-[#f3f4f6] transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="startFree">Empezar gratis</span>
                            <span wire:loading wire:target="startFree">Cargando...</span>
                        </button>
                    @else
                        <button
                            wire:click="choosePlan('{{ $plan->key }}')"
                            wire:loading.attr="disabled"
                            class="w-full py-3 rounded-xl text-[13.5px] font-semibold transition-colors disabled:opacity-50
                                {{ $isPro
                                    ? 'bg-[#5b69e2] text-white hover:bg-[#4a58d0]'
                                    : 'bg-[#1a1f36] text-white hover:bg-[#2d3452]' }}">
                            <span wire:loading.remove wire:target="choosePlan">Elegir {{ $plan->name }}</span>
                            <span wire:loading wire:target="choosePlan">Redirigiendo...</span>
                        </button>
                    @endif
                </div>
            @endforeach

        </div>

        <p class="text-center text-[12px] text-[#9ca3af] mt-8">
            Puedes cambiar o cancelar tu plan en cualquier momento desde tu portal de facturación.
        </p>
    </section>

</div>
