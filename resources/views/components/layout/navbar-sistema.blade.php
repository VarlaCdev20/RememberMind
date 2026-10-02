@php
    $topbarPreviewRole = app(\App\Backend\Modulos\Identidad\Servicios\RolePreviewService::class)->activeRole(auth()->user());
    $isAdministracionTopbar = $topbarPreviewRole === 'ADMINISTRADOR'
        || ($topbarPreviewRole === null && auth()->user()?->hasRole('ADMINISTRADOR') && ! auth()->user()?->hasRole('SUPERADMINISTRADOR'));
    $nombreUsuario = trim((string) (auth()->user()?->nombres ?: auth()->user()?->name ?: 'Usuario'));
    $nombreUsuario = mb_convert_case(mb_strtolower($nombreUsuario, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    $rolUsuario = $isAdministracionTopbar ? 'Administración' : (auth()->user()?->getRoleNames()->first() ?? 'Usuario');
    $partesNombreUsuario = preg_split('/\s+/u', $nombreUsuario, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $inicialesUsuario = mb_strtoupper(mb_substr($partesNombreUsuario[0] ?? 'U', 0, 1).(isset($partesNombreUsuario[1]) ? mb_substr($partesNombreUsuario[1], 0, 1) : ''));
    $brandRoute = $isAdministracionTopbar && app(\App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion::class)->puedeVerRuta('admin.administracion.dashboard')
        ? 'admin.administracion.dashboard' : 'dashboard';
    $subtituloModulo = $isAdministracionTopbar ? 'Administración' : 'Panel General';
    $puedeBuscarAdministracion = app(\App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion::class)
        ->puedeVerRuta('admin.administracion.buscar');
    $rutaBuscar = $puedeBuscarAdministracion ? route('admin.administracion.buscar') : null;
    $placeholderBuscar = $isAdministracionTopbar ? 'Buscar en Administración (residentes, documentos, etc.)…' : 'Buscar en el sistema…';
@endphp

<header
    id="topbar-sistema" data-rm-topbar="system"
    class="sticky top-0 z-30 flex h-[60px] min-h-[60px] max-h-[60px] box-border w-full items-center justify-between gap-2 border-b border-[var(--rm-border)] bg-[var(--rm-topbar-bg)] px-3 sm:px-5 shadow-xs backdrop-blur-md transition-all duration-300 ease-in-out" style="height: 60px; min-height: 60px; max-height: 60px; box-sizing: border-box;"
>
    {{-- LADO IZQUIERDO: LOGO INSTITUCIONAL + REMEMBERMIND + CENTRO GERIÁTRICO --}}
    <div class="flex min-w-0 items-center gap-2 sm:gap-3.5 shrink-0">
        {{-- Botón Móvil para abrir sidebar --}}
        <button type="button" id="sidebar-mobile-trigger"
                @click="sidebarOpen = true"
                class="p-2 rounded-xl text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] lg:hidden transition-colors cursor-pointer"
                aria-label="Abrir menú lateral" aria-controls="{{ request()->routeIs('admin.enfermeria.*') ? 'sidebar-enfermeria' : 'sidebar' }}" :aria-expanded="sidebarOpen.toString()">
            <i class="ph-bold ph-list text-xl"></i>
        </button>

        {{-- Logo Oficial Institucional --}}
        <a href="{{ route($brandRoute) }}"
           class="flex items-center gap-3 transition-opacity duration-200 hover:opacity-90 min-w-0"
           title="Centro Geriátrico Los Almendros">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}"
                 alt="CENTRO GERIÁTRICO LOS ALMENDROS"
                 class="h-9 w-auto object-contain shrink-0 sm:h-9" style="max-height: 36px; width: auto;"
                 onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">

            <div class="hidden sm:block min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[var(--rm-action-primary)] font-sans">
                    CENTRO GERIÁTRICO LOS ALMENDROS
                </p>
                <p class="text-xs sm:text-sm font-black tracking-tight text-[var(--rm-text-primary)]">
                    RememberMind <span class="text-[11px] font-bold text-[var(--rm-text-secondary)] font-normal">· {{ $subtituloModulo }}</span>
                </p>
            </div>
        </a>
    </div>

    {{-- CENTRO: BARRA DE BÚSQUEDA PILL CON DISEÑO EXACTO DE ENFERMERÍA --}}
    @if($puedeBuscarAdministracion)
    <div class="hidden flex-1 max-w-md mx-2 md:block">
        <div class="rm-topbar-search" x-data="{ searchOpen: false }" @click.outside="searchOpen = false" @keydown.escape.window="searchOpen = false">
            <button type="button" class="rm-topbar-search__toggle" @click="searchOpen = !searchOpen; if (searchOpen) $nextTick(() => $refs.topbarSearch.focus())"
                    :aria-expanded="searchOpen.toString()" aria-controls="topbar-admin-search-form" aria-label="Buscar en Administración">
                <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
            </button>
            <form id="topbar-admin-search-form" action="{{ $rutaBuscar }}" method="GET"
                  class="rm-topbar-search__form" :class="{ 'is-open': searchOpen }" role="search">
                <label for="topbar-admin-search" class="sr-only">Búsqueda administrativa</label>
                <i class="ph-bold ph-magnifying-glass rm-topbar-search__icon" aria-hidden="true"></i>
                <input id="topbar-admin-search" x-ref="topbarSearch" type="search" name="q"
                       value="{{ request('q', '') }}" minlength="2" maxlength="100"
                       placeholder="{{ $placeholderBuscar }}" autocomplete="off">
                <button type="submit" class="rm-topbar-search__submit" aria-label="Enviar búsqueda">
                    <i class="ph-bold ph-arrow-right" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- LADO DERECHO: MODO OSCURO + NOTIFICACIONES + PERFIL --}}
    <div class="flex items-center gap-1 sm:gap-3 shrink-0">
        @if(auth()->user()?->hasRole('SUPERADMINISTRADOR'))
            @inject('rolePreview', 'App\Backend\Modulos\Identidad\Servicios\RolePreviewService')
            <form method="POST" action="{{ route('role-preview.store') }}" class="hidden items-center gap-2 xl:flex">
                @csrf
                <label for="role-preview-selector" class="sr-only">Ver como rol</label>
                <i class="ph-bold ph-eye text-[var(--rm-action-primary)]" aria-hidden="true"></i>
                <select id="role-preview-selector" name="role" onchange="this.form.submit()"
                        class="rm-input min-h-9 max-w-44 rounded-full py-1.5 pl-3 pr-8 text-xs font-bold"
                        aria-label="Ver como rol">
                    @foreach($rolePreview->availableRoles() as $roleKey => $roleLabel)
                        <option value="{{ $roleKey }}" @selected(($rolePreview->activeRole(auth()->user()) ?? 'SUPERADMINISTRADOR') === $roleKey)>{{ $roleLabel }}</option>
                    @endforeach
                </select>
            </form>
        @endif

        {{-- Botón Modo Claro / Oscuro --}}
        <button type="button"
                data-theme-toggle
                @click.stop="toggleDarkMode()"
                :title="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
                :aria-label="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
                class="h-10 w-10 flex items-center justify-center rounded-full bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] transition-all duration-200 active:scale-95 shadow-xs cursor-pointer">
            <i class="ph-bold text-lg transition-transform duration-200" data-theme-icon data-icon-dark="ph-bold ph-sun text-[var(--rm-action-primary)]" data-icon-light="ph-bold ph-moon text-[var(--rm-text-primary)]" :class="darkMode ? 'ph-sun text-[var(--rm-action-primary)]' : 'ph-moon text-[var(--rm-text-primary)]'"></i>
        </button>

        {{-- Campana de Notificaciones / Alertas --}}
        @if(auth()->user()?->canAny(['alertas.ver', 'alertas.gestionar', 'prescripciones.ver', 'administraciones_medicacion.ver']))
            <livewire:alertas.campana-notificaciones />
        @endif

        {{-- Perfil de Usuario con Avatar, Nombre, Rol y Dropdown --}}
        <div x-data="{ abierto: false }" @keydown.escape.window="if (abierto) { abierto = false; $refs.perfil.focus() }" class="relative">
            <button
                type="button"
                x-ref="perfil"
                @click="abierto = !abierto"
                @click.outside="abierto = false"
                :aria-expanded="abierto.toString()"
                aria-controls="menu-perfil-sistema"
                class="flex items-center gap-1 rounded-full border border-[var(--rm-border)] bg-[var(--rm-surface)] py-1 pl-1 pr-1.5 shadow-xs transition-all hover:bg-[var(--rm-surface-soft)] active:scale-95 cursor-pointer sm:gap-2.5 sm:pl-1.5 sm:pr-3"
                aria-label="Abrir menú de usuario">

                {{-- Avatar Circular --}}
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] text-xs font-[700] shadow-xs shrink-0">
                    {{ $inicialesUsuario }}
                </div>

                {{-- Nombre y Rol --}}
                <div class="hidden text-left leading-tight lg:block">
                    <p class="rm-topbar-user-name max-w-[140px] truncate text-[12px] font-bold text-[var(--rm-text-primary)]">
                        {{ $nombreUsuario }}
                    </p>
                    <p class="text-[10px] font-[700] tracking-wider text-[var(--rm-action-primary-ink)] dark:text-[var(--rm-action-primary)] uppercase font-sans">
                        {{ $rolUsuario }}
                    </p>
                </div>

                <i class="hidden ph-bold ph-caret-down text-xs text-[var(--rm-text-secondary)] transition-transform duration-200 sm:block"
                   :class="abierto ? 'rotate-180' : ''"></i>
            </button>

            {{-- Menú Dropdown Institucional --}}
            <div
                id="menu-perfil-sistema"
                x-show="abierto"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                x-cloak
                class="absolute right-0 mt-2 w-56 rounded-[var(--rm-radius-lg)] border border-[var(--rm-border-soft)] bg-[var(--rm-surface-main)] p-2 shadow-[var(--rm-shadow-floating)] z-50">

                @if(Route::has('profile.show'))
                    <a href="{{ route('profile.show') }}"
                       class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-[600] text-[var(--rm-text-primary)] transition hover:bg-[var(--rm-surface-soft)]">
                        <i class="ph-bold ph-user-circle text-base text-[var(--rm-action-primary)]"></i>
                        <span>Mi perfil</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-[700] text-[var(--rm-danger)] transition hover:bg-[var(--rm-danger-soft)] cursor-pointer">
                        <i class="ph-bold ph-sign-out text-base"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
