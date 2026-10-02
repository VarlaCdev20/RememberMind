{{-- TOPBAR UNIFICADO DE ENFERMERÍA (IDÉNTICO A SUPERADMIN) --}}
<header
    id="topbar-enfermeria" data-rm-topbar="nursing"
    class="sticky top-0 z-30 flex h-[60px] min-h-[60px] max-h-[60px] box-border w-full items-center justify-between gap-2 border-b border-[var(--rm-border)] bg-[var(--rm-topbar-bg)] px-3 sm:px-5 shadow-xs backdrop-blur-md transition-all duration-300 ease-in-out" style="height: 60px; min-height: 60px; max-height: 60px; box-sizing: border-box;"
    >

    {{-- LADO IZQUIERDO: LOGO INSTITUCIONAL + REMEMBERMIND + CENTRO GERIÁTRICO --}}
    <div class="flex min-w-0 items-center gap-2 sm:gap-3.5 shrink-0">
        {{-- Botón Móvil para abrir sidebar --}}
        <button type="button"
                @click="sidebarOpen = true"
                class="p-2 rounded-xl text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] lg:hidden transition-colors cursor-pointer"
                aria-label="Abrir menú lateral">
            <i class="ph-bold ph-list text-xl"></i>
        </button>

        {{-- Logo Oficial Institucional --}}
        <a href="{{ route('admin.enfermeria.dashboard') }}"
           class="flex items-center gap-3 transition-opacity duration-200 hover:opacity-90 min-w-0"
           title="Centro Geriátrico Los Almendros">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}"
                 alt="CENTRO GERIÁTRICO LOS ALMENDROS"
                 class="h-9 w-auto object-contain shrink-0 sm:h-9" style="max-height: 36px; width: auto;"
                 onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">

            <div class="hidden min-w-0 xl:block">
                <p class="text-[12px] font-[800] uppercase tracking-[0.2em] text-[var(--rm-action-primary)] font-sans leading-none mb-1">
                    RememberMind
                </p>
                <p class="text-[11px] font-[700] uppercase leading-[1.1] text-[var(--rm-text-primary)] font-sans max-w-[240px]">
                    CENTRO GERIÁTRICO LOS ALMENDROS
                </p>
            </div>
        </a>
    </div>

    @can('enfermeria.ver_pacientes_asignados')
        <div class="rm-topbar-search" x-data="{ searchOpen: false }" @click.outside="searchOpen = false" @keydown.escape.window="searchOpen = false">
            <button type="button" class="rm-topbar-search__toggle" @click="searchOpen = !searchOpen; if (searchOpen) $nextTick(() => $refs.nursingSearch.focus())"
                    :aria-expanded="searchOpen.toString()" aria-controls="nursing-topbar-search-form" aria-label="Buscar residentes">
                <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
            </button>
            <form id="nursing-topbar-search-form" action="{{ route('admin.enfermeria.pacientes') }}" method="GET"
                  class="rm-topbar-search__form" :class="{ 'is-open': searchOpen }" role="search">
                <label for="nursing-topbar-search" class="sr-only">Buscar residentes por nombre, documento o habitación</label>
                <i class="ph-bold ph-magnifying-glass rm-topbar-search__icon" aria-hidden="true"></i>
                <input id="nursing-topbar-search" x-ref="nursingSearch" type="search" name="buscar"
                       value="{{ is_string(request()->query('buscar')) ? request()->query('buscar') : '' }}" maxlength="100"
                       placeholder="Buscar residente, documento o habitación…" autocomplete="off">
                <button type="submit" class="rm-topbar-search__submit" aria-label="Enviar búsqueda">
                    <i class="ph-bold ph-arrow-right" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    @endcan

    {{-- LADO DERECHO: MODO OSCURO + NOTIFICACIONES + PERFIL --}}
    <div class="flex items-center gap-1 sm:gap-3 shrink-0">
        @if(auth()->user()?->hasRole('SUPERADMINISTRADOR'))
            @inject('rolePreview', 'App\Backend\Modulos\Identidad\Servicios\RolePreviewService')
            <form method="POST" action="{{ route('role-preview.store') }}" class="hidden items-center gap-2 xl:flex">
                @csrf
                <label for="nursing-role-preview-selector" class="sr-only">Ver como rol</label>
                <i class="ph-bold ph-eye text-[var(--rm-action-primary)]" aria-hidden="true"></i>
                <select id="nursing-role-preview-selector" name="role" onchange="this.form.submit()"
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
                @click.stop="toggleDarkMode()"
                :title="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
                :aria-label="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
                class="h-10 w-10 flex items-center justify-center rounded-full bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] transition-all duration-200 active:scale-95 shadow-xs cursor-pointer">
            <i class="ph-bold text-lg transition-transform duration-200" :class="darkMode ? 'ph-sun text-[var(--rm-action-primary)]' : 'ph-moon text-[var(--rm-text-primary)]'"></i>
        </button>

        {{-- Campana de Notificaciones / Alertas --}}
        @auth
            @if(app(\App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion::class)->puedeVerRuta('admin.enfermeria.alertas'))
            <a href="{{ route('admin.enfermeria.alertas') }}"
               class="relative h-10 w-10 flex items-center justify-center rounded-full bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] transition-all duration-200 active:scale-95 shadow-xs"
               title="Notificaciones y alertas" aria-label="Ver alertas">
                <i class="ph-bold ph-bell text-lg" aria-hidden="true"></i>
                @php
                    $conteoAlertasTop = $alertasCount ?? null;
                @endphp
                @if($conteoAlertasTop > 0)
                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[var(--rm-danger)] text-[9.5px] font-black text-[var(--rm-text-inverse)] ring-2 ring-[var(--rm-surface)]">
                        {{ $conteoAlertasTop }}
                    </span>
                @endif
            </a>
            @endif
        @endauth

        {{-- Perfil de Usuario con Avatar, Nombre, Rol y Dropdown --}}
        @php
            $nombreUsuario = trim(auth()->user()?->name ?? '') ?: 'Personal';
            $nombreUsuario = mb_convert_case(mb_strtolower($nombreUsuario, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $rolUsuario = auth()->user()?->getRoleNames()->first() ?? 'Personal';
            $partesNombreUsuario = preg_split('/\s+/u', $nombreUsuario, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $inicialesUsuario = mb_strtoupper(mb_substr($partesNombreUsuario[0] ?? 'P', 0, 1).mb_substr($partesNombreUsuario[1] ?? '', 0, 1));
        @endphp

        <div x-data="{ abierto: false }" @keydown.escape.window="if (abierto) { abierto = false; $refs.perfil.focus() }" class="relative">
            <button
                type="button"
                x-ref="perfil"
                @click="abierto = !abierto"
                @click.outside="abierto = false"
                :aria-expanded="abierto.toString()"
                aria-controls="menu-perfil-enfermeria"
                class="flex items-center gap-1 rounded-full border border-[var(--rm-border)] bg-[var(--rm-surface)] py-1 pl-1 pr-1.5 shadow-xs transition-all hover:bg-[var(--rm-surface-soft)] active:scale-95 cursor-pointer sm:gap-2.5 sm:pl-1.5 sm:pr-3"
                aria-label="Abrir menú de usuario">

                {{-- Avatar Circular --}}
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border)] text-xs font-[700] shadow-xs shrink-0">
                    {{ $inicialesUsuario }}
                </div>

                {{-- Nombre y Rol --}}
                <div class="hidden text-left leading-tight lg:block">
                    <p class="rm-topbar-user-name max-w-[140px] truncate text-[12px]">
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
                id="menu-perfil-enfermeria"
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
