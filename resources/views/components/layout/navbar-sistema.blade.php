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
    class="rm-topbar"
>
    {{-- LADO IZQUIERDO: LOGO INSTITUCIONAL + REMEMBERMIND + CENTRO GERIÁTRICO --}}
    <div class="rm-topbar__identity">
        {{-- Botón Móvil para abrir sidebar --}}
        <button type="button" id="sidebar-mobile-trigger"
                @click="sidebarOpen = true"
                class="rm-topbar__action rm-topbar__hamburger"
                aria-label="Abrir menú lateral" aria-controls="{{ request()->routeIs('admin.enfermeria.*') ? 'sidebar-enfermeria' : 'sidebar' }}" :aria-expanded="sidebarOpen.toString()">
            <i class="ph-bold ph-list text-xl"></i>
        </button>


    </div>

    {{-- CENTRO: BARRA DE BÚSQUEDA PILL CON DISEÑO EXACTO DE ENFERMERÍA --}}
    @if($puedeBuscarAdministracion)
    <div class="rm-topbar__search-slot">
        <div class="rm-topbar-search" x-data="{ searchOpen: false }" @click.outside="searchOpen = false" @keydown.escape.window="if (searchOpen) { searchOpen = false; $refs.searchToggle.focus() }" x-on:livewire:navigating.window="searchOpen = false">
            <button type="button" class="rm-topbar-search__toggle" x-ref="searchToggle" @click="searchOpen = !searchOpen; if (searchOpen) $nextTick(() => $refs.topbarSearch.focus())"
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
    <div class="rm-topbar__actions">
        <x-layout.topbar-calendar />
        {{-- Botón Modo Claro / Oscuro --}}
        <x-layout.theme-toggle />


        {{-- Campana de Notificaciones / Alertas --}}
        @if(auth()->user()?->canAny(['alertas.ver', 'alertas.gestionar', 'prescripciones.ver', 'administraciones_medicacion.ver']))
            <livewire:alertas.campana-notificaciones />
        @endif

        <x-layout.topbar-settings />

        {{-- Perfil de Usuario con Avatar, Nombre, Rol y Dropdown --}}
        <div x-data="{ abierto: false }" @click.outside="abierto = false" @keydown.escape.window="if (abierto) { abierto = false; $refs.perfil.focus() }" x-on:livewire:navigating.window="abierto = false" class="relative">
            <button
                type="button"
                x-ref="perfil"
                @click="abierto = !abierto"
                :aria-expanded="abierto.toString()"
                aria-controls="menu-perfil-sistema"
                class="rm-topbar__profile"
                aria-label="Abrir menú de usuario">

                {{-- Avatar Circular --}}
                <div class="rm-topbar__avatar">
                    {{ $inicialesUsuario }}
                </div>

                {{-- Nombre y Rol --}}
                <div class="rm-topbar__profile-copy">
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
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                x-cloak
                class="absolute right-0 mt-2 w-56 rounded-[var(--rm-radius-lg)] border border-[var(--rm-border-soft)] bg-[var(--rm-surface-main)] p-2 shadow-[var(--rm-shadow-floating)] z-50">

        @if(auth()->user()?->hasRole('SUPERADMINISTRADOR'))
            @inject('rolePreview', 'App\Backend\Modulos\Identidad\Servicios\RolePreviewService')
            <form method="POST" action="{{ route('role-preview.store') }}" class="rm-topbar__role-preview">
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
