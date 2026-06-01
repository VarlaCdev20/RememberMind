<nav
 class="header-institucional sticky top-0 z-30 px-5 py-2.5 backdrop-blur-xl transition-all duration-300 ease-in-out"
 :class="sidebarCollapsed ? 'lg:ml-[82px]' : 'lg:ml-[240px]'"
>
 <div class="flex items-center justify-between gap-4">
 <div class="flex items-center gap-3">
 <button
 type="button"
 @click="$dispatch('toggle-sidebar')"
 class="rm-btn-icon lg:hidden"
 aria-label="Abrir menú lateral"
 >
 <i class="ph-bold ph-list text-lg"></i>
 </button>

 <img src="{{ asset('storage/images/LOGO.png') }}"
 alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
 class="hidden sm:block h-9 w-auto object-contain shrink-0">

 <div class="min-w-0">
 <p class="text-xs font-bold uppercase tracking-[0.22em] text-modulo-salud">
 RememberMind
 </p>
 <h1 class="max-w-[230px] text-[11px] font-bold uppercase leading-[1.05] text-titulo sm:text-xs">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
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

 <div class="flex items-center gap-2">
 {{-- Botón modo oscuro --}}
 <button
 type="button"
 data-theme-toggle
 aria-label="Cambiar modo claro u oscuro"
 class="rm-btn-icon"
 >
 <span data-theme-icon>🌙</span>
 </button>

 <button
 type="button"
 class="rm-btn-icon relative"
 aria-label="Notificaciones"
 >
 <i class="ph-bold ph-bell text-base"></i>
 <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-boton-acento ring-2 ring-fondo-card"></span>
 </button>

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

 <div class="mt-3 md:hidden">
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
