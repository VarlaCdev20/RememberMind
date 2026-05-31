<aside
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'lg:w-[82px]' : 'lg:w-[240px]'
    ]"
    class="sidebar-institucional fixed left-0 top-0 z-50 flex h-screen w-[240px] flex-col shadow-sidebar backdrop-blur-xl transition-all duration-300 ease-in-out"
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
                <img src="{{ asset('storage/images/LOGO.png') }}"
                     alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                     class="h-8 w-8 shrink-0 object-contain rounded-lg transition-all duration-500 hover:scale-110">

                <div
                    x-show="!sidebarCollapsed"
                    x-transition.opacity.duration.300ms
                    class="min-w-0"
                >
                    <h2 class="max-w-[150px] text-[10px] font-black uppercase leading-[1.05] text-titulo">
                        CENTRO GERIÁTRICO<br>JARDÍN DE LOS RECUERDOS
                    </h2>
                <p class="truncate text-[10px] font-black uppercase tracking-widest text-modulo-salud">
                        RememberMind
                    </p>
                </div>
            </div>

            <button
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
                    'items' => array_filter([
                        auth()->user()->can('usuarios.ver') ? ['label' => 'Usuarios', 'route' => 'admin.usuarios.index'] : null,
                        auth()->user()->can('roles.ver') ? ['label' => 'Roles y permisos', 'route' => 'admin.roles-permisos.index'] : null,
                        auth()->user()->can('areas.ver') ? ['label' => 'Áreas institucionales', 'route' => 'admin.areas-institucionales.index'] : null,
                        auth()->user()->can('turnos.ver') ? ['label' => 'Horarios y asignaciones', 'route' => 'admin.turnos-asignaciones.index'] : null,
                        auth()->user()->can('bitacora.ver') ? ['label' => 'Bitácora y auditoría', 'route' => 'admin.bitacora.index'] : null,
                    ]),
                ],
                [
                    'title' => 'Adultos Mayores',
                    'icon' => 'ph-users-four',
                    'items' => array_filter([
                        auth()->user()->can('adultos.ver') ? ['label' => 'Centro de Adultos Mayores', 'route' => 'admin.adultos-mayores.index'] : null,
                        auth()->user()->can('adultos.ver') ? ['label' => 'Alertas y Pendientes', 'route' => 'admin.adultos-mayores.alertas-pendientes'] : null,
                        auth()->user()->can('reportes.ver') ? ['label' => 'Reportes Institucionales', 'route' => 'admin.adultos-mayores.reporte-institucional'] : null,
                    ]),
                ],
                [
                    'title' => 'Salud y Seguimiento',
                    'icon' => 'ph-heartbeat',
                    'items' => array_filter([
                        auth()->user()->can('salud.ver') ? ['label' => 'Resumen de salud', 'route' => 'admin.salud-seguimiento.index'] : null,
                        auth()->user()->can('salud.ficha.ver') ? ['label' => 'Ficha médica', 'route' => 'admin.salud-seguimiento.ficha.index'] : null,
                        auth()->user()->can('salud.signos.ver') ? ['label' => 'Signos vitales', 'route' => 'admin.salud-seguimiento.signos.index'] : null,
                        auth()->user()->can('salud.medicacion.ver') ? ['label' => 'Medicación', 'route' => 'admin.salud-seguimiento.medicacion.index'] : null,
                        auth()->user()->can('salud.medicacion.ver') ? ['label' => 'Administración', 'route' => 'admin.salud-seguimiento.administracion.index'] : null,
                        auth()->user()->can('salud.ver') ? ['label' => 'Valoración funcional', 'route' => 'admin.salud-seguimiento.valoracion.index'] : null,
                        auth()->user()->can('salud.ver') ? ['label' => 'Evaluaciones geriátricas', 'route' => 'admin.salud-seguimiento.evaluaciones-geriatricas.index'] : null,
                        auth()->user()->can('salud.alertas.ver') ? ['label' => 'Alertas', 'route' => 'admin.salud-seguimiento.alertas'] : null,
                        auth()->user()->can('salud.reportes.ver') ? ['label' => 'Reportes', 'route' => 'admin.salud-seguimiento.reportes'] : null,
                    ]),
                ],
                [
                    'title' => 'Familia y Social',
                    'icon' => 'ph-house-line',
                    'items' => array_filter([
                        auth()->user()->can('familiares.ver') ? ['label' => 'Resumen', 'route' => 'admin.familia-social.resumen'] : null,
                        auth()->user()->can('familiares.ver') ? ['label' => 'Red de apoyo', 'route' => 'admin.familia-social.red-apoyo'] : null,
                        auth()->user()->can('familiares.ver') ? ['label' => 'Visitas', 'route' => 'admin.familia-social.visitas'] : null,
                        auth()->user()->can('familiares.ver') ? ['label' => 'Ficha social', 'route' => 'admin.familia-social.ficha-social'] : null,
                    ]),
                ],
                [
                    'title' => 'Actividades',
                    'icon' => 'ph-calendar-check',
                    'items' => [
                        ['label' => 'Actividades', 'route' => 'admin.actividades.index'],
                        ['label' => 'Tipos de actividades', 'route' => 'admin.actividades.tipos'],
                        ['label' => 'Participación', 'route' => 'admin.actividades.participacion'],
                        ['label' => 'Asistencia', 'route' => 'admin.actividades.asistencia'],
                        ['label' => 'Reportes', 'route' => 'admin.actividades.reportes'],
                    ],
                ],
                [
                    'title' => 'Voluntariado',
                    'icon' => 'ph-hand-heart',
                    'items' => [
                        ['label' => 'Resumen', 'route' => 'admin.voluntariado.index'],
                        ['label' => 'Voluntarios', 'route' => 'admin.voluntariado.voluntarios.index'],
                        ['label' => 'Disponibilidad', 'route' => 'admin.voluntariado.disponibilidad.index'],
                        ['label' => 'Asignaciones', 'route' => 'admin.voluntariado.asignaciones.index'],
                        ['label' => 'Asistencia', 'route' => 'admin.voluntariado.asistencia.index'],
                        ['label' => 'Reportes', 'route' => 'admin.voluntariado.reportes.index'],
                    ],
                ],
            ];

            // Filtrar secciones que tienen menú items y todos fueron ocultados
            $sections = array_filter($sections, function ($section) {
                if (isset($section['items'])) {
                    return count($section['items']) > 0;
                }
                return true;
            });
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
                            {{ $isSectionActive ? 'bg-fondo-card-calido text-boton-acento shadow-card ring-1 ring-borde border-l-4 border-boton-acento' : 'text-apoyo hover:bg-fondo-hover hover:text-boton-acento' }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <i class="ph-bold {{ $section['icon'] }} text-xl shrink-0 transition-all duration-300
                                    {{ $isSectionActive ? 'text-boton-acento' : 'text-meta group-hover/section:text-boton-acento group-hover/section:rotate-3' }}"></i>
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
                        <div class="mx-auto h-px w-8 bg-borde my-2" x-show="sidebarCollapsed"></div>

                        {{-- TOOLTIP CUANDO ESTÁ COLAPSADO --}}
                        <div
                            x-show="sidebarCollapsed"
                            class="pointer-events-none absolute left-full z-50 ml-4 hidden whitespace-nowrap rounded-lg bg-boton-principal px-3 py-2 text-[11px] font-black text-boton-principalTexto shadow-panel transition-all group-hover/section:block"
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
                            {{ $isSectionActive ? 'bg-fondo-card-calido text-boton-acento shadow-card ring-1 ring-borde border-l-4 border-boton-acento' : 'text-apoyo hover:bg-fondo-hover hover:text-boton-acento' }}"
                            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <i class="ph-bold {{ $section['icon'] }} text-xl shrink-0 transition-all duration-300
                                    {{ $isSectionActive ? 'text-boton-acento' : 'text-meta group-hover/section:text-boton-acento group-hover/section:rotate-3' }}"></i>
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
                                :class="expanded ? 'rotate-180 text-boton-acento' : 'text-meta'"
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
                                        ? 'text-boton-acento'
                                        : 'text-meta hover:text-boton-acento hover:translate-x-1'
                                    }}
                                    {{ $disabled ? 'opacity-40 cursor-not-allowed grayscale' : '' }}
                                    "
                                >
                                    @if($active)
                                        <span class="absolute -left-4 top-1/2 -translate-y-1/2 h-2 w-2 rounded-full bg-boton-acento shadow-glow"></span>
                                    @endif

                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                        
                        {{-- DIVISOR EN MODO COLAPSADO --}}
                        <div class="mx-auto h-px w-8 bg-borde my-2" x-show="sidebarCollapsed"></div>

                        {{-- TOOLTIP CUANDO ESTÁ COLAPSADO --}}
                        <div
                            x-show="sidebarCollapsed"
                            class="pointer-events-none absolute left-full z-50 ml-4 hidden whitespace-nowrap rounded-lg bg-boton-principal px-3 py-2 text-[11px] font-black text-boton-principalTexto shadow-panel transition-all group-hover/section:block"
                        >
                            {{ $section['title'] }}
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    </div>

    {{-- FOOTER --}}
    <div class="shrink-0 border-t border-[var(--color-borde-suave)] p-3">
        <a
            href="#"
            class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-black text-apoyo transition-all duration-300 hover:bg-boton-principal hover:text-boton-principalTexto active:scale-95"
            :class="sidebarCollapsed ? 'justify-center px-0' : ''"
        >
            <i class="ph-bold ph-question text-xl shrink-0 group-hover:rotate-12 transition-transform"></i>

            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>
                Centro de Ayuda
            </span>
        </a>
    </div>
</aside>
