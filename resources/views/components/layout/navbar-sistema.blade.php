<nav
    class="header-institucional sticky top-0 z-30 px-5 py-2.5 backdrop-blur-xl transition-all duration-300 ease-in-out"
    :class="sidebarCollapsed ? 'lg:ml-[82px]' : 'lg:ml-[240px]'"
>
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="$dispatch('toggle-sidebar')"
                class="flex h-9 w-9 items-center justify-center rounded-full bg-[#CBEFE8] text-[#0B4F46] shadow-md transition-all hover:bg-[#006B5E] hover:text-white active:translate-y-1 active:scale-90 active:shadow-inner lg:hidden"
                aria-label="Abrir menú lateral"
            >
                <i class="ph-bold ph-list text-lg"></i>
            </button>

            <img src="{{ asset('storage/images/LOGO.png') }}"
                 alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                 class="hidden sm:block h-9 w-auto object-contain shrink-0">

            <div class="min-w-0">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#006B5E]">
                    RememberMind
                </p>
                <h1 class="max-w-[230px] text-[11px] font-black uppercase leading-[1.05] text-azul-profundo sm:text-xs">
                    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                </h1>
            </div>
        </div>

        <div class="hidden w-full max-w-lg md:block">
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-azul-profundo/60"></i>
                <input
                    type="search"
                    placeholder="Buscar módulo, usuario, adulto mayor..."
                    class="w-full rounded-full border border-[var(--color-borde-suave)] bg-[#FFFDF9]/85 py-2 pl-10 pr-4 text-sm text-azul-profundo outline-none placeholder:text-azul-profundo/60 shadow-sm transition-all focus:border-[#006B5E] focus:bg-white focus:ring-2 focus:ring-[#006B5E]/15"
                >
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- Botón modo oscuro --}}
            <button
                type="button"
                data-theme-toggle
                aria-label="Cambiar modo claro u oscuro"
                class="relative flex h-9 w-9 items-center justify-center rounded-full bg-[#CBEFE8] text-[#0B4F46] shadow-sm transition-all hover:bg-[#006B5E] hover:text-white active:translate-y-1 active:scale-90 active:shadow-inner"
            >
                <i class="ph-bold ph-moon text-base" data-theme-icon data-icon-dark="ph-bold ph-moon text-base" data-icon-light="ph-bold ph-sun text-base"></i>
            </button>

            <button
                type="button"
                class="relative flex h-9 w-9 items-center justify-center rounded-full bg-[#F1E7DA] text-[#0B4F46] shadow-sm transition-all hover:bg-[#006B5E] hover:text-white active:translate-y-1 active:scale-90 active:shadow-inner"
                aria-label="Notificaciones"
            >
                <i class="ph-bold ph-bell text-base"></i>
                <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-[#F28B54] ring-2 ring-[#FAF6EF]"></span>
            </button>

            <div x-data="{ abierto:false }" class="relative">
                <button
                    type="button"
                    @click="abierto = !abierto"
                    @click.outside="abierto = false"
                    class="flex items-center gap-2 rounded-full bg-[#CBEFE8] py-1.5 pl-1.5 pr-2.5 shadow-sm transition-all hover:bg-[#97E3D5]/65 active:translate-y-1 active:scale-95 active:shadow-inner"
                    aria-label="Abrir menú de usuario"
                >
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-[#006B5E] text-xs font-black text-white shadow-sm shadow-[#006B5E]/20">
                        {{ strtoupper(substr(auth()->user()->nombres ?? auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="hidden text-left leading-tight sm:block">
                        <p class="max-w-[130px] truncate text-xs font-black text-azul-profundo">
                            {{ auth()->user()->nombres ?? auth()->user()->name ?? 'Usuario' }}
                        </p>
                        <p class="text-xs font-bold text-azul-profundo/75">
                            {{ auth()->user()->getRoleNames()->first() ?? 'Sin rol' }}
                        </p>
                    </div>

                    <i class="ph-bold ph-caret-down text-sm transition-transform" :class="abierto ? 'rotate-180' : ''"></i>
                </button>

                <div
                    x-show="abierto"
                    x-transition
                    style="display:none;"
                    class="dropdown-institucional absolute right-0 mt-3 w-64 rounded-[2rem] p-3 backdrop-blur-xl"
                >
                    <a href="{{ route('profile.show') }}"
                       class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-black text-azul-profundo transition hover:bg-[#CBEFE8] hover:text-[#006B5E] active:scale-95">
                        <i class="ph-bold ph-user-circle text-base"></i>
                        Mi perfil
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-black text-[#F28B54] transition hover:bg-[#F28B54] hover:text-white active:scale-95">
                            <i class="ph-bold ph-sign-out text-base"></i>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 md:hidden">
        <div class="relative">
            <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-azul-profundo/60"></i>
            <input
                type="search"
                placeholder="Buscar..."
                class="w-full rounded-full border border-[var(--color-borde-suave)] bg-[#FFFDF9]/85 py-2 pl-10 pr-4 text-sm text-azul-profundo outline-none placeholder:text-azul-profundo/60 shadow-sm focus:border-[#006B5E] focus:ring-2 focus:ring-[#006B5E]/15"
            >
        </div>
    </div>
</nav>
