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
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">

    {{-- Phosphor Icons Oficial --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    {{-- Chart.js Oficial --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Nunito Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        :root {
            --glow-primary: rgba(113, 135, 106, 0.08);
            --glow-warm: rgba(163, 90, 68, 0.04);

            /* Paleta unificada heredada de Design System V2 */
        }

        /* Modo oscuro manejado canónicamente por design-system/tokens/colors.css */

        /* Utilidad para barra de scroll estilizada */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--rm-border);
            border-radius: 9999px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--rm-surface-raised);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--rm-border-hover);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--rm-border-strong);
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

<body class="h-full bg-[var(--rm-bg-app)] text-[var(--rm-text-primary)] antialiased selection:bg-[var(--rm-action-primary)] selection:text-[var(--rm-text-on-primary)] transition-colors duration-200"
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

    <div class="min-h-screen bg-[var(--rm-bg-app)] transition-colors duration-200">

        {{-- Backdrop para móviles / tablets --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-[var(--rm-modal-overlay,rgba(64,42,32,0.45))] backdrop-blur-xs lg:hidden"
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
