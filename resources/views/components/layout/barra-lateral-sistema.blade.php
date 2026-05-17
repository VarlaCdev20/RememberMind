<aside
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-[82px]' : 'lg:w-[240px]'
    ]"
    class="fixed left-0 top-0 z-50 flex h-screen w-[240px] flex-col border-r border-[#C7B5A3]/70 bg-[#E6DDD3]/95 shadow-[18px_0_55px_rgba(47,62,92,0.12)] backdrop-blur-xl transition-all duration-300 ease-in-out"
>
    {{-- BOTÓN COLAPSAR --}}
    <button
        type="button"
        @click="sidebarCollapsed = !sidebarCollapsed"
        class="absolute -right-4 top-8 hidden h-8 w-8 items-center justify-center rounded-full border border-[#C7B5A3] bg-[#E6DDD3] text-azul-profundo shadow-md transition-all duration-300 hover:bg-terracota hover:text-white active:scale-90 lg:flex"
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
            class="flex items-center rounded-[1.35rem] bg-[#D5C7B9]/40 px-3 py-3 shadow-inner transition-all duration-300"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-between'"
        >
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-terracota text-white font-black shadow-md transition-all duration-500 hover:rotate-6">
                    <span class="text-xl">C</span>
                </div>

                <div
                    x-show="!sidebarCollapsed"
                    x-transition.opacity.duration.300ms
                    class="min-w-0"
                >
                    <h2 class="truncate text-lg font-black text-azul-profundo leading-tight">
                        Casa Amandita
                    </h2>
                    <p class="truncate text-[11px] font-black uppercase tracking-widest text-terracota">
                        RememberMind
                    </p>
                </div>
            </div>

            <button
                @click="sidebarOpen = false"
                class="flex h-8 w-8 items-center justify-center rounded-full bg-[#E6DDD3] text-azul-profundo transition hover:bg-terracota hover:text-white lg:hidden"
                aria-label="Cerrar menú lateral"
            >
                <i class="ph-bold ph-x"></i>
            </button>
        </div>
    </div>

    {{-- MENÚ --}}
    <div class="mt-5 flex-1 overflow-y-auto px-3 pb-6 [scrollbar-width:thin] [scrollbar-color:#C7B5A3_transparent]">
        @php
            $safeUrl = function (?string $route, string $fallback = '#') {
                return $route && Route::has($route) ? route($route) : $fallback;
            };

            $isDisabled = function (?string $route) {
                return ! $route || ! Route::has($route);
            };

            $isActiveItem = function (array $item) {
                if (isset($item['route']) && $item['route'] && request()->routeIs($item['route'] . '*')) {
                    return true;
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

            $sections = [
                [
                    'title' => 'Inicio',
                    'icon' => 'ph-house',
                    'route' => 'dashboard',
                ],
                [
                    'title' => 'Administración',
                    'icon' => 'ph-gear-six',
                    'items' => [
                        ['label' => 'Usuarios', 'route' => 'admin.usuarios.index'],
                        ['label' => 'Roles y permisos', 'route' => null],
                        ['label' => 'Personal institucional', 'route' => null],
                        ['label' => 'Áreas institucionales', 'route' => null],
                        ['label' => 'Turnos y asignaciones', 'route' => null],
                        ['label' => 'Bitácora y auditoría', 'route' => 'admin.bitacora.index'],
                    ],
                ],
                [
                    'title' => 'Adultos Mayores',
                    'icon' => 'ph-users-four',
                    'items' => [
                        ['label' => 'Lista general', 'route' => 'admin.adultos-mayores.index'],
                        ['label' => 'Registro', 'route' => null],
                        ['label' => 'Expedientes', 'route' => null],
                        ['label' => 'Estados institucionales', 'route' => null],
                        ['label' => 'Documentación', 'route' => null],
                        ['label' => 'Reportes', 'route' => null],
                    ],
                ],
                [
                    'title' => 'Salud y Seguimiento',
                    'icon' => 'ph-heartbeat',
                    'items' => [
                        ['label' => 'Ficha médica', 'route' => null],
                        ['label' => 'Medicación', 'route' => null],
                        ['label' => 'Adm. de Medicación', 'route' => null],
                        ['label' => 'Signos vitales', 'route' => null],
                        ['label' => 'Valoración funcional', 'route' => null],
                        ['label' => 'Observación diaria', 'route' => null],
                        ['label' => 'Atenciones e incidentes', 'route' => null],
                        ['label' => 'Evaluación cognitiva', 'route' => null],
                    ],
                ],
                [
                    'title' => 'Familia y Social',
                    'icon' => 'ph-house-line',
                    'items' => [
                        ['label' => 'Familiares', 'route' => null],
                        ['label' => 'Responsables', 'route' => null],
                        ['label' => 'Contactos de emergencia', 'route' => null],
                        ['label' => 'Autorizaciones', 'route' => null],
                        ['label' => 'Visitas', 'route' => null],
                        ['label' => 'Comunicaciones', 'route' => null],
                        ['label' => 'Ficha social', 'route' => null],
                    ],
                ],
                [
                    'title' => 'Actividades',
                    'icon' => 'ph-calendar-check',
                    'items' => [
                        ['label' => 'Actividades', 'route' => null],
                        ['label' => 'Tipos de actividades', 'route' => null],
                        ['label' => 'Participación', 'route' => null],
                        ['label' => 'Asistencia', 'route' => null],
                        ['label' => 'Reportes', 'route' => null],
                    ],
                ],
                [
                    'title' => 'Voluntariado',
                    'icon' => 'ph-hand-heart',
                    'items' => [
                        ['label' => 'Voluntarios', 'route' => null],
                        ['label' => 'Disponibilidad', 'route' => null],
                        ['label' => 'Asignaciones', 'route' => null],
                        ['label' => 'Asistencia', 'route' => null],
                        ['label' => 'Reportes', 'route' => null],
                    ],
                ],
            ];
        @endphp

        <nav class="space-y-2">
            @foreach($sections as $section)
                @php
                    $isDirect = isset($section['route']);
                    $isSectionActive = $isDirect 
                        ? (request()->routeIs($section['route'] . '*'))
                        : $isActiveSection($section);
                @endphp

                @if($isDirect)
                    <div class="group/section relative">
                        @php
                            $disabled = $isDisabled($section['route']);
                            $url = $safeUrl($section['route']);
                        @endphp
                        <a
                            href="{{ $url }}"
                            @if($disabled) title="Próximamente" @else title="{{ $section['title'] }}" @endif
                            class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-black transition-all duration-300
                            {{ $isSectionActive ? 'bg-[#D5C7B9] text-terracota shadow-sm' : 'text-azul-profundo/80 hover:bg-[#D5C7B9]/50 hover:text-terracota' }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <i class="ph-bold {{ $section['icon'] }} text-xl shrink-0 transition-all duration-300
                                    {{ $isSectionActive ? 'text-terracota' : 'text-azul-profundo/60 group-hover/section:text-terracota group-hover/section:rotate-3' }}"></i>
                                <span
                                    x-show="!sidebarCollapsed"
                                    x-transition.opacity.duration.300ms
                                    class="text-xs uppercase tracking-[0.15em] font-black"
                                >
                                    {{ $section['title'] }}
                                </span>
                            </div>
                        </a>
                        
                        {{-- DIVISOR EN MODO COLAPSADO --}}
                        <div class="mx-auto h-px w-8 bg-[#C7B5A3]/40 my-2" x-show="sidebarCollapsed"></div>

                        {{-- TOOLTIP CUANDO ESTÁ COLAPSADO --}}
                        <div
                            x-show="sidebarCollapsed"
                            class="pointer-events-none absolute left-full z-50 ml-4 hidden whitespace-nowrap rounded-lg bg-azul-profundo px-3 py-2 text-[11px] font-black text-white shadow-2xl transition-all group-hover/section:block"
                        >
                            {{ $section['title'] }}
                        </div>
                    </div>
                @else
                    <div x-data="{ expanded: {{ $isSectionActive ? 'true' : 'false' }} }" class="group/section relative">
                        {{-- HEADER DE SECCIÓN --}}
                        <button
                            @click="expanded = !expanded"
                            class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm font-black transition-all duration-300
                            {{ $isSectionActive ? 'bg-[#D5C7B9] text-terracota shadow-sm' : 'text-azul-profundo/80 hover:bg-[#D5C7B9]/50 hover:text-terracota' }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <i class="ph-bold {{ $section['icon'] }} text-xl shrink-0 transition-all duration-300
                                    {{ $isSectionActive ? 'text-terracota' : 'text-azul-profundo/60 group-hover/section:text-terracota group-hover/section:rotate-3' }}"></i>
                                <span
                                    x-show="!sidebarCollapsed"
                                    x-transition.opacity.duration.300ms
                                    class="text-xs uppercase tracking-[0.15em] font-black"
                                >
                                    {{ $section['title'] }}
                                </span>
                            </div>

                            <i
                                x-show="!sidebarCollapsed"
                                class="ph-bold ph-caret-down text-[10px] transition-transform duration-500"
                                :class="expanded ? 'rotate-180 text-terracota' : 'text-azul-profundo/40'"
                            ></i>
                        </button>

                        {{-- ITEMS DE SECCIÓN --}}
                        <div
                            x-show="expanded && !sidebarCollapsed"
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
                                @endphp

                                <a
                                    href="{{ $url }}"
                                    @if($disabled) title="Próximamente" @else title="{{ $item['label'] }}" @endif
                                    class="group/item relative flex items-center gap-2.5 rounded-lg py-2 text-sm font-black transition-all duration-300
                                    {{ $active
                                        ? 'text-terracota'
                                        : 'text-azul-profundo/60 hover:text-terracota hover:translate-x-1'
                                    }}
                                    {{ $disabled ? 'opacity-40 cursor-not-allowed grayscale' : '' }}
                                    "
                                >
                                    @if($active)
                                        <span class="absolute -left-4 top-1/2 -translate-y-1/2 h-1.5 w-1.5 rounded-full bg-terracota shadow-[0_0_8px_rgba(233,122,95,0.6)]"></span>
                                    @endif

                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                        
                        {{-- DIVISOR EN MODO COLAPSADO --}}
                        <div class="mx-auto h-px w-8 bg-[#C7B5A3]/40 my-2" x-show="sidebarCollapsed"></div>

                        {{-- TOOLTIP CUANDO ESTÁ COLAPSADO --}}
                        <div
                            x-show="sidebarCollapsed"
                            class="pointer-events-none absolute left-full z-50 ml-4 hidden whitespace-nowrap rounded-lg bg-azul-profundo px-3 py-2 text-[11px] font-black text-white shadow-2xl transition-all group-hover/section:block"
                        >
                            {{ $section['title'] }}
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    </div>

    {{-- FOOTER --}}
    <div class="shrink-0 border-t border-[#C7B5A3]/40 p-3">
        <a
            href="#"
            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-black text-azul-profundo/70 transition-all duration-300 hover:bg-terracota hover:text-white active:scale-95"
            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
        >
            <i class="ph-bold ph-question text-xl shrink-0 group-hover:rotate-12 transition-transform"></i>

            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>
                Centro de Ayuda
            </span>
        </a>
    </div>
</aside>