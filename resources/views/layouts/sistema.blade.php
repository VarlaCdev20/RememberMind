<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">

 <title>{{ config('app.name', 'RememberMind') }}</title>

 <link rel="preconnect" href="https://fonts.bunny.net">
 <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
 <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap"
 rel="stylesheet">

 <script src="https://unpkg.com/@phosphor-icons/web"></script>
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

 @vite(['resources/css/app.css', 'resources/js/app.js'])

 @livewireStyles
</head>

<body class="antialiased bg-fondo-app">
 <x-banner />

 <div x-data="{ sidebarOpen: false, sidebarCollapsed: false }"
 class="rm-bg-app relative min-h-screen overflow-x-hidden font-outfit text-titulo selection:bg-boton-acento selection:text-inverso">
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
 <main class="relative min-h-screen pb-8 pt-6 transition-all duration-300 ease-in-out"
 :class="sidebarCollapsed ? 'lg:ml-[82px]' : 'lg:ml-[240px]'">
 <div class="mx-auto max-w-[1540px] space-y-4 px-4 pb-8 sm:px-5 lg:px-6">
 {{ $slot }}
 </div>
 </main>
 </div>

 <x-ui.sweetalert />

 @stack('modals')

 @livewireScripts
</body>

</html>