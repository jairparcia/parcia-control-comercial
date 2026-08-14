<div class="p-6 space-y-6">

    {{-- ── Estado actual de suscripción ──────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-[#e2e4ea] p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-[13.5px] font-semibold text-[#353636] mb-1">Tu suscripción</h2>

                @if ($presenter->hasSubscription())
                    <p class="text-[28px] font-bold tracking-[-0.02em] text-[#1a1f36]">
                        {{ $presenter->planLabel() }}
                    </p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium {{ $presenter->statusBg() }} {{ $presenter->statusColor() }}">
                            <span class="w-[6px] h-[6px] rounded-full bg-current {{ $presenter->isActive() ? 'animate-pulse' : '' }}"></span>
                            {{ $presenter->statusLabel() }}
                        </span>
                        @if ($presenter->renewsAt() !== '—')
                            <span class="text-[12px] text-[#7c7c86]">
                                Se renueva el {{ $presenter->renewsAt() }}
                            </span>
                        @endif
                    </div>
                @else
                    <p class="text-[20px] font-semibold text-[#7c7c86]">Sin plan activo</p>
                    <p class="text-sm text-[#9ca3af] mt-1">
                        Elige un plan para empezar a usar el plugin.
                    </p>
                @endif
            </div>

            @if ($presenter->hasSubscription())
                <a href="{{ route('billing.portal') }}"
                   class="shrink-0 text-sm font-medium text-[#5b69e2] border border-[#5b69e2] px-4 py-2 rounded-xl hover:bg-[#5b69e2] hover:text-white transition-colors">
                    Gestionar en Stripe →
                </a>
            @endif
        </div>

        @if ($presenter->hasPaymentMethod())
            <div class="mt-5 pt-5 border-t border-[#f3f4f6] flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#f6f6f7] border border-[#eaeaea] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#7c7c86]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[13px] font-medium text-[#353636] capitalize">
                        {{ $presenter->pmType() }} terminada en {{ $presenter->pmLastFour() }}
                    </p>
                    <p class="text-[11.5px] text-[#7c7c86]">Método de pago activo</p>
                </div>
            </div>
        @endif
    </div>

    {{-- ── Planes ─────────────────────────────────────────────────────── --}}
    <div>
        <h2 class="text-[13.5px] font-semibold text-[#353636] mb-4">
            {{ $presenter->hasSubscription() ? 'Cambiar plan' : 'Elige un plan' }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($presenter->plans() as $plan)
                <div class="relative bg-white rounded-2xl border p-6 flex flex-col gap-4
                    {{ ($plan['highlight'] ?? false) ? 'border-[#5b69e2] ring-1 ring-[#5b69e2]' : 'border-[#e2e4ea]' }}">

                    @if ($plan['highlight'] ?? false)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#5b69e2] text-white text-[10.5px] font-semibold px-3 py-1 rounded-full">
                            Más popular
                        </span>
                    @endif

                    <div>
                        <p class="text-[13px] font-semibold text-[#353636]">{{ $plan['name'] }}</p>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-[30px] font-bold tracking-[-0.03em] text-[#1a1f36]">
                                {{ $plan['price'] }}
                            </span>
                            <span class="text-[13px] text-[#7c7c86]">/{{ $plan['period'] }}</span>
                        </div>
                        <p class="text-[11.5px] text-[#7c7c86] mt-1">{{ $plan['quota'] }}</p>
                    </div>

                    <ul class="space-y-2 flex-1">
                        @foreach ($plan['features'] as $feature)
                            <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                                <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    @if ($plan['current'])
                        <div class="w-full text-center py-2.5 rounded-xl bg-[#f3f4f6] text-[#7c7c86] text-[13px] font-medium">
                            Plan actual
                        </div>
                    @else
                        <button
                            wire:click="checkout('{{ $plan['key'] }}')"
                            wire:loading.attr="disabled"
                            class="w-full py-2.5 rounded-xl text-[13px] font-semibold transition-colors disabled:opacity-50
                                {{ ($plan['highlight'] ?? false)
                                    ? 'bg-[#5b69e2] text-white hover:bg-[#4a58d0]'
                                    : 'bg-[#f3f4f6] text-[#353636] hover:bg-[#e5e7eb]' }}">
                            <span wire:loading.remove wire:target="checkout">Elegir {{ $plan['name'] }}</span>
                            <span wire:loading wire:target="checkout">Redirigiendo...</span>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</div>
