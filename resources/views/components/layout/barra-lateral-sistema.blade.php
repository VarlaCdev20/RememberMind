<aside
    id="sidebar"
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-[82px]' : 'lg:w-[280px]'
    ]"
    class="sidebar-institucional fixed left-0 top-0 z-50 flex h-screen w-[280px] flex-col shadow-sidebar backdrop-blur-xl transition-all duration-300 ease-in-out"
    aria-label="Barra lateral de navegación"
>
    {{-- BOTÓN COLAPSAR --}}
    <button
        type="button"
        @click="sidebarCollapsed = !sidebarCollapsed"
        class="rm-btn-icon absolute -right-4 top-8 hidden lg:flex"
        aria-label="Contraer o expandir menú lateral"
    >
        <i
            class="ph-bold ph-caret-left text-sm transition-transform duration-300"
            :class="sidebarCollapsed ? 'rotate-180' : ''"
        ></i>
    </button>

    {{-- HEADER --}}
    <div class="shrink-0 px-3 pt-4">
        <div
            class="flex items-center rounded-xl border border-borde bg-fondo-card px-2.5 py-2 shadow-card transition-all duration-300"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-between'"
        >
            <div class="flex min-w-0 items-center gap-3">
                <img src="{{ asset('storage/imagenes/LOGO.png') }}"
                     alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                     class="h-8 w-8 shrink-0 object-contain rounded-lg transition-all duration-500 hover:scale-110">

                <div
                    x-show="!sidebarCollapsed"
                    x-transition.opacity.duration.300ms
                    class="min-w-0"
                >
                    <h2 class="max-w-[180px] text-[11px] font-extrabold uppercase leading-[1.1] text-titulo tracking-wide">
                        CENTRO GERIÁTRICO<br>JARDÍN DE LOS RECUERDOS
                    </h2>
                    <p class="truncate text-[10.5px] font-black uppercase tracking-widest text-modulo-salud">
                        RememberMind
                    </p>
                </div>
            </div>

            <button
                type="button"
                @click="sidebarOpen = false"
                class="rm-btn-icon lg:hidden"
                aria-label="Cerrar menú lateral"
            >
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
    </div>

    {{-- MENÚ --}}
    <div class="mt-3 flex-1 overflow-y-auto px-3 pb-4 [scrollbar-width:thin] [scrollbar-color:var(--color-scrollbar-thumb)_transparent]">
        @php
            $safeUrl = function (?string $route, string $fallback = 'javascript:void(0)') {
                return $route && Route::has($route) ? route($route) : $fallback;
            };

            $isDisabled = function (?string $route) {
                return ! $route || ! Route::has($route);
            };

            $isActiveItem = function (array $item) {
                if (isset($item['route']) && $item['route']) {
                    if (request()->routeIs($item['route'])) {
                        return true;
                    }
                    $base = preg_replace('/\.index$/', '.*', $item['route']);
                    if ($base !== $item['route'] && request()->routeIs($base)) {
                        return true;
                    }
                }
                return false;
            };

            $isActiveSection = function (array $section) use ($isActiveItem) {
                if (isset($section['items'])) {
                    foreach ($section['items'] as $item) {
                        if ($isActiveItem($item)) return true;
                    }
                }
                return false;
            };
        @endphp

        @inject('sidebarService', 'App\Services\Identidad\SidebarService')

        @php
            $sections = $sidebarService->getSidebar();
            $initialOpen = null;
            foreach ($sections as $idx => $sec) {
                if (!isset($sec['route']) && !empty($sec['items'])) {
                    $sActive = false;
                    foreach ($sec['items'] as $it) {
                        if ($isActiveItem($it)) {
                            $sActive = true;
                            break;
                        }
                    }
                    if ($sActive) {
                        $initialOpen = $idx;
                        break;
                    }
                    if (!empty($sec['default_expanded']) && $initialOpen === null) {
                        $initialOpen = $idx;
                    }
                }
            }
        @endphp

        <nav x-data="{ openSection: {{ $initialOpen !== null ? $initialOpen : 'null' }}, search: '' }" class="space-y-1">
            {{-- BUSCADOR RÁPIDO EN MENÚ --}}
            <div x-show="!sidebarCollapsed" class="mb-2 px-1">
                <div class="relative flex items-center">
                    <i class="ph-bold ph-magnifying-glass absolute left-2.5 text-xs text-meta pointer-events-none"></i>
                    <input
                        type="text"
                        x-model="search"
                        placeholder="BUSCAR MÓDULO..."
                        class="w-full h-8 pl-7 pr-6 text-xs rounded-xl bg-fondo-card/70 border border-borde text-titulo placeholder:text-meta/70 focus:outline-none focus:ring-1 focus:ring-boton-acento focus:border-boton-acento transition"
                    />
                    <button
                        x-show="search.length > 0"
                        @click="search = ''"
                        type="button"
                        class="absolute right-2 text-meta hover:text-titulo text-xs cursor-pointer"
                        title="Limpiar búsqueda"
                    >
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>
            </div>

            @foreach($sections as $section)
                @php
                    $isDirect = isset($section['route']) && !empty($section['route']);
                    $isSectionActive = $isDirect 
                        ? (request()->routeIs($section['route']) || (preg_replace('/\.index$/', '.*', $section['route']) !== $section['route'] && request()->routeIs(preg_replace('/\.index$/', '.*', $section['route']))))
                        : $isActiveSection($section);
                    $url = $isDirect ? $safeUrl($section['route']) : '#';
                    $hasChildren = !empty($section['items']);
                    $group = $section['group'] ?? null;
                    $prevGroup = ($loop->index > 0 && isset($sections[$loop->index - 1]['group'])) ? $sections[$loop->index - 1]['group'] : null;
                    $itemsJson = json_encode(strtolower(implode(' ', array_column($section['items'] ?? [], 'label'))));
                    $titleJson = json_encode(strtolower($section['title']));
                @endphp

                {{-- OVERLINE DE CATEGORÍA --}}
                @if(!empty($group) && $group !== $prevGroup)
                    <div class="pt-3 pb-1 px-3" x-show="!sidebarCollapsed && search === ''">
                        <span class="text-[10px] font-black uppercase tracking-[0.18em] text-apoyo/80 select-none">
                            {{ $group }}
                        </span>
                    </div>
                @endif

                @if($isDirect)
                    {{-- SECCIÓN CON LINK DIRECTO --}}
                    <div
                        class="group/section relative"
                        x-show="search === '' || {{ $titleJson }}.includes(search.toLowerCase())"
                    >
                        <a
                            wire:navigate
                            href="{{ $url }}"
                            class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-bold transition-all duration-300
                                   {{ $isSectionActive ? 'bg-fondo-card-calido text-boton-acento shadow-card ring-1 ring-borde border-l-4 border-boton-acento' : 'text-apoyo hover:bg-fondo-hover hover:text-boton-acento' }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                            title="{{ $section['title'] }}"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <i class="ph-bold {{ $section['icon'] }} text-xl shrink-0 transition-all duration-300
                                          {{ $isSectionActive ? 'text-boton-acento' : 'text-meta group-hover/section:text-boton-acento group-hover/section:rotate-3' }}"></i>
                                <span
                                    x-show="!sidebarCollapsed"
                                    x-transition.opacity.duration.300ms
                                    class="text-xs uppercase tracking-wider font-extrabold truncate"
                                >
                                    {{ $section['title'] }}
                                </span>
                            </div>

                            @if(!empty($section['badge']))
                                <span
                                    x-show="!sidebarCollapsed"
                                    class="inline-flex items-center justify-center rounded-full bg-red-500/15 px-2 py-0.5 text-[10px] font-black text-red-600 border border-red-200 dark:border-red-800 shrink-0"
                                >
                                    {{ $section['badge'] }}
                                </span>
                            @endif
                        </a>
                        
                        {{-- DIVISOR EN MODO COLAPSADO --}}
                        <div class="mx-auto h-px w-8 bg-borde my-2" x-show="sidebarCollapsed"></div>

                        {{-- TOOLTIP CUANDO ESTÁ COLAPSADO --}}
                        <div
                            x-show="sidebarCollapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 z-50 ml-3 hidden whitespace-nowrap rounded-lg bg-boton-principal px-3 py-2 text-[11px] font-extrabold uppercase tracking-wider text-boton-principalTexto shadow-panel transition-all group-hover/section:block"
                        >
                            {{ $section['title'] }}
                            @if(!empty($section['badge']))
                                <span class="ml-1.5 rounded-full bg-red-500 px-1.5 py-0.2 text-[9px] text-white">
                                    {{ $section['badge'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- SECCIÓN CON ACORDEÓN DE SUB-ITEMS --}}
                    <div
                        class="group/section relative"
                        x-show="search === '' || {{ $titleJson }}.includes(search.toLowerCase()) || {{ $itemsJson }}.includes(search.toLowerCase())"
                    >
                        {{-- HEADER DE SECCIÓN --}}
                        <button
                            type="button"
                            @click="openSection = (openSection === {{ $loop->index }}) ? null : {{ $loop->index }}"
                            class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-bold transition-all duration-300
                                   {{ $isSectionActive ? 'bg-fondo-card-calido text-boton-acento shadow-card ring-1 ring-borde border-l-4 border-boton-acento' : 'text-apoyo hover:bg-fondo-hover hover:text-boton-acento' }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <i class="ph-bold {{ $section['icon'] }} text-xl shrink-0 transition-all duration-300
                                          {{ $isSectionActive ? 'text-boton-acento' : 'text-meta group-hover/section:text-boton-acento group-hover/section:rotate-3' }}"></i>
                                <span
                                    x-show="!sidebarCollapsed"
                                    x-transition.opacity.duration.300ms
                                    class="text-xs uppercase tracking-wider font-extrabold truncate"
                                >
                                    {{ $section['title'] }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5" x-show="!sidebarCollapsed">
                                @if(!empty($section['badge']))
                                    <span class="inline-flex items-center justify-center rounded-full bg-red-500/15 px-2 py-0.5 text-[10px] font-black text-red-600 border border-red-200 dark:border-red-800 shrink-0">
                                        {{ $section['badge'] }}
                                    </span>
                                @elseif($isSectionActive)
                                    <span x-show="openSection !== {{ $loop->index }}" class="h-1.5 w-1.5 rounded-full bg-boton-acento shadow-glow shrink-0"></span>
                                @endif
                                <i
                                    class="ph-bold ph-caret-down text-[10px] transition-transform duration-500"
                                    :class="(openSection === {{ $loop->index }} || (search !== '' && {{ $itemsJson }}.includes(search.toLowerCase()))) ? 'rotate-180 text-boton-acento' : 'text-meta'"
                                ></i>
                            </div>
                        </button>

                        {{-- ITEMS DE SECCIÓN --}}
                        <div
                            x-show="(openSection === {{ $loop->index }} || (search !== '' && {{ $itemsJson }}.includes(search.toLowerCase()))) && !sidebarCollapsed"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="mt-1 space-y-0.5 pl-10"
                        >
                            @foreach($section['items'] as $item)
                                @php
                                    $active = $isActiveItem($item);
                                    $disabled = $isDisabled($item['route']);
                                    $url = $safeUrl($item['route']);
                                    $labelJson = json_encode(strtolower($item['label']));
                                @endphp

                                <a
                                    wire:navigate
                                    href="{{ $url }}"
                                    x-show="search === '' || {{ $labelJson }}.includes(search.toLowerCase()) || {{ $titleJson }}.includes(search.toLowerCase())"
                                    @if($disabled) title="Próximamente" @else title="{{ $item['label'] }}" @endif
                                    class="group/item relative flex items-center justify-between gap-2.5 pr-2 rounded-lg py-1.5 text-sm font-bold transition-all duration-300
                                           {{ $active
                                                ? 'text-boton-acento'
                                                : 'text-meta hover:text-boton-acento hover:translate-x-1'
                                           }}
                                           {{ $disabled ? 'opacity-40 cursor-not-allowed grayscale' : '' }}"
                                >
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        @if($active)
                                            <span class="absolute -left-4 top-1/2 -translate-y-1/2 h-2 w-2 rounded-full bg-boton-acento shadow-glow"></span>
                                        @endif
                                        <span class="truncate uppercase tracking-wider font-extrabold text-[11.5px]">{{ $item['label'] }}</span>
                                    </div>

                                    @if(!empty($item['badge']))
                                        <span class="inline-flex items-center justify-center rounded-full bg-red-500/15 px-2 py-0.5 text-[10px] font-black text-red-600 border border-red-200 dark:border-red-800 shrink-0">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                        
                        {{-- DIVISOR EN MODO COLAPSADO --}}
                        <div class="mx-auto h-px w-8 bg-borde my-2" x-show="sidebarCollapsed"></div>

                        {{-- FLYOUT POPOVER CUANDO ESTÁ COLAPSADO --}}
                        <div
                            x-show="sidebarCollapsed"
                            class="pointer-events-none absolute left-full top-0 z-50 ml-3 hidden min-w-[210px] rounded-xl border border-borde bg-fondo-card p-3 shadow-panel transition-all group-hover/section:block group-hover/section:pointer-events-auto"
                        >
                            <div class="flex items-center gap-2 border-b border-borde pb-2 mb-2">
                                <i class="ph-bold {{ $section['icon'] }} text-boton-acento text-base"></i>
                                <span class="text-xs font-black uppercase tracking-wider text-titulo">{{ $section['title'] }}</span>
                            </div>
                            <div class="space-y-1">
                                @foreach($section['items'] as $item)
                                    @php
                                        $active = $isActiveItem($item);
                                        $url = $safeUrl($item['route']);
                                    @endphp
                                    <a
                                        wire:navigate
                                        href="{{ $url }}"
                                        class="flex items-center justify-between rounded-lg px-2 py-1.5 text-xs font-bold transition {{ $active ? 'bg-fondo-card-calido text-boton-acento' : 'text-meta hover:bg-fondo-hover hover:text-boton-acento' }}"
                                    >
                                        <span class="truncate uppercase tracking-wider font-extrabold text-[11.5px]">{{ $item['label'] }}</span>
                                        @if(!empty($item['badge']))
                                            <span class="rounded-full bg-red-500/15 px-1.5 py-0.2 text-[9px] font-black text-red-600 border border-red-200">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    </div>

    {{-- FOOTER --}}
    <div class="shrink-0 border-t border-[var(--color-borde-suave)] p-3 space-y-2">
        @php
            $currentUser = auth()->user();
            $userRole = $currentUser ? ($currentUser->getRoleNames()->first() ?? 'USUARIO') : null;
        @endphp

        {{-- TARJETA DE ROL Y USUARIO --}}
        @if($currentUser)
            <div class="px-1" x-show="!sidebarCollapsed">
                <div class="flex items-center gap-2.5 rounded-xl border border-borde/70 bg-fondo-card/60 p-2 text-xs shadow-2xs">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-boton-acento/10 text-boton-acento font-black text-xs">
                        {{ strtoupper(substr($currentUser->nombres ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-bold uppercase text-titulo leading-tight">
                            {{ $currentUser->nombres }}
                        </p>
                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ $userRole ?? 'ACTIVO' }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <button
            type="button"
            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-black text-apoyo transition-all duration-300 hover:bg-boton-principal hover:text-boton-principalTexto active:scale-95"
            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
        >
            <i class="ph-bold ph-question text-base shrink-0 group-hover:rotate-12 transition-transform"></i>

            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>
                CENTRO DE AYUDA
            </span>
        </button>
    </div>
</aside>
