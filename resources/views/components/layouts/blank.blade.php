<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-[#f6f6f7] text-[#353636] font-sans antialiased">
    {{ $slot }}
    @livewireScripts
</body>
</html>
