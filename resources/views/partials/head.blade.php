<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? 'Parcia Plugin' }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=lexend:300,400,500,600,700&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
