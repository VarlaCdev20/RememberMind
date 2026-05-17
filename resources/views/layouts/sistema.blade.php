<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RememberMind') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <style>
        .dash-noise {
            background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.85%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');
        }

        .dash-dots {
            background-image: radial-gradient(#2F3E5C 1.2px, transparent 1.2px);
            background-size: 30px 30px;
        }

        .mouse-light {
            position: fixed;
            top: 0;
            left: 0;
            width: 170px;
            height: 170px;
            border-radius: 9999px;
            pointer-events: none;
            z-index: 40;
            opacity: 0;
            background: radial-gradient(circle, rgba(233,122,95,0.14), rgba(233,122,95,0.04), transparent 80%);
            transition: opacity .25s ease;
        }
        
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="font-sans antialiased bg-[#D5C7B9]">
    <x-banner />

    <div
        x-data="{ sidebarOpen: false, sidebarCollapsed: false }"
        class="relative min-h-screen overflow-x-hidden bg-[#D5C7B9] font-outfit text-azul-profundo"
    >
        {{-- Fondos estéticos --}}
        <div class="dash-noise pointer-events-none fixed inset-0 z-[60] opacity-[0.14] mix-blend-overlay"></div>
        <div class="dash-dots pointer-events-none fixed inset-0 z-0 opacity-[0.03]"></div>
        <div class="mouse-light pointer-events-none"></div>

        {{-- Overlay para móvil --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
            style="display:none;"
        ></div>

        {{-- Sidebar Fijo --}}
        <x-layout.barra-lateral-sistema />

        {{-- Navbar Superior --}}
        <x-layout.navbar-sistema />

        {{-- Área Principal de Contenido --}}
        <main
            class="relative min-h-screen pb-8 pt-6 transition-all duration-300 ease-in-out"
            :class="sidebarCollapsed ? 'lg:ml-[82px]' : 'lg:ml-[240px]'"
        >
            <div class="mx-auto max-w-[1540px] space-y-4 px-4 pb-8 sm:px-5 lg:px-6">
                {{ $slot }}
            </div>
        </main>
    </div>

    <x-ui.sweetalert />

    @stack('modals')

    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const light = document.querySelector('.mouse-light');
            if (light) {
                document.addEventListener('mousemove', (e) => {
                    light.style.opacity = '1';
                    light.style.transform = `translate(${e.clientX - 85}px, ${e.clientY - 85}px)`;
                });
                document.addEventListener('mouseleave', () => {
                    light.style.opacity = '0';
                });
            }
        });
    </script>
</body>
</html>
