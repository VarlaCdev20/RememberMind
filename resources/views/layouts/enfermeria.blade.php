<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RememberMind') }} - Enfermería</title>

    {{-- Script Anti-FOUC para Modo Oscuro Inmediato (Sin Parpadeo) --}}
    <script>
        (function() {
            const saved = localStorage.getItem('remembermind-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = saved === 'dark' || (!saved && prefersDark);
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.style.colorScheme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.style.colorScheme = 'light';
            }
        })();
    </script>

    {{-- Tipografía Oficial Google Fonts (Outfit) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Phosphor Icons Oficial --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    {{-- Chart.js Oficial --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Outfit', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        :root {
            --glow-primary: rgba(113, 135, 106, 0.08);
            --glow-warm: rgba(163, 90, 68, 0.04);

            /* Superadmin & Enfermería Unified Canonical Palette - Light Mode */
            --rm-bg-app: #E9DFD3;
            --rm-surface: #F0E8DE;
            --rm-surface-alt: #F7F1EA;
            --rm-surface-highlight: #E5D8CB;
            --rm-border: #D5CABE;
            --rm-border-strong: #C4B4A4;
            --rm-text-primary: #304060;
            --rm-text-secondary: #677084;
            --rm-text-muted: #8F8275;
            --rm-navy: #304060;
            --rm-terracotta: #A35A44;
            --rm-terracotta-soft: #D6AE86;
            --rm-sage: #71876A;
            --rm-sage-dark: #597053;
            --rm-amber: #D2A45E;
            --rm-amber-soft: #E8C988;
            --rm-coral: #C85D52;
            --rm-coral-soft: #E8988E;
        }

        .dark {
            /* Superadmin & Enfermería Unified Canonical Palette - Dark Mode */
            --rm-bg-app: #24211D;
            --rm-surface: #2D2924;
            --rm-surface-alt: #332F29;
            --rm-surface-highlight: #3D3830;
            --rm-border: #494139;
            --rm-border-strong: #5A5147;
            --rm-text-primary: #EFE4D8;
            --rm-text-secondary: #BDAE9F;
            --rm-text-muted: #8D8073;
            --rm-navy: #EFE4D8;
            --rm-terracotta: #C47B63;
            --rm-terracotta-soft: #D6AE86;
            --rm-sage: #93A587;
            --rm-sage-dark: #A8B99E;
            --rm-amber: #D1A25C;
            --rm-amber-soft: #E5BA79;
            --rm-coral: #D47167;
            --rm-coral-soft: #E8988E;
        }

        /* Utilidad para barra de scroll estilizada */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #D5CABE;
            border-radius: 9999px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #403A32;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #B8A896;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #50493F;
        }

        /* Animación suave de entrada */
        @keyframes rm-fade-in-up {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: none;
            }
        }

        .animate-fade-in-up {
            animation: rm-fade-in-up 250ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-fade-in-up {
                animation: none !important;
                transform: none !important;
            }
            * {
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="h-full bg-[#E9DFD3] dark:bg-[#24211D] text-[#304060] dark:text-[#EFE4D8] antialiased selection:bg-[#71876A] selection:text-white transition-colors duration-200"
      x-data="{
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('remembermind-sidebar-collapsed') === 'true',
          toggleSidebarCollapse() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('remembermind-sidebar-collapsed', this.sidebarCollapsed);
          },
          userDropdown: false,
          darkMode: document.documentElement.classList.contains('dark'),
          toggleDarkMode() {
              const nextDark = !this.darkMode;
              this.darkMode = nextDark;
              document.documentElement.classList.toggle('dark', nextDark);
              document.documentElement.setAttribute('data-theme', nextDark ? 'dark' : 'light');
              document.documentElement.style.colorScheme = nextDark ? 'dark' : 'light';
              localStorage.setItem('remembermind-theme', nextDark ? 'dark' : 'light');

              // Disparo de evento global para componentes reactivos o gráficos
              window.dispatchEvent(
                  new CustomEvent('remembermind:theme-changed', {
                      detail: {
                          theme: nextDark ? 'dark' : 'light',
                          resolvedTheme: nextDark ? 'dark' : 'light',
                          isDark: nextDark,
                      },
                  })
              );
          }
      }"
      @remembermind:theme-changed.window="darkMode = $event.detail.isDark">

    <div class="min-h-screen bg-[#E9DFD3] dark:bg-[#24211D] transition-colors duration-200">

        {{-- Backdrop para móviles / tablets --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-[#304060]/50 dark:bg-black/75 backdrop-blur-xs lg:hidden"
             style="display: none;"
             aria-hidden="true"></div>

        {{-- ========================================================= --}}
        {{-- SIDEBAR OFICIAL UNIFICADO DE ENFERMERÍA (EXPANDIBLE)       --}}
        {{-- ========================================================= --}}
        <x-layout.sidebar-enfermeria />

        {{-- ========================================================= --}}
        {{-- TOPBAR INSTITUCIONAL COMPARTIDO (IDÉNTICO A SUPERADMIN)   --}}
        {{-- ========================================================= --}}
        <x-layout.topbar-enfermeria />

        {{-- ========================================================= --}}
        {{-- CONTENIDO PRINCIPAL: ANCHO EXACTO Y ESPACIADO             --}}
        {{-- ========================================================= --}}
        <main class="min-h-[calc(100vh-74px)] pb-10 transition-all duration-300 ease-in-out"
              :class="sidebarCollapsed ? 'lg:pl-[80px]' : 'lg:pl-[260px]'">
            <div class="mx-auto max-w-[1600px] px-3.5 sm:px-5 lg:px-6 pt-4 animate-fade-in-up">
                {{ $slot }}
            </div>
        </main>
    </div>

    <x-ui.sweetalert />

    @stack('modals')
    @livewireStyles
    @stack('scripts')
</body>

</html>
