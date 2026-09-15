<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-[#09090b] text-white font-sans antialiased">
    <div class="flex min-h-screen">
        @livewire('admin.admin-layout-sidebar', ['active' => $active ?? 'plans'])
        <main class="flex-1 min-w-0 px-6 py-8 md:px-10 xl:px-12">
            <div class="mx-auto max-w-[1400px]">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Toast container --}}
    <div
        x-data="{
            toasts: [],
            add(message, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, message, type });
                setTimeout(() => this.remove(id), 4000);
            },
            remove(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        @toast.window="add($event.detail.message, $event.detail.type)"
        class="fixed bottom-5 right-5 z-[100] flex flex-col gap-2"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-sm font-medium min-w-[260px] max-w-sm"
                :class="{
                    'bg-[#18181b] border-emerald-700/50 text-emerald-300': toast.type === 'success',
                    'bg-[#18181b] border-red-700/50 text-red-400': toast.type === 'error',
                    'bg-[#18181b] border-[#3f3f46] text-[#a1a1aa]': toast.type === 'info',
                }"
            >
                {{-- Icon --}}
                <template x-if="toast.type === 'success'">
                    <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="w-4 h-4 shrink-0 text-[#71717a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z"/>
                    </svg>
                </template>
                <span x-text="toast.message" class="flex-1"></span>
                <button @click="remove(toast.id)" class="shrink-0 opacity-50 hover:opacity-100 transition-opacity">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
