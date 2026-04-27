<!DOCTYPE html>
<html lang="pl" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Agent Command Center') }}</title>

    {{-- Google Fonts: Inter + JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet"
    />

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- Fixed Topbar --}}
    <x-topbar />

    {{-- Main content pushed below topbar --}}
    <main class="pt-12 min-h-screen" style="background-color: #0a0a0b;">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
