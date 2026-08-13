<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-[#f6f6f7] font-sans antialiased flex items-center justify-center">

    <div class="w-full max-w-sm px-6">
        <div class="mb-8 text-center">
            <div class="inline-flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-[#353636] flex items-center justify-center">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 15 15">
                        <rect x="1" y="1" width="5.5" height="5.5" rx="1"/>
                        <rect x="8.5" y="1" width="5.5" height="5.5" rx="1"/>
                        <rect x="8.5" y="8.5" width="5.5" height="5.5" rx="1"/>
                        <rect x="1" y="8.5" width="5.5" height="5.5" rx="1"/>
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-[#353636]">Parcia Plugin</span>
            </div>
            <h1 class="text-2xl font-semibold text-[#2f2f35] tracking-tight">Accede a tu cuenta</h1>
            <p class="mt-2 text-sm text-[#7c7c86]">Inicia sesión con tu cuenta de Google</p>
        </div>

        <div class="bg-white border border-[#eaeaea] rounded-2xl p-8 shadow-sm">
            <a
                href="{{ route('auth.google') }}"
                class="flex items-center justify-center gap-3 w-full py-3 px-4 rounded-xl border border-[#eaeaea] bg-white hover:bg-[#f6f6f7] text-sm font-medium text-[#353636] transition-colors duration-150"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continuar con Google
            </a>
        </div>

        <p class="mt-6 text-center text-xs text-[#9ca3af]">
            Al acceder, aceptas los términos de uso de Parcia Plugin.
        </p>
    </div>

    @livewireScripts
</body>
</html>
