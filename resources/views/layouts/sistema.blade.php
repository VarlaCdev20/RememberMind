<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">

 <title>{{ config('app.name', 'RememberMind') }}</title>

 <link rel="preconnect" href="https://fonts.bunny.net">
 <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
 <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
 rel="stylesheet">

 <script defer src="https://unpkg.com/@phosphor-icons/web"></script>

 @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])

     @stack('styles')

    @livewireStyles
</head>

<body class="antialiased bg-fondo-app">
 <x-banner />

 <div x-data="{ sidebarOpen: false, sidebarCollapsed: false }"
 class="rm-bg-app relative min-h-screen overflow-x-hidden font-sans text-titulo selection:bg-[var(--rm-action-primary)] selection:text-[var(--rm-text-on-primary)]">
 {{-- Fondos estéticos --}}
 <div class="rm-texture-dots pointer-events-none fixed inset-0 z-0 opacity-40"></div>
 <div class="rm-mouse-light pointer-events-none fixed inset-0 z-40"></div>

 {{-- Overlay para móvil --}}
 <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
 class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" style="display:none;"></div>

 {{-- Sidebar Fijo --}}
 <x-layout.barra-lateral-sistema />

 {{-- Navbar Superior --}}
 <x-layout.navbar-sistema />

 {{-- Área Principal de Contenido --}}
 <main class="rm-depth-canvas relative min-w-0 min-h-[calc(100vh-64px)] pb-6 pt-6 transition-all duration-300 ease-in-out"
 :class="sidebarCollapsed ? 'lg:ml-[76px]' : 'lg:ml-[248px]'">
 <div class="mx-auto min-w-0 max-w-[1440px] space-y-6 px-4 pb-6 sm:px-6">
 {{ $slot }}
 </div>
 </main>
 </div>

 <x-ui.sweetalert />

 @stack('modals')

 @livewireScripts
    @stack('scripts')
</body>

</html>
