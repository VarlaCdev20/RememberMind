{{-- TOPBAR UNIFICADO DE ENFERMERÍA (IDÉNTICO A SUPERADMIN) --}}
<header 
    id="topbar-enfermeria"
    class="sticky top-0 z-30 flex h-[74px] min-h-[72px] max-h-[76px] w-full items-center justify-between border-b border-[#D5CABE] dark:border-[#494139] bg-[#F0E8DE] dark:bg-[#24211D] px-4 sm:px-6 shadow-xs backdrop-blur-md transition-all duration-300 ease-in-out"
    :class="sidebarCollapsed ? 'lg:pl-[96px]' : 'lg:pl-[276px]'">

    {{-- LADO IZQUIERDO: LOGO INSTITUCIONAL + REMEMBERMIND + CENTRO GERIÁTRICO --}}
    <div class="flex items-center gap-3.5 shrink-0">
        {{-- Botón Móvil para abrir sidebar --}}
        <button type="button"
                @click="sidebarOpen = true"
                class="p-2 rounded-xl text-[#304060] dark:text-[#EFE4D8] hover:bg-[#E5D8CB] dark:hover:bg-[#332F29] lg:hidden transition-colors cursor-pointer"
                aria-label="Abrir menú lateral">
            <i class="ph-bold ph-list text-xl"></i>
        </button>

        {{-- Logo Oficial Institucional --}}
        <a href="{{ route('admin.enfermeria.dashboard') }}" 
           class="flex items-center gap-3 transition-opacity duration-200 hover:opacity-90 min-w-0"
           title="Centro Geriátrico Jardín de los Recuerdos">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}"
                 alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                 class="h-10 w-auto object-contain shrink-0"
                 onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';">

            <div class="min-w-0">
                <p class="text-[12px] font-[800] uppercase tracking-[0.2em] text-[#71876A] dark:text-[#93A587] font-outfit leading-none mb-1">
                    RememberMind
                </p>
                <h1 class="text-[11px] font-[700] uppercase leading-[1.1] text-[#304060] dark:text-[#EFE4D8] font-outfit max-w-[240px]">
                    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                </h1>
            </div>
        </a>
    </div>

    {{-- BUSCADOR CENTRAL (520–620px, pill suave, fondo crema claro #F7F1EA) --}}
    <div class="hidden md:flex flex-1 justify-center max-w-[580px] mx-4">
        <div class="relative w-full">
            <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-sm text-[#677084] dark:text-[#9E9084] pointer-events-none"></i>
            <input 
                type="search" 
                placeholder="Buscar residente, habitación o diagnóstico..." 
                class="w-full h-11 pl-11 pr-4 text-xs sm:text-[13px] font-medium rounded-full bg-[#F7F1EA] dark:bg-[#2D2924] border border-[#D5CABE] dark:border-[#494139] text-[#304060] dark:text-[#EFE4D8] placeholder:text-[#677084] dark:placeholder:text-[#9E9084] focus:outline-none focus:ring-2 focus:ring-[#71876A]/20 focus:border-[#71876A] shadow-xs transition-all duration-200">
        </div>
    </div>

    {{-- LADO DERECHO: MODO OSCURO + NOTIFICACIONES + PERFIL EXACTO SUPERADMIN --}}
    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
        {{-- Botón Modo Claro / Oscuro --}}
        <button type="button"
                @click.stop="toggleDarkMode()"
                :title="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
                :aria-label="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
                class="h-10 w-10 flex items-center justify-center rounded-full bg-[#F7F1EA] hover:bg-[#EAE0D4] dark:bg-[#2D2924] dark:hover:bg-[#38332D] text-[#304060] dark:text-[#D1A25C] border border-[#D5CABE] dark:border-[#494139] transition-all duration-200 active:scale-95 shadow-xs cursor-pointer">
            <i class="ph-bold text-lg transition-transform duration-200" :class="darkMode ? 'ph-sun text-[#D1A25C]' : 'ph-moon text-[#304060]'"></i>
        </button>

        {{-- Campana de Notificaciones / Alertas --}}
        @auth
            <a href="{{ Route::has('admin.enfermeria.alertas') ? route('admin.enfermeria.alertas') : '#' }}"
               class="relative h-10 w-10 flex items-center justify-center rounded-full bg-[#F7F1EA] hover:bg-[#EAE0D4] dark:bg-[#2D2924] dark:hover:bg-[#38332D] text-[#304060] dark:text-[#EFE4D8] border border-[#D5CABE] dark:border-[#494139] transition-all duration-200 active:scale-95 shadow-xs"
               title="Notificaciones y Alertas">
                <i class="ph-bold ph-bell text-lg"></i>
                @php
                    $conteoAlertasTop = $alertasCount ?? 3;
                @endphp
                @if($conteoAlertasTop > 0)
                    <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#A35A44] dark:bg-[#C47B63] text-[9.5px] font-black text-white ring-2 ring-[#F0E8DE] dark:ring-[#24211D]">
                        {{ $conteoAlertasTop }}
                    </span>
                @endif
            </a>
        @endauth

        {{-- Perfil de Usuario con Avatar, Nombre, Rol y Dropdown --}}
        @php
            $nombreUsuario = strtoupper(auth()->user()?->name ?? 'ROSA MAMANI');
            $rolUsuario = strtoupper(auth()->user()?->getRoleNames()->first() ?? 'ENFERMEROS');
            $inicialesUsuario = strtoupper(substr(auth()->user()?->name ?? 'ROSA MAMANI', 0, 2));
        @endphp

        <div x-data="{ abierto: false }" class="relative">
            <button 
                type="button" 
                @click="abierto = !abierto" 
                @click.outside="abierto = false"
                class="flex items-center gap-2.5 rounded-full border border-[#D5CABE] dark:border-[#494139] bg-[#F7F1EA] dark:bg-[#2D2924] py-1 pl-1.5 pr-3 shadow-xs transition-all hover:bg-[#EAE0D4] dark:hover:bg-[#38332D] active:scale-95 cursor-pointer"
                aria-label="Abrir menú de usuario">
                
                {{-- Avatar Circular Navy/Sage --}}
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#304060] dark:bg-[#71876A] text-[#F7F1EA] text-xs font-[700] shadow-xs shrink-0">
                    {{ $inicialesUsuario }}
                </div>

                {{-- Nombre y Rol --}}
                <div class="hidden text-left leading-tight sm:block">
                    <p class="max-w-[140px] truncate text-[12px] font-[700] text-[#304060] dark:text-[#EFE4D8] uppercase font-outfit">
                        {{ $nombreUsuario }}
                    </p>
                    <p class="text-[10px] font-[700] tracking-wider text-[#71876A] dark:text-[#93A587] uppercase font-outfit">
                        {{ $rolUsuario }}
                    </p>
                </div>

                <i class="ph-bold ph-caret-down text-xs text-[#677084] dark:text-[#9E9084] transition-transform duration-200" 
                   :class="abierto ? 'rotate-180' : ''"></i>
            </button>

            {{-- Menú Dropdown Institucional --}}
            <div 
                x-show="abierto" 
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                style="display: none;" 
                class="absolute right-0 mt-2 w-56 rounded-[18px] border border-[#D5CABE] dark:border-[#494139] bg-[#F7F1EA] dark:bg-[#2D2924] p-2 shadow-[0_12px_30px_rgba(48,64,96,0.15)] dark:shadow-[0_12px_30px_rgba(0,0,0,0.4)] backdrop-blur-xl z-50">
                
                @if(Route::has('profile.show'))
                    <a href="{{ route('profile.show') }}" 
                       class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-[600] text-[#304060] dark:text-[#EFE4D8] transition hover:bg-[#EAE0D4] dark:hover:bg-[#38332D]">
                        <i class="ph-bold ph-user-circle text-base text-[#71876A]"></i>
                        <span>Mi perfil</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-[700] text-[#A35A44] dark:text-[#C47B63] transition hover:bg-[#F3DDDA] dark:hover:bg-[#A35A44]/20 cursor-pointer">
                        <i class="ph-bold ph-sign-out text-base"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
