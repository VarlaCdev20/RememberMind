<nav
    class="sticky top-0 z-30 border-b border-[#C7B5A3]/50 bg-[#E6DDD3]/95 px-6 py-4 shadow-[0_10px_30px_rgba(47,62,92,0.08)] backdrop-blur-xl transition-all duration-300 ease-in-out"
    :class="sidebarCollapsed ? 'lg:ml-[82px]' : 'lg:ml-[240px]'"
>
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="$dispatch('toggle-sidebar')"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-[#D5C7B9] text-azul-profundo shadow-md transition-all hover:bg-terracota hover:text-white active:translate-y-1 active:scale-90 active:shadow-inner lg:hidden"
                aria-label="Abrir menú lateral"
            >
                <i class="ph-bold ph-list text-2xl"></i>
            </button>

            <div class="hidden h-11 w-11 items-center justify-center rounded-full bg-terracota text-white shadow-md sm:flex">
                <span class="font-black">C</span>
            </div>

            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-terracota">
                    RememberMind
                </p>
                <h1 class="text-lg font-black leading-tight text-azul-profundo sm:text-2xl">
                    Panel institucional
                </h1>
            </div>
        </div>

        <div class="hidden w-full max-w-lg md:block">
            <div class="relative">
                <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-azul-profundo/60"></i>
                <input
                    type="search"
                    placeholder="Buscar módulo, usuario, adulto mayor..."
                    class="w-full rounded-full border border-[#C7B5A3] bg-white/70 py-3 pl-11 pr-4 text-sm font-bold text-azul-profundo outline-none placeholder:text-azul-profundo/60 transition-all focus:border-terracota focus:bg-white focus:ring-4 focus:ring-terracota/10 shadow-sm"
                >
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                class="relative flex h-11 w-11 items-center justify-center rounded-full bg-[#D5C7B9] text-azul-profundo shadow-md transition-all hover:bg-terracota hover:text-white active:translate-y-1 active:scale-90 active:shadow-inner"
                aria-label="Notificaciones"
            >
                <i class="ph-bold ph-bell text-xl"></i>
                <span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-terracota ring-2 ring-[#E6DDD3]"></span>
            </button>

            <div x-data="{ abierto:false }" class="relative">
                <button
                    type="button"
                    @click="abierto = !abierto"
                    @click.outside="abierto = false"
                    class="flex items-center gap-3 rounded-full bg-[#D5C7B9] py-2 pl-2 pr-3 shadow-md transition-all hover:bg-[#E6DDD3] active:translate-y-1 active:scale-95 active:shadow-inner"
                    aria-label="Abrir menú de usuario"
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-azul-profundo text-sm font-black text-white">
                        {{ strtoupper(substr(auth()->user()->nombres ?? auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="hidden text-left leading-tight sm:block">
                        <p class="max-w-[150px] truncate text-sm font-black text-azul-profundo">
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
                    class="absolute right-0 mt-3 w-64 rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/95 p-3 shadow-[0_25px_60px_rgba(47,62,92,0.25)] backdrop-blur-xl"
                >
                    <a href="{{ route('profile.show') }}"
                       class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black text-azul-profundo transition hover:bg-[#D5C7B9] hover:text-terracota active:scale-95">
                        <i class="ph-bold ph-user-circle text-xl"></i>
                        Mi perfil
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black text-terracota transition hover:bg-terracota hover:text-white active:scale-95">
                            <i class="ph-bold ph-sign-out text-xl"></i>
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
                class="w-full rounded-full border border-[#C7B5A3] bg-white/70 py-3 pl-11 pr-4 text-sm font-bold text-azul-profundo outline-none placeholder:text-azul-profundo/60 focus:border-terracota focus:ring-4 focus:ring-terracota/10 shadow-sm"
            >
        </div>
    </div>
</nav>