<div class="p-6 space-y-6">

    {{-- ── Estado actual ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-[#e2e4ea] p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-[13.5px] font-semibold text-[#353636] mb-1">Tu suscripción</h2>
                <p class="text-[28px] font-bold tracking-[-0.02em] text-[#1a1f36]">Plan Starter</p>
                <div class="flex items-center gap-3 mt-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-[#dcfce7] text-[#16a34a]">
                        <span class="w-[6px] h-[6px] rounded-full bg-current animate-pulse"></span>
                        Activo
                    </span>
                    <span class="text-[12px] text-[#7c7c86]">Se renueva el 31 ago 2026</span>
                </div>
            </div>
            <a href="#"
               class="shrink-0 text-sm font-medium text-[#5b69e2] border border-[#5b69e2] px-4 py-2 rounded-xl hover:bg-[#5b69e2] hover:text-white transition-colors">
                Gestionar en Stripe →
            </a>
        </div>

        <div class="mt-5 pt-5 border-t border-[#f3f4f6] flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-[#f6f6f7] border border-[#eaeaea] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-[#7c7c86]" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
            </div>
            <div>
                <p class="text-[13px] font-medium text-[#353636]">Visa terminada en 4242</p>
                <p class="text-[11.5px] text-[#7c7c86]">Método de pago activo</p>
            </div>
        </div>
    </div>

    {{-- ── Planes ─────────────────────────────────────────────────────── --}}
    <div>
        <h2 class="text-[13.5px] font-semibold text-[#353636] mb-4">Cambiar plan</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Starter --}}
            <div class="bg-white rounded-2xl border border-[#e2e4ea] p-6 flex flex-col gap-4">
                <div>
                    <p class="text-[13px] font-semibold text-[#353636]">Starter</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-[30px] font-bold tracking-[-0.03em] text-[#1a1f36]">$29</span>
                        <span class="text-[13px] text-[#7c7c86]">/mes</span>
                    </div>
                    <p class="text-[11.5px] text-[#7c7c86] mt-1">500 escaneos/mes</p>
                </div>
                <ul class="space-y-2 flex-1">
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        TikTok e Instagram
                    </li>
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Exportación CSV
                    </li>
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Portal web
                    </li>
                </ul>
                <div class="w-full text-center py-2.5 rounded-xl bg-[#f3f4f6] text-[#7c7c86] text-[13px] font-medium">
                    Plan actual
                </div>
            </div>

            {{-- Pro --}}
            <div class="relative bg-white rounded-2xl border border-[#5b69e2] ring-1 ring-[#5b69e2] p-6 flex flex-col gap-4">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#5b69e2] text-white text-[10.5px] font-semibold px-3 py-1 rounded-full">
                    Más popular
                </span>
                <div>
                    <p class="text-[13px] font-semibold text-[#353636]">Pro</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-[30px] font-bold tracking-[-0.03em] text-[#1a1f36]">$79</span>
                        <span class="text-[13px] text-[#7c7c86]">/mes</span>
                    </div>
                    <p class="text-[11.5px] text-[#7c7c86] mt-1">2,000 escaneos/mes</p>
                </div>
                <ul class="space-y-2 flex-1">
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Todo lo de Starter
                    </li>
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Webhooks
                    </li>
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Soporte prioritario
                    </li>
                </ul>
                <button class="w-full py-2.5 rounded-xl text-[13px] font-semibold bg-[#5b69e2] text-white hover:bg-[#4a58d0] transition-colors">
                    Elegir Pro
                </button>
            </div>

            {{-- Agency --}}
            <div class="bg-white rounded-2xl border border-[#e2e4ea] p-6 flex flex-col gap-4">
                <div>
                    <p class="text-[13px] font-semibold text-[#353636]">Agency</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-[30px] font-bold tracking-[-0.03em] text-[#1a1f36]">$199</span>
                        <span class="text-[13px] text-[#7c7c86]">/mes</span>
                    </div>
                    <p class="text-[11.5px] text-[#7c7c86] mt-1">10,000 escaneos/mes</p>
                </div>
                <ul class="space-y-2 flex-1">
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Todo lo de Pro
                    </li>
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Multi-usuario próximamente
                    </li>
                    <li class="flex items-center gap-2 text-[12.5px] text-[#353636]">
                        <svg class="w-4 h-4 text-[#16a34a] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        SLA garantizado
                    </li>
                </ul>
                <button class="w-full py-2.5 rounded-xl text-[13px] font-semibold bg-[#f3f4f6] text-[#353636] hover:bg-[#e5e7eb] transition-colors">
                    Elegir Agency
                </button>
            </div>

        </div>
    </div>

</div>
