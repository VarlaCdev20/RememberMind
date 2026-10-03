<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RememberMind') }}</title>

    {{-- Script Anti-FOUC para Modo Oscuro Inmediato (Sin Parpadeo) --}}
    <script>
        (function() {
            const saved = localStorage.getItem('remembermind-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = saved === 'dark' || ((!saved || saved === 'system') && prefersDark);
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

    {{-- Tipografía Oficial Google Fonts (Nunito Sans) --}}

    {{-- Phosphor Icons Oficial --}}
    <script defer src="https://unpkg.com/@phosphor-icons/web"></script>

    {{-- Chart.js Oficial --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/frontend/styles/app.css', 'resources/frontend/scripts/app.js'])

    @stack('styles')
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
            }
            to {
                opacity: 1;
            }
        }

        .animate-fade-in-up {
            animation: rm-fade-in-up 250ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-fade-in-up {
                animation: none !important;
            }
            * {
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
            }
        }
    </style>
</head>

@php
    $shellPreviewRole = app(\App\Backend\Modulos\Identidad\Servicios\RolePreviewService::class)->activeRole(auth()->user());
    $shellAdministracion = $shellPreviewRole === 'ADMINISTRADOR'
        || ($shellPreviewRole === null && auth()->user()?->hasRole('ADMINISTRADOR') && ! auth()->user()?->hasRole('SUPERADMINISTRADOR'));
@endphp

<body class="rm-shell {{ $shellAdministracion ? 'rm-shell--administracion' : '' }} h-full bg-[var(--rm-bg-app)] text-[var(--rm-text-primary)] antialiased selection:bg-[var(--rm-action-primary)] selection:text-[var(--rm-text-on-primary)] transition-colors duration-200"
      x-data="{
          @include('components.layout.sidebar-shell-state')
          userDropdown: false,
          darkMode: document.documentElement.classList.contains('dark'),
          toggleDarkMode() {
              if (window.RememberMindTheme) { window.RememberMindTheme.toggle(); return; }
              const nextDark = !this.darkMode;
              this.darkMode = nextDark;
              document.documentElement.classList.toggle('dark', nextDark);
              document.documentElement.setAttribute('data-theme', nextDark ? 'dark' : 'light');
              document.documentElement.style.colorScheme = nextDark ? 'dark' : 'light';
              localStorage.setItem('remembermind-theme', nextDark ? 'dark' : 'light');

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
      x-init="initSidebar()"
      @resize.window.debounce.150ms="syncSidebarViewport()"
      @remembermind:theme-changed.window="darkMode = $event.detail.isDark"
      :class="{ 'is-sidebar-collapsed': sidebarCollapsed }">

    <x-banner />

    <div class="rm-app-frame relative min-h-screen bg-transparent transition-colors duration-200">
        {{-- Fondos estéticos canónicos --}}

        {{-- Overlay para móvil --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-out duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="rm-sidebar-overlay lg:hidden"
             style="display:none;"
             aria-hidden="true"></div>

        {{-- Sidebar Fijo --}}
        <x-layout.barra-lateral-sistema />

        {{-- Navbar Superior Unificado con Enfermería --}}
        <x-layout.navbar-sistema />

        <x-layout.role-preview-banner />

        @inject('rolePreview', 'App\Backend\Modulos\Identidad\Servicios\RolePreviewService')
        @php($rolePreviewActive = $rolePreview->isActive(auth()->user()))

        {{-- Área Principal de Contenido --}}
        <main data-rm-main @if($rolePreviewActive) data-preview-readonly @endif class="rm-depth-canvas relative min-w-0 min-h-[calc(100vh-60px)] pb-4 pt-4">
            <div class="mx-auto min-w-0 max-w-[1360px] space-y-4 px-4 pb-4 sm:px-5 animate-fade-in-up">
                {{ $slot }}
            </div>
        </main>
    </div>

    <x-ui.sweetalert />

    @stack('modals')

    <x-layout.livewire-runtime />
    @if($rolePreviewActive)
        <style>
            [data-preview-readonly] button,
            [data-preview-readonly] input:not([type="search"]),
            [data-preview-readonly] textarea,
            [data-preview-readonly] select {
                cursor: not-allowed !important;
                opacity: .58;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const root = document.querySelector('[data-preview-readonly]');
                if (!root) return;
                root.querySelectorAll('button, input:not([type="search"]), textarea, select').forEach((element) => {
                    element.disabled = true;
                    element.setAttribute('title', 'Disponible únicamente fuera del modo de previsualización.');
                });
                root.addEventListener('submit', (event) => event.preventDefault(), true);
            });
        </script>
    @endif
    @stack('scripts')
</body>

</html>
