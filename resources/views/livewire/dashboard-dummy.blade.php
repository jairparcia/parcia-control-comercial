<div>
    {{-- PAGE HEADER --}}
    <div class="flex items-end justify-between gap-4 pb-2 mb-7">
        <div>
            <h1 class="text-[34px] font-semibold leading-none tracking-[-0.025em] text-[#2f2f35]">
                Dashboard
            </h1>
            <p class="mt-2 text-sm text-[#7c7c86]">
                Resumen de uso, creadores escaneados y estado de tu suscripción.
            </p>
        </div>
    </div>

    {{-- UPGRADE BANNER --}}
    <div class="flex items-center justify-between gap-5 bg-[#eceffe] border border-[#c7cdf8] rounded-xl px-5 py-3.5 mb-5">
        <div>
            <p class="text-sm font-medium text-[#4a5bcc]">Estás al 68% de tu cuota mensual</p>
            <p class="text-xs text-[#4a5bcc]/70 mt-0.5">Actualiza a Pro para escaneos ilimitados, webhooks y exportación avanzada.</p>
        </div>
        <button class="shrink-0 bg-[#5b69e2] hover:opacity-85 text-white text-[13px] font-semibold rounded-lg px-4 py-2 transition-opacity duration-150">
            Actualizar a Pro →
        </button>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3.5 mb-5">

        <div class="bg-white border border-[#eaeaea] rounded-xl px-5 py-5">
            <p class="text-[10.5px] font-medium uppercase tracking-[0.07em] text-[#7c7c86] mb-2.5">Escaneados este mes</p>
            <p class="text-[30px] font-bold leading-none tracking-[-0.025em] text-[#353636] tabular-nums">342</p>
            <p class="text-[11.5px] text-[#7c7c86] mt-1.5">de 500 en tu plan</p>
        </div>

        <div class="bg-white border border-[#eaeaea] rounded-xl px-5 py-5">
            <p class="text-[10.5px] font-medium uppercase tracking-[0.07em] text-[#7c7c86] mb-2.5">Creadores guardados</p>
            <p class="text-[30px] font-bold leading-none tracking-[-0.025em] text-[#353636] tabular-nums">128</p>
            <p class="text-[11.5px] text-[#7c7c86] mt-1.5">TikTok e Instagram</p>
        </div>

        <div class="bg-white border border-[#eaeaea] rounded-xl px-5 py-5">
            <p class="text-[10.5px] font-medium uppercase tracking-[0.07em] text-[#7c7c86] mb-2.5">Publicaciones</p>
            <p class="text-[30px] font-bold leading-none tracking-[-0.025em] text-[#353636] tabular-nums">2,847</p>
            <p class="text-[11.5px] text-[#7c7c86] mt-1.5">En todas las plataformas</p>
        </div>

        <div class="bg-white border border-[#eaeaea] rounded-xl px-5 py-5">
            <p class="text-[10.5px] font-medium uppercase tracking-[0.07em] text-[#7c7c86] mb-2.5">Plan activo</p>
            <p class="text-[22px] font-bold leading-none tracking-[-0.02em] text-[#353636] mt-1">Starter</p>
            <span class="inline-flex items-center gap-1.5 mt-2.5 bg-[#dcfce7] text-[#15803d] text-[11px] font-medium px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                Activo · vence ago 31
            </span>
        </div>
    </div>

    {{-- CONTENT GRID --}}
    <div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-5">

        {{-- LEFT COLUMN --}}
        <div class="flex flex-col gap-5">

            {{-- QUOTA --}}
            <div class="bg-white border border-[#eaeaea] rounded-xl px-6 py-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[13.5px] font-semibold text-[#353636]">Uso mensual</h2>
                    <span class="text-[13px] text-[#7c7c86] tabular-nums">68%</span>
                </div>
                <div class="flex items-baseline justify-between mb-2.5">
                    <div>
                        <span class="text-[26px] font-bold tracking-[-0.02em] text-[#353636] tabular-nums">342</span>
                        <span class="text-[15px] font-normal text-[#7c7c86]"> / 500 scans</span>
                    </div>
                    <span class="text-[12px] text-[#7c7c86]">158 restantes</span>
                </div>
                <div class="h-[7px] bg-[#eaeaea] rounded-full overflow-hidden mb-2">
                    <div class="h-full rounded-full transition-all duration-700 bg-[#5b69e2]" style="width: 68%"></div>
                </div>
                <p class="text-[11.5px] text-[#7c7c86]">Período: 1–31 agosto 2026 · Se reinicia en 26 días</p>
            </div>

            {{-- RECENT CREATORS --}}
            <div class="bg-white border border-[#eaeaea] rounded-xl px-6 py-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[13.5px] font-semibold text-[#353636]">Creadores recientes</h2>
                    <a href="#" class="text-[12px] font-medium text-[#5b69e2] hover:underline">Ver todos →</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="text-left text-[10.5px] font-medium uppercase tracking-[0.06em] text-[#7c7c86] pb-3 border-b border-[#eaeaea]">Creador</th>
                                <th class="text-left text-[10.5px] font-medium uppercase tracking-[0.06em] text-[#7c7c86] pb-3 border-b border-[#eaeaea]">Plataforma</th>
                                <th class="text-right text-[10.5px] font-medium uppercase tracking-[0.06em] text-[#7c7c86] pb-3 border-b border-[#eaeaea]">Seguidores</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-3 border-b border-[#eaeaea]">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-semibold shrink-0" style="background:#e9dfcf;color:#5c4a36">MR</div>
                                        <div>
                                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">@mariaruiz</p>
                                            <p class="text-[11px] text-[#7c7c86] mt-0.5">María Ruiz</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 border-b border-[#eaeaea]">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-[#efefef] text-[#353636]">TikTok</span>
                                </td>
                                <td class="py-3 border-b border-[#eaeaea] text-right">
                                    <span class="text-[13px] font-medium text-[#353636] tabular-nums">1.2M</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 border-b border-[#eaeaea]">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-semibold shrink-0" style="background:#fce7f3;color:#9d174d">JG</div>
                                        <div>
                                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">@jorge.garcia</p>
                                            <p class="text-[11px] text-[#7c7c86] mt-0.5">Jorge García</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 border-b border-[#eaeaea]">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-[#fce7f3] text-[#c13584]">Instagram</span>
                                </td>
                                <td class="py-3 border-b border-[#eaeaea] text-right">
                                    <span class="text-[13px] font-medium text-[#353636] tabular-nums">847K</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 border-b border-[#eaeaea]">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-semibold shrink-0" style="background:#dbeafe;color:#1e40af">LV</div>
                                        <div>
                                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">@luisavieira</p>
                                            <p class="text-[11px] text-[#7c7c86] mt-0.5">Luisa Vieira</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 border-b border-[#eaeaea]">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-[#efefef] text-[#353636]">TikTok</span>
                                </td>
                                <td class="py-3 border-b border-[#eaeaea] text-right">
                                    <span class="text-[13px] font-medium text-[#353636] tabular-nums">412K</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 border-b border-[#eaeaea]">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-semibold shrink-0" style="background:#fce7f3;color:#9d174d">CP</div>
                                        <div>
                                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">@carla.perez</p>
                                            <p class="text-[11px] text-[#7c7c86] mt-0.5">Carla Pérez</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 border-b border-[#eaeaea]">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-[#fce7f3] text-[#c13584]">Instagram</span>
                                </td>
                                <td class="py-3 border-b border-[#eaeaea] text-right">
                                    <span class="text-[13px] font-medium text-[#353636] tabular-nums">289K</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-semibold shrink-0" style="background:#f0fdf4;color:#15803d">AM</div>
                                        <div>
                                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">@andres.moreno</p>
                                            <p class="text-[11px] text-[#7c7c86] mt-0.5">Andrés Moreno</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-[#efefef] text-[#353636]">TikTok</span>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="text-[13px] font-medium text-[#353636] tabular-nums">178K</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="flex flex-col gap-5">

            {{-- QUICK ACTIONS --}}
            <div class="bg-white border border-[#eaeaea] rounded-xl px-6 py-5">
                <h2 class="text-[13.5px] font-semibold text-[#353636] mb-4">Acciones rápidas</h2>
                <div class="flex flex-col gap-2">

                    <button class="flex items-center gap-3.5 w-full text-left px-4 py-3.5 rounded-xl border border-[#eaeaea] bg-[#f6f6f7] hover:bg-[#f3f3f3] hover:border-transparent transition-colors duration-150 cursor-pointer">
                        <div class="w-9 h-9 rounded-[9px] bg-[#eceffe] flex items-center justify-center shrink-0">
                            <svg class="w-[17px] h-[17px] text-[#5b69e2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                <line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">Ver mis creadores</p>
                            <p class="text-[11.5px] text-[#7c7c86] mt-0.5">128 perfiles guardados</p>
                        </div>
                    </button>

                    <button class="flex items-center gap-3.5 w-full text-left px-4 py-3.5 rounded-xl border border-[#eaeaea] bg-[#f6f6f7] hover:bg-[#f3f3f3] hover:border-transparent transition-colors duration-150 cursor-pointer">
                        <div class="w-9 h-9 rounded-[9px] bg-[#eceffe] flex items-center justify-center shrink-0">
                            <svg class="w-[17px] h-[17px] text-[#5b69e2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">Exportar CSV</p>
                            <p class="text-[11.5px] text-[#7c7c86] mt-0.5">Descarga todos tus datos</p>
                        </div>
                    </button>

                    <button class="flex items-center gap-3.5 w-full text-left px-4 py-3.5 rounded-xl border border-[#eaeaea] bg-[#f6f6f7] hover:bg-[#f3f3f3] hover:border-transparent transition-colors duration-150 cursor-pointer">
                        <div class="w-9 h-9 rounded-[9px] bg-[#eceffe] flex items-center justify-center shrink-0">
                            <svg class="w-[17px] h-[17px] text-[#5b69e2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">Ver licencia</p>
                            <p class="text-[11.5px] text-[#7c7c86] mt-0.5">Copiar clave del plugin</p>
                        </div>
                    </button>

                    <button class="flex items-center gap-3.5 w-full text-left px-4 py-3.5 rounded-xl border border-[#eaeaea] bg-[#f6f6f7] hover:bg-[#f3f3f3] hover:border-transparent transition-colors duration-150 cursor-pointer">
                        <div class="w-9 h-9 rounded-[9px] bg-[#eceffe] flex items-center justify-center shrink-0">
                            <svg class="w-[17px] h-[17px] text-[#5b69e2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[13px] font-medium text-[#353636] leading-[1.2]">Gestionar suscripción</p>
                            <p class="text-[11.5px] text-[#7c7c86] mt-0.5">Portal de Stripe</p>
                        </div>
                    </button>

                </div>
            </div>

            {{-- LICENSE KEY --}}
            <div class="bg-white border border-[#eaeaea] rounded-xl px-6 py-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-[13.5px] font-semibold text-[#353636]">Clave de licencia</h2>
                    <span class="flex items-center gap-1.5 text-[11.5px] font-medium text-[#16a34a]">
                        <span class="w-[7px] h-[7px] rounded-full bg-current animate-pulse"></span>
                        Activa
                    </span>
                </div>
                <p class="text-[12.5px] text-[#7c7c86] leading-relaxed mb-3.5">
                    Usa esta clave en el plugin de Chrome para autenticar tus escaneos. Mantenla privada.
                </p>
                <div class="flex items-center gap-2.5 bg-[#f6f6f7] border border-[#eaeaea] rounded-lg px-3.5 py-3">
                    <span class="font-mono text-[11.5px] text-[#353636] tracking-[0.01em] flex-1 min-w-0 truncate">
                        pk_live_a1b2c3d4-e5f6-7890-abcd-ef1234567890
                    </span>
                    <button
                        onclick="navigator.clipboard.writeText('pk_live_a1b2c3d4-e5f6-7890-abcd-ef1234567890')"
                        class="shrink-0 flex items-center justify-center p-1 rounded text-[#7c7c86] hover:bg-[#eaeaea] hover:text-[#353636] transition-colors duration-150"
                        title="Copiar clave"
                    >
                        <svg class="w-[15px] h-[15px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                            <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
