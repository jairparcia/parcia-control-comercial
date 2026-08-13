<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-[#f6f6f7] text-[#353636] font-sans antialiased">
    <div class="flex min-h-screen">
        @livewire('layout.admin-sidebar', ['active' => $active ?? 'dashboard'])
        <main class="flex-1 min-w-0 px-6 py-8 md:px-10 xl:px-12">
            <div class="mx-auto max-w-[1400px]">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
