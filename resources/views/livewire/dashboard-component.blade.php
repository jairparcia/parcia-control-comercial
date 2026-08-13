<div class="p-6 space-y-6">

    {{-- ── Cuota de escaneos ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-[#e2e4ea] p-6 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-[#6b7280]">Escaneos este mes</p>
                <p class="text-2xl font-bold text-[#1a1f36]">
                    {{ $presenter->scansUsed() }}
                    <span class="text-base font-normal text-[#6b7280]">/ {{ $presenter->scansLimit() }}</span>
                </p>
            </div>
            <span class="text-sm text-[#6b7280]">{{ $presenter->scansRemaining() }} restantes</span>
        </div>

        <div class="w-full bg-[#f3f4f6] rounded-full h-2.5 overflow-hidden">
            <div class="{{ $presenter->quotaBarColor() }} h-2.5 rounded-full transition-all duration-500"
                 style="width: {{ $presenter->scansPercent() }}%"></div>
        </div>

        <p class="text-xs text-[#9ca3af]">{{ $presenter->periodLabel() }}</p>

        @if ($presenter->isUpgradeable())
            <div class="flex items-center gap-3 mt-1 p-3 bg-[#fff9e6] border border-[#f59e0b]/30 rounded-xl text-sm">
                <svg class="w-4 h-4 text-[#f59e0b] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                </svg>
                <span class="text-[#92400e]">{{ $presenter->upgradeWarning() }}</span>
                <a href="{{ route('billing') }}" class="ml-auto text-[#5b69e2] font-medium hover:underline whitespace-nowrap">
                    Mejorar plan →
                </a>
            </div>
        @endif
    </div>

    {{-- ── Stats rápidos ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Total creadores --}}
        <div class="bg-white rounded-2xl border border-[#e2e4ea] p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-[#ede9fe] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-[#7c3aed]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-[#6b7280] font-medium">Creadores</p>
                <p class="text-xl font-bold text-[#1a1f36]">{{ $presenter->totalCreators() }}</p>
            </div>
        </div>

        {{-- Total publicaciones --}}
        <div class="bg-white rounded-2xl border border-[#e2e4ea] p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-[#fce7f3] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-[#be185d]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82V18a1 1 0 01-1.447.894L15 17M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-[#6b7280] font-medium">Publicaciones</p>
                <p class="text-xl font-bold text-[#1a1f36]">{{ $presenter->totalPublications() }}</p>
            </div>
        </div>

        {{-- Plan activo --}}
        <div class="bg-white rounded-2xl border border-[#e2e4ea] p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-[#e0f2fe] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-[#0369a1]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-[#6b7280] font-medium">Plan activo</p>
                <p class="text-xl font-bold text-[#1a1f36]">{{ $presenter->planName() }}</p>
                <p class="text-xs text-[#9ca3af]">Vence {{ $presenter->planExpiry() }}</p>
            </div>
        </div>

    </div>

    {{-- ── Creadores recientes + Licencia ────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Creadores recientes --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-[#e2e4ea] p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-[#1a1f36]">Creadores recientes</h2>
                <a href="{{ route('creators') }}" class="text-sm text-[#5b69e2] hover:underline">Ver todos →</a>
            </div>

            <div class="divide-y divide-[#f3f4f6]">
                @foreach ($presenter->recentCreators() as $creator)
                    <div class="flex items-center gap-3 py-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold shrink-0"
                             style="background:{{ $creator['bg'] }}; color:{{ $creator['color'] }}">
                            {{ $creator['initials'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-[#1a1f36] truncate">{{ $creator['name'] }}</p>
                            <p class="text-xs text-[#9ca3af] truncate">{{ $creator['handle'] }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $creator['platform'] === 'tiktok' ? 'bg-[#f0fdf4] text-[#15803d]' : 'bg-[#fce7f3] text-[#9d174d]' }}">
                            {{ $creator['platform'] }}
                        </span>
                        <span class="text-sm font-medium text-[#374151] tabular-nums">{{ $creator['followers'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Clave de licencia --}}
        <div class="bg-white rounded-2xl border border-[#e2e4ea] p-6 space-y-4">
            <h2 class="font-semibold text-[#1a1f36]">Clave de licencia</h2>

            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium {{ $presenter->licenseStatusColor() }}">
                        ● {{ $presenter->licenseStatusLabel() }}
                    </span>
                </div>
                <div class="flex items-center gap-2 bg-[#f9fafb] rounded-xl px-3 py-2 border border-[#e5e7eb]">
                    <code class="text-xs text-[#374151] flex-1 truncate font-mono">
                        {{ $presenter->licenseKey() }}
                    </code>
                    <button
                        onclick="navigator.clipboard.writeText('{{ $presenter->licenseKey() }}')"
                        class="text-[#9ca3af] hover:text-[#5b69e2] transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="pt-2 border-t border-[#f3f4f6]">
                <a href="{{ route('settings') }}"
                   class="block w-full text-center py-2 rounded-xl bg-[#5b69e2] text-white text-sm font-medium hover:bg-[#4a58d0] transition-colors">
                    Gestionar clave
                </a>
            </div>
        </div>

    </div>

</div>
