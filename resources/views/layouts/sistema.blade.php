<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RememberMind · Los Almendros</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    @vite(['resources/frontend/styles/sistema.css', 'resources/frontend/scripts/app.js'])
    @livewireStyles
</head>
<body class="rm-app-shell">
    <a class="rm-skip-link" href="#contenido-principal">Saltar al contenido</a>
    <div x-data="{ sidebarOpen: false, compact: window.innerWidth < 1024 }" @resize.window="compact = window.innerWidth < 1024; if (!compact) sidebarOpen = false" @keydown.escape.window="if (sidebarOpen) { sidebarOpen = false; $nextTick(() => $refs.menuTrigger?.focus()) }" class="rm-shell">
        <div x-cloak x-show="sidebarOpen" class="rm-sidebar-backdrop" @click="sidebarOpen = false; $nextTick(() => $refs.menuTrigger?.focus())" aria-hidden="true"></div>
        <x-layout.barra-lateral-sistema />
        <div class="rm-shell-main">
            <x-layout.navbar-sistema />
            <main id="contenido-principal" class="rm-main-content" tabindex="-1">
                {{ $slot }}
            </main>
        </div>
    </div>
    <x-ui.toast />
    @livewireScripts
</body>
</html>
