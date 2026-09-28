<nav
 class="header-institucional sticky top-0 z-30 flex h-[64px] min-h-[64px] items-center px-3 sm:px-6 backdrop-blur-xl transition-all duration-300 ease-in-out"
 :class="sidebarCollapsed ? 'lg:ml-[76px]' : 'lg:ml-[248px]'"
>
 <div class="flex min-w-0 items-center justify-between gap-2 sm:gap-4">
 <div class="flex min-w-0 items-center gap-2 sm:gap-3">
 <button
 type="button"
 @click="$dispatch('toggle-sidebar')"
 class="rm-btn-icon lg:hidden"
 aria-label="Abrir menú lateral"
 >
 <i class="ph-bold ph-list text-lg"></i>
 </button>

 <img src="{{ asset('storage/imagenes/LOGO.png') }}"
 alt="CENTRO GERIÁTRICO LOS ALMENDROS"
 class="block h-9 w-auto shrink-0 object-contain lg:hidden">

 <div class="hidden min-w-0">
 <p class="text-xs font-bold uppercase tracking-[0.22em] text-modulo-salud">
 RememberMind
 </p>
 <h1 class="hidden max-w-[230px] text-[11px] font-bold uppercase leading-[1.05] text-titulo min-[420px]:block sm:text-xs">
 CENTRO GERIÁTRICO LOS ALMENDROS
 </h1>
 </div>
 </div>

 <div class="hidden w-full max-w-lg md:block">
 <div class="relative">
 <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-meta"></i>
 <input
 type="search"
 placeholder="Buscar módulo, usuario, adulto mayor..."
 class="rm-input rounded-full py-2 pl-10 pr-4 text-sm shadow-sm focus:ring-[var(--color-input-ring-focus)]"
 >
 </div>
 </div>

 <div class="flex shrink-0 items-center gap-1 sm:gap-2">
 {{-- Botón modo oscuro --}}
 <button
 type="button"
 data-theme-toggle
 aria-label="Cambiar modo claro u oscuro"
 class="rm-btn-icon"
 >
 <i class="ph-bold ph-moon text-lg" data-theme-icon data-icon-dark="ph-bold ph-sun text-lg" data-icon-light="ph-bold ph-moon text-lg"></i>
 </button>

 @auth
                <livewire:alertas.campana-notificaciones />
            @endauth

 <div x-data="{ abierto:false }" class="relative">
 <button
 type="button"
 @click="abierto = !abierto"
 @click.outside="abierto = false"
 class="flex items-center gap-2 rounded-full border border-borde bg-fondo-card py-1.5 pl-1.5 pr-2.5 shadow-card transition-all hover:bg-fondo-hover active:translate-y-1 active:scale-95 active:shadow-inner"
 aria-label="Abrir menú de usuario"
 >
 <div class="flex h-7 w-7 items-center justify-center rounded-full bg-boton-principal text-xs font-bold text-boton-principalTexto shadow-card">
 {{ strtoupper(substr(auth()->user()->nombres ?? auth()->user()->name ?? 'U', 0, 1)) }}
 </div>

 <div class="hidden text-left leading-tight sm:block">
 <p class="max-w-[130px] truncate text-xs font-bold text-titulo">
 {{ auth()->user()->nombres ?? auth()->user()->name ?? 'Usuario' }}
 </p>
 <p class="text-xs font-bold text-apoyo">
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
 class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-bold text-titulo transition hover:bg-fondo-hover hover:text-boton-acento active:scale-95">
 <i class="ph-bold ph-user-circle text-base"></i>
 Mi perfil
 </a>

 <form method="POST" action="{{ route('logout') }}">
 @csrf
 <button type="submit"
 class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-bold text-boton-acento transition hover:bg-boton-acento hover:text-boton-acentoTexto active:scale-95">
 <i class="ph-bold ph-sign-out text-base"></i>
 Cerrar sesión
 </button>
 </form>
 </div>
 </div>
 </div>
 </div>

 <div class="hidden">
 <div class="relative">
 <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-meta"></i>
 <input
 type="search"
 placeholder="Buscar..."
 class="rm-input rounded-full py-2 pl-10 pr-4 text-sm shadow-sm focus:ring-[var(--color-input-ring-focus)]"
 >
 </div>
 </div>
</nav>
