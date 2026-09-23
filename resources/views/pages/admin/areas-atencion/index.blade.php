<x-sistema-layout>
    <div
        x-data="{
            tabActiva: 'todas',
            filtroEnfermeria: 'todas',
            search: '',
            matchesSearch(item) {
                if (!this.search || this.search.trim() === '') return true;
                const q = this.search.toLowerCase().trim();
                return (item.titulo && item.titulo.toLowerCase().includes(q)) ||
                       (item.subtitulo && item.subtitulo.toLowerCase().includes(q)) ||
                       (item.tipo && item.tipo.toLowerCase().includes(q));
            },
            areaVisible(areaId) {
                if (this.tabActiva === 'todas') return true;
                return this.tabActiva === areaId;
            }
        }"
        class="space-y-6"
    >
        {{-- ENCABEZADO Y CONTEXTO INSTITUCIONAL --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 rounded-full border border-boton-acento/30 bg-boton-acento/10 px-3 py-1 text-xs font-black text-boton-acento">
                    <i class="ph-bold ph-shield-star text-sm"></i>
                    <span>SUPERADMINISTRACIÓN • GOBERNANZA CLÍNICA Y ASISTENCIAL</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-titulo">
                    Centro de Mando de Áreas de Atención
                </h1>
                <p class="text-sm font-medium text-apoyo max-w-3xl leading-relaxed">
                    Organización y división integral de las áreas asistenciales según los roles profesionales y sus vistas operativas y de supervisión.
                </p>
            </div>

            {{-- KPIs RÁPIDOS --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <div class="rounded-2xl border border-borde bg-fondo-card px-3.5 py-2.5 shadow-2xs text-center sm:text-left">
                    <span class="text-[10px] font-black uppercase tracking-wider text-apoyo">Áreas Activas</span>
                    <p class="text-lg font-black text-titulo leading-none mt-1">7</p>
                    <span class="text-[9.5px] font-bold text-teal-600 dark:text-teal-400">100% integradas</span>
                </div>
                <div class="rounded-2xl border border-borde bg-fondo-card px-3.5 py-2.5 shadow-2xs text-center sm:text-left">
                    <span class="text-[10px] font-black uppercase tracking-wider text-apoyo">Residentes</span>
                    <p class="text-lg font-black text-titulo leading-none mt-1">{{ $totalResidentes }}</p>
                    <span class="text-[9.5px] font-bold text-emerald-600 dark:text-emerald-400">Censo total</span>
                </div>
                <div class="rounded-2xl border border-borde bg-fondo-card px-3.5 py-2.5 shadow-2xs text-center sm:text-left">
                    <span class="text-[10px] font-black uppercase tracking-wider text-apoyo">Turnos Enfermería</span>
                    <p class="text-lg font-black text-titulo leading-none mt-1">{{ $turnosEnfermeriaCount }}</p>
                    <span class="text-[9.5px] font-bold text-teal-600 dark:text-teal-400">Rotación activa</span>
                </div>
                <div class="rounded-2xl border border-borde bg-fondo-card px-3.5 py-2.5 shadow-2xs text-center sm:text-left">
                    <span class="text-[10px] font-black uppercase tracking-wider text-apoyo">Alertas Clínicas</span>
                    <p class="text-lg font-black {{ $alertasActivas > 0 ? 'text-red-600 dark:text-red-400' : 'text-titulo' }} leading-none mt-1">{{ $alertasActivas }}</p>
                    <span class="text-[9.5px] font-bold {{ $alertasActivas > 0 ? 'text-red-500' : 'text-emerald-500' }}">
                        {{ $alertasActivas > 0 ? 'Requieren atención' : 'Sin alertas' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- BARRA DE HERRAMIENTAS Y FILTRO POR ROL / ÁREA --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 rounded-2xl border border-borde bg-fondo-card/80 p-2.5 backdrop-blur-md shadow-sm">
            {{-- TABS POR ÁREA Y ROL --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 [scrollbar-width:thin]">
                <button
                    type="button"
                    @click="tabActiva = 'todas'"
                    :class="tabActiva === 'todas'
                        ? 'bg-boton-principal text-boton-principalTexto shadow-sm font-black'
                        : 'text-apoyo hover:text-titulo hover:bg-fondo-hover font-bold'"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                    <i class="ph-bold ph-squares-four text-sm"></i>
                    <span>Todas las Áreas</span>
                </button>

                <button
                    type="button"
                    @click="tabActiva = 'enfermeria'"
                    :class="tabActiva === 'enfermeria'
                        ? 'bg-teal-600 text-white shadow-sm font-black'
                        : 'text-apoyo hover:text-titulo hover:bg-fondo-hover font-bold'"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                    <i class="ph-bold ph-first-aid text-sm"></i>
                    <span>Enfermería y Supervisión</span>
                    <span class="rounded-full bg-white/20 px-1.5 py-0.2 text-[9.5px]">15</span>
                </button>

                <button
                    type="button"
                    @click="tabActiva = 'medico'"
                    :class="tabActiva === 'medico'
                        ? 'bg-emerald-600 text-white shadow-sm font-black'
                        : 'text-apoyo hover:text-titulo hover:bg-fondo-hover font-bold'"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                    <i class="ph-bold ph-stethoscope text-sm"></i>
                    <span>Medicina</span>
                </button>

                <button
                    type="button"
                    @click="tabActiva = 'psicologia'"
                    :class="tabActiva === 'psicologia'
                        ? 'bg-purple-600 text-white shadow-sm font-black'
                        : 'text-apoyo hover:text-titulo hover:bg-fondo-hover font-bold'"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                    <i class="ph-bold ph-brain text-sm"></i>
                    <span>Psicología</span>
                </button>

                <button
                    type="button"
                    @click="tabActiva = 'nutricion'"
                    :class="tabActiva === 'nutricion'
                        ? 'bg-amber-600 text-white shadow-sm font-black'
                        : 'text-apoyo hover:text-titulo hover:bg-fondo-hover font-bold'"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                    <i class="ph-bold ph-fork-knife text-sm"></i>
                    <span>Nutrición</span>
                </button>

                <button
                    type="button"
                    @click="tabActiva = 'terapia'"
                    :class="tabActiva === 'terapia'
                        ? 'bg-blue-600 text-white shadow-sm font-black'
                        : 'text-apoyo hover:text-titulo hover:bg-fondo-hover font-bold'"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                    <i class="ph-bold ph-barbell text-sm"></i>
                    <span>Terapia y Estimulación</span>
                </button>

                <button
                    type="button"
                    @click="tabActiva = 'social'"
                    :class="tabActiva === 'social'
                        ? 'bg-rose-600 text-white shadow-sm font-black'
                        : 'text-apoyo hover:text-titulo hover:bg-fondo-hover font-bold'"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                    <i class="ph-bold ph-hand-heart text-sm"></i>
                    <span>Social y Familia</span>
                </button>

                <button
                    type="button"
                    @click="tabActiva = 'administracion'"
                    :class="tabActiva === 'administracion'
                        ? 'bg-indigo-600 text-white shadow-sm font-black'
                        : 'text-apoyo hover:text-titulo hover:bg-fondo-hover font-bold'"
                    class="px-3 py-1.5 rounded-xl text-xs transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5 shrink-0"
                >
                    <i class="ph-bold ph-shield-check text-sm"></i>
                    <span>Administración</span>
                </button>
            </div>

            {{-- BUSCADOR INSTANTÁNEO --}}
            <div class="relative w-full md:w-64 shrink-0">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo text-sm"></i>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Buscar vistas o roles..."
                    class="w-full rounded-xl border border-borde bg-fondo-card pl-9 pr-3 py-1.5 text-xs text-titulo placeholder:text-apoyo/70 focus:border-boton-acento focus:ring-1 focus:ring-boton-acento"
                />
            </div>
        </div>

        {{-- CONTENIDO DE ÁREAS DE ATENCIÓN --}}
        <div class="space-y-8">

            {{-- ========================================================================= --}}
            {{-- 1. ÁREA DE ENFERMERÍA Y CUIDADOS: VISTA COMPLETA + SUPERVISIÓN ORGANIZADA --}}
            {{-- ========================================================================= --}}
            @if(isset($areas['enfermeria']))
                @php $enf = $areas['enfermeria']; @endphp
                <section
                    x-show="areaVisible('enfermeria')"
                    class="rounded-3xl border-2 border-teal-500/20 bg-fondo-card/60 p-5 sm:p-6 shadow-panel space-y-6 transition-all"
                >
                    {{-- CABECERA DEL ÁREA DE ENFERMERÍA --}}
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-borde pb-5">
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-teal-500/10 text-teal-600 dark:text-teal-400 text-2xl border border-teal-500/20 shadow-2xs">
                                <i class="ph-bold ph-first-aid"></i>
                            </span>
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-black text-titulo tracking-tight">
                                        {{ $enf['nombre'] }}
                                    </h2>
                                    <span class="rounded-full bg-teal-500/10 px-2.5 py-0.5 text-[10.5px] font-black uppercase tracking-wider text-teal-700 dark:text-teal-300 border border-teal-500/20">
                                        Rol: {{ $enf['rol_principal'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-apoyo max-w-3xl">
                                    {{ $enf['descripcion'] }}
                                </p>
                            </div>
                        </div>

                        {{-- SUB-FILTRO DE ENFERMERÍA (OPERATIVA VS SUPERVISIÓN) --}}
                        <div class="flex items-center gap-1.5 rounded-xl border border-teal-500/20 bg-teal-500/5 p-1 self-start md:self-auto">
                            <button
                                type="button"
                                @click="filtroEnfermeria = 'todas'"
                                :class="filtroEnfermeria === 'todas' ? 'bg-teal-600 text-white shadow-xs font-black' : 'text-apoyo hover:text-titulo font-bold'"
                                class="rounded-lg px-2.5 py-1 text-[11px] transition cursor-pointer"
                            >
                                Todas las Vistas (15)
                            </button>
                            <button
                                type="button"
                                @click="filtroEnfermeria = 'operativa'"
                                :class="filtroEnfermeria === 'operativa' ? 'bg-teal-600 text-white shadow-xs font-black' : 'text-apoyo hover:text-titulo font-bold'"
                                class="rounded-lg px-2.5 py-1 text-[11px] transition cursor-pointer flex items-center gap-1"
                            >
                                <i class="ph-bold ph-hand-heart"></i>
                                <span>Vista Completa (9)</span>
                            </button>
                            <button
                                type="button"
                                @click="filtroEnfermeria = 'supervision'"
                                :class="filtroEnfermeria === 'supervision' ? 'bg-teal-600 text-white shadow-xs font-black' : 'text-apoyo hover:text-titulo font-bold'"
                                class="rounded-lg px-2.5 py-1 text-[11px] transition cursor-pointer flex items-center gap-1"
                            >
                                <i class="ph-bold ph-shield-check"></i>
                                <span>Supervisión (6)</span>
                            </button>
                        </div>
                    </div>

                    {{-- BLOQUE 1: VISTA COMPLETA DE ENFERMERÍA (OPERATIVA) --}}
                    <div
                        x-show="filtroEnfermeria === 'todas' || filtroEnfermeria === 'operativa'"
                        class="space-y-3.5"
                    >
                        <div class="flex items-center justify-between border-b border-borde/60 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600 text-xs">
                                    <i class="ph-bold ph-hand-heart"></i>
                                </span>
                                <h3 class="text-sm font-black uppercase tracking-wider text-titulo">
                                    Vista Completa de Enfermería • Atención Diaria y Cuidados
                                </h3>
                            </div>
                            <span class="text-[10px] font-bold text-apoyo uppercase">
                                9 Vistas Asistenciales
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($enf['subdivisiones']['operativa']['vistas'] as $v)
                                @php
                                    $routeExists = Route::has($v['ruta']);
                                    $url = $routeExists ? route($v['ruta']) : '#';
                                @endphp
                                <div
                                    x-show="matchesSearch({{ json_encode($v) }})"
                                    class="h-full"
                                >
                                    <a
                                        wire:navigate
                                        href="{{ $url }}"
                                        class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover hover:border-teal-500/40 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5"
                                    >
                                        <div class="space-y-2.5">
                                            <div class="flex items-start justify-between gap-2">
                                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 text-base shadow-2xs group-hover:scale-105 transition-transform">
                                                    <i class="ph-bold {{ $v['icono'] }}"></i>
                                                </span>
                                                <span class="rounded-full bg-teal-500/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-teal-700 dark:text-teal-300 border border-teal-500/20">
                                                    {{ $v['tipo'] }}
                                                </span>
                                            </div>
                                            <div class="space-y-1">
                                                <h4 class="text-xs sm:text-sm font-black text-titulo group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors leading-snug">
                                                    {{ $v['titulo'] }}
                                                </h4>
                                                <p class="text-[11px] text-apoyo leading-relaxed">
                                                    {{ $v['subtitulo'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="pt-3 mt-3 border-t border-borde/60 flex items-center justify-between text-[10px]">
                                            <span class="font-bold text-apoyo uppercase tracking-wider">Acceder a vista</span>
                                            <span class="font-black text-teal-600 dark:text-teal-400 group-hover:translate-x-1 transition-transform flex items-center gap-1">
                                                <span>Abrir</span>
                                                <i class="ph-bold ph-caret-right text-[10px]"></i>
                                            </span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- BLOQUE 2: SUPERVISIÓN Y GESTIÓN DE CUIDADOS --}}
                    <div
                        x-show="filtroEnfermeria === 'todas' || filtroEnfermeria === 'supervision'"
                        class="space-y-3.5 pt-4 border-t border-borde/60"
                    >
                        <div class="flex items-center justify-between border-b border-borde/60 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 text-xs">
                                    <i class="ph-bold ph-shield-check"></i>
                                </span>
                                <h3 class="text-sm font-black uppercase tracking-wider text-titulo">
                                    Supervisión y Gestión de Enfermería • Coordinación Institucional
                                </h3>
                            </div>
                            <span class="text-[10px] font-bold text-apoyo uppercase">
                                6 Vistas de Control
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($enf['subdivisiones']['supervision']['vistas'] as $v)
                                @php
                                    $routeExists = Route::has($v['ruta']);
                                    $url = $routeExists ? route($v['ruta']) : '#';
                                @endphp
                                <div
                                    x-show="matchesSearch({{ json_encode($v) }})"
                                    class="h-full"
                                >
                                    <a
                                        wire:navigate
                                        href="{{ $url }}"
                                        class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover hover:border-emerald-500/40 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5"
                                    >
                                        <div class="space-y-2.5">
                                            <div class="flex items-start justify-between gap-2">
                                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-base shadow-2xs group-hover:scale-105 transition-transform">
                                                    <i class="ph-bold {{ $v['icono'] }}"></i>
                                                </span>
                                                <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                                                    {{ $v['tipo'] }}
                                                </span>
                                            </div>
                                            <div class="space-y-1">
                                                <h4 class="text-xs sm:text-sm font-black text-titulo group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors leading-snug">
                                                    {{ $v['titulo'] }}
                                                </h4>
                                                <p class="text-[11px] text-apoyo leading-relaxed">
                                                    {{ $v['subtitulo'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="pt-3 mt-3 border-t border-borde/60 flex items-center justify-between text-[10px]">
                                            <span class="font-bold text-apoyo uppercase tracking-wider">Supervisar</span>
                                            <span class="font-black text-emerald-600 dark:text-emerald-400 group-hover:translate-x-1 transition-transform flex items-center gap-1">
                                                <span>Abrir</span>
                                                <i class="ph-bold ph-caret-right text-[10px]"></i>
                                            </span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            {{-- ========================================================================= --}}
            {{-- 2. ÁREA MÉDICA Y GERIÁTRICA --}}
            {{-- ========================================================================= --}}
            @if(isset($areas['medico']))
                @php $med = $areas['medico']; @endphp
                <section
                    x-show="areaVisible('medico')"
                    class="rounded-3xl border border-borde bg-fondo-card/60 p-5 sm:p-6 shadow-card space-y-5 transition-all"
                >
                    <div class="flex items-start gap-3.5 border-b border-borde pb-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xl border border-emerald-500/20">
                            <i class="ph-bold {{ $med['icono'] }}"></i>
                        </span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-black text-titulo">{{ $med['nombre'] }}</h2>
                                <span class="rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[10px] font-black uppercase text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                                    Rol: {{ $med['rol_principal'] }}
                                </span>
                            </div>
                            <p class="text-xs text-apoyo">{{ $med['descripcion'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($med['subdivisiones']['clinica']['vistas'] as $v)
                            @php
                                $routeExists = Route::has($v['ruta']);
                                $url = $routeExists ? route($v['ruta']) : '#';
                            @endphp
                            <div x-show="matchesSearch({{ json_encode($v) }})" class="h-full">
                                <a wire:navigate href="{{ $url }}" class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover hover:border-emerald-500/40 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 text-sm">
                                                <i class="ph-bold {{ $v['icono'] }}"></i>
                                            </span>
                                            <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                                                {{ $v['tipo'] }}
                                            </span>
                                        </div>
                                        <div class="space-y-0.5">
                                            <h4 class="text-xs sm:text-sm font-black text-titulo group-hover:text-emerald-600 transition-colors">{{ $v['titulo'] }}</h4>
                                            <p class="text-[11px] text-apoyo">{{ $v['subtitulo'] }}</p>
                                        </div>
                                    </div>
                                    <div class="pt-2.5 mt-2 border-t border-borde/60 flex items-center justify-between text-[10px]">
                                        <span class="font-bold text-apoyo uppercase">Acceder</span>
                                        <span class="font-black text-emerald-600 flex items-center gap-1"><span>Ir</span><i class="ph-bold ph-caret-right text-[10px]"></i></span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ========================================================================= --}}
            {{-- 3. ÁREA DE PSICOLOGÍA Y SALUD MENTAL --}}
            {{-- ========================================================================= --}}
            @if(isset($areas['psicologia']))
                @php $psi = $areas['psicologia']; @endphp
                <section
                    x-show="areaVisible('psicologia')"
                    class="rounded-3xl border border-borde bg-fondo-card/60 p-5 sm:p-6 shadow-card space-y-5 transition-all"
                >
                    <div class="flex items-start gap-3.5 border-b border-borde pb-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 text-xl border border-purple-500/20">
                            <i class="ph-bold {{ $psi['icono'] }}"></i>
                        </span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-black text-titulo">{{ $psi['nombre'] }}</h2>
                                <span class="rounded-full bg-purple-500/10 px-2.5 py-0.5 text-[10px] font-black uppercase text-purple-700 dark:text-purple-300 border border-purple-500/20">
                                    Rol: {{ $psi['rol_principal'] }}
                                </span>
                            </div>
                            <p class="text-xs text-apoyo">{{ $psi['descripcion'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($psi['subdivisiones']['psicologia']['vistas'] as $v)
                            @php
                                $routeExists = Route::has($v['ruta']);
                                $url = $routeExists ? route($v['ruta']) : '#';
                            @endphp
                            <div x-show="matchesSearch({{ json_encode($v) }})" class="h-full">
                                <a wire:navigate href="{{ $url }}" class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover hover:border-purple-500/40 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 text-sm">
                                                <i class="ph-bold {{ $v['icono'] }}"></i>
                                            </span>
                                            <span class="rounded-full bg-purple-500/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-purple-700 dark:text-purple-300 border border-purple-500/20">
                                                {{ $v['tipo'] }}
                                            </span>
                                        </div>
                                        <div class="space-y-0.5">
                                            <h4 class="text-xs sm:text-sm font-black text-titulo group-hover:text-purple-600 transition-colors">{{ $v['titulo'] }}</h4>
                                            <p class="text-[11px] text-apoyo">{{ $v['subtitulo'] }}</p>
                                        </div>
                                    </div>
                                    <div class="pt-2.5 mt-2 border-t border-borde/60 flex items-center justify-between text-[10px]">
                                        <span class="font-bold text-apoyo uppercase">Acceder</span>
                                        <span class="font-black text-purple-600 flex items-center gap-1"><span>Ir</span><i class="ph-bold ph-caret-right text-[10px]"></i></span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ========================================================================= --}}
            {{-- 4. ÁREA DE NUTRICIÓN Y DIETÉTICA --}}
            {{-- ========================================================================= --}}
            @if(isset($areas['nutricion']))
                @php $nut = $areas['nutricion']; @endphp
                <section
                    x-show="areaVisible('nutricion')"
                    class="rounded-3xl border border-borde bg-fondo-card/60 p-5 sm:p-6 shadow-card space-y-5 transition-all"
                >
                    <div class="flex items-start gap-3.5 border-b border-borde pb-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xl border border-amber-500/20">
                            <i class="ph-bold {{ $nut['icono'] }}"></i>
                        </span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-black text-titulo">{{ $nut['nombre'] }}</h2>
                                <span class="rounded-full bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-black uppercase text-amber-700 dark:text-amber-300 border border-amber-500/20">
                                    Rol: {{ $nut['rol_principal'] }}
                                </span>
                            </div>
                            <p class="text-xs text-apoyo">{{ $nut['descripcion'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($nut['subdivisiones']['nutricion']['vistas'] as $v)
                            @php
                                $routeExists = Route::has($v['ruta']);
                                $url = $routeExists ? route($v['ruta']) : '#';
                            @endphp
                            <div x-show="matchesSearch({{ json_encode($v) }})" class="h-full">
                                <a wire:navigate href="{{ $url }}" class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover hover:border-amber-500/40 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 text-sm">
                                                <i class="ph-bold {{ $v['icono'] }}"></i>
                                            </span>
                                            <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300 border border-amber-500/20">
                                                {{ $v['tipo'] }}
                                            </span>
                                        </div>
                                        <div class="space-y-0.5">
                                            <h4 class="text-xs sm:text-sm font-black text-titulo group-hover:text-amber-600 transition-colors">{{ $v['titulo'] }}</h4>
                                            <p class="text-[11px] text-apoyo">{{ $v['subtitulo'] }}</p>
                                        </div>
                                    </div>
                                    <div class="pt-2.5 mt-2 border-t border-borde/60 flex items-center justify-between text-[10px]">
                                        <span class="font-bold text-apoyo uppercase">Acceder</span>
                                        <span class="font-black text-amber-600 flex items-center gap-1"><span>Ir</span><i class="ph-bold ph-caret-right text-[10px]"></i></span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ========================================================================= --}}
            {{-- 5. ÁREA DE FISIOTERAPIA, TERAPIA Y ESTIMULACIÓN --}}
            {{-- ========================================================================= --}}
            @if(isset($areas['terapia']))
                @php $ter = $areas['terapia']; @endphp
                <section
                    x-show="areaVisible('terapia')"
                    class="rounded-3xl border border-borde bg-fondo-card/60 p-5 sm:p-6 shadow-card space-y-5 transition-all"
                >
                    <div class="flex items-start gap-3.5 border-b border-borde pb-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xl border border-blue-500/20">
                            <i class="ph-bold {{ $ter['icono'] }}"></i>
                        </span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-black text-titulo">{{ $ter['nombre'] }}</h2>
                                <span class="rounded-full bg-blue-500/10 px-2.5 py-0.5 text-[10px] font-black uppercase text-blue-700 dark:text-blue-300 border border-blue-500/20">
                                    Roles: {{ $ter['rol_principal'] }} / PEDAGOGO
                                </span>
                            </div>
                            <p class="text-xs text-apoyo">{{ $ter['descripcion'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($ter['subdivisiones']['terapia']['vistas'] as $v)
                            @php
                                $routeExists = Route::has($v['ruta']);
                                $url = $routeExists ? route($v['ruta']) : '#';
                            @endphp
                            <div x-show="matchesSearch({{ json_encode($v) }})" class="h-full">
                                <a wire:navigate href="{{ $url }}" class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover hover:border-blue-500/40 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 text-sm">
                                                <i class="ph-bold {{ $v['icono'] }}"></i>
                                            </span>
                                            <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-blue-700 dark:text-blue-300 border border-blue-500/20">
                                                {{ $v['tipo'] }}
                                            </span>
                                        </div>
                                        <div class="space-y-0.5">
                                            <h4 class="text-xs sm:text-sm font-black text-titulo group-hover:text-blue-600 transition-colors">{{ $v['titulo'] }}</h4>
                                            <p class="text-[11px] text-apoyo">{{ $v['subtitulo'] }}</p>
                                        </div>
                                    </div>
                                    <div class="pt-2.5 mt-2 border-t border-borde/60 flex items-center justify-between text-[10px]">
                                        <span class="font-bold text-apoyo uppercase">Acceder</span>
                                        <span class="font-black text-blue-600 flex items-center gap-1"><span>Ir</span><i class="ph-bold ph-caret-right text-[10px]"></i></span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ========================================================================= --}}
            {{-- 6. ÁREA SOCIAL Y FAMILIAS --}}
            {{-- ========================================================================= --}}
            @if(isset($areas['social']))
                @php $soc = $areas['social']; @endphp
                <section
                    x-show="areaVisible('social')"
                    class="rounded-3xl border border-borde bg-fondo-card/60 p-5 sm:p-6 shadow-card space-y-5 transition-all"
                >
                    <div class="flex items-start gap-3.5 border-b border-borde pb-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xl border border-rose-500/20">
                            <i class="ph-bold {{ $soc['icono'] }}"></i>
                        </span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-black text-titulo">{{ $soc['nombre'] }}</h2>
                                <span class="rounded-full bg-rose-500/10 px-2.5 py-0.5 text-[10px] font-black uppercase text-rose-700 dark:text-rose-300 border border-rose-500/20">
                                    Roles: {{ $soc['rol_principal'] }} / FAMILIAR
                                </span>
                            </div>
                            <p class="text-xs text-apoyo">{{ $soc['descripcion'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($soc['subdivisiones']['social']['vistas'] as $v)
                            @php
                                $routeExists = Route::has($v['ruta']);
                                $url = $routeExists ? route($v['ruta']) : '#';
                            @endphp
                            <div x-show="matchesSearch({{ json_encode($v) }})" class="h-full">
                                <a wire:navigate href="{{ $url }}" class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover hover:border-rose-500/40 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 text-sm">
                                                <i class="ph-bold {{ $v['icono'] }}"></i>
                                            </span>
                                            <span class="rounded-full bg-rose-500/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-rose-700 dark:text-rose-300 border border-rose-500/20">
                                                {{ $v['tipo'] }}
                                            </span>
                                        </div>
                                        <div class="space-y-0.5">
                                            <h4 class="text-xs sm:text-sm font-black text-titulo group-hover:text-rose-600 transition-colors">{{ $v['titulo'] }}</h4>
                                            <p class="text-[11px] text-apoyo">{{ $v['subtitulo'] }}</p>
                                        </div>
                                    </div>
                                    <div class="pt-2.5 mt-2 border-t border-borde/60 flex items-center justify-between text-[10px]">
                                        <span class="font-bold text-apoyo uppercase">Acceder</span>
                                        <span class="font-black text-rose-600 flex items-center gap-1"><span>Ir</span><i class="ph-bold ph-caret-right text-[10px]"></i></span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ========================================================================= --}}
            {{-- 7. ÁREA DE DIRECCIÓN Y GESTIÓN INSTITUCIONAL --}}
            {{-- ========================================================================= --}}
            @if(isset($areas['administracion']))
                @php $adm = $areas['administracion']; @endphp
                <section
                    x-show="areaVisible('administracion')"
                    class="rounded-3xl border border-borde bg-fondo-card/60 p-5 sm:p-6 shadow-card space-y-5 transition-all"
                >
                    <div class="flex items-start gap-3.5 border-b border-borde pb-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xl border border-indigo-500/20">
                            <i class="ph-bold {{ $adm['icono'] }}"></i>
                        </span>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-black text-titulo">{{ $adm['nombre'] }}</h2>
                                <span class="rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-[10px] font-black uppercase text-indigo-700 dark:text-indigo-300 border border-indigo-500/20">
                                    Roles: ADMINISTRADOR / SUPERADMINISTRADOR
                                </span>
                            </div>
                            <p class="text-xs text-apoyo">{{ $adm['descripcion'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($adm['subdivisiones']['administracion']['vistas'] as $v)
                            @php
                                $routeExists = Route::has($v['ruta']);
                                $url = $routeExists ? route($v['ruta']) : '#';
                            @endphp
                            <div x-show="matchesSearch({{ json_encode($v) }})" class="h-full">
                                <a wire:navigate href="{{ $url }}" class="group relative flex flex-col justify-between h-full p-4 rounded-2xl border border-borde bg-fondo-card hover:bg-fondo-hover hover:border-indigo-500/40 hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 text-sm">
                                                <i class="ph-bold {{ $v['icono'] }}"></i>
                                            </span>
                                            <span class="rounded-full bg-indigo-500/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-300 border border-indigo-500/20">
                                                {{ $v['tipo'] }}
                                            </span>
                                        </div>
                                        <div class="space-y-0.5">
                                            <h4 class="text-xs sm:text-sm font-black text-titulo group-hover:text-indigo-600 transition-colors">{{ $v['titulo'] }}</h4>
                                            <p class="text-[11px] text-apoyo">{{ $v['subtitulo'] }}</p>
                                        </div>
                                    </div>
                                    <div class="pt-2.5 mt-2 border-t border-borde/60 flex items-center justify-between text-[10px]">
                                        <span class="font-bold text-apoyo uppercase">Acceder</span>
                                        <span class="font-black text-indigo-600 flex items-center gap-1"><span>Ir</span><i class="ph-bold ph-caret-right text-[10px]"></i></span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

        </div>

        {{-- MATRIZ DE GOBERNANZA: ROLES VS. VISTAS ASISTENCIALES --}}
        <section class="rounded-3xl border border-borde bg-fondo-card p-5 sm:p-6 shadow-panel space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-borde pb-4">
                <div>
                    <h3 class="text-base font-black text-titulo flex items-center gap-2">
                        <i class="ph-bold ph-table text-boton-acento"></i>
                        <span>Matriz de Gobernanza: Áreas de Atención vs. Roles Institucionales</span>
                    </h3>
                    <p class="text-xs text-apoyo mt-0.5">
                        Relación formal de responsabilidades, vistas operativas asignadas y niveles de supervisión por rol.
                    </p>
                </div>
                <span class="rounded-full bg-boton-acento/10 px-3 py-1 text-xs font-bold text-boton-acento self-start sm:self-auto">
                    Control de Acceso Basado en Roles (RBAC)
                </span>
            </div>

            <div class="overflow-x-auto [scrollbar-width:thin]">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-borde bg-fondo-hover/50 text-[11px] font-black uppercase tracking-wider text-apoyo">
                            <th class="py-3 px-4">Área de Atención</th>
                            <th class="py-3 px-4">Rol Principal</th>
                            <th class="py-3 px-4">Vistas Operativas Clave</th>
                            <th class="py-3 px-4">Supervisión y Control</th>
                            <th class="py-3 px-4 text-center">Acceso Rápido</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde/60 font-medium">
                        <tr class="hover:bg-fondo-hover/40 transition">
                            <td class="py-3 px-4 font-bold text-titulo flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-teal-500"></span>
                                <span>Enfermería y Cuidados</span>
                            </td>
                            <td class="py-3 px-4 text-teal-600 dark:text-teal-400 font-bold">ENFERMEROS</td>
                            <td class="py-3 px-4 text-apoyo">Mi Turno, Mis Pacientes, Agenda, Medicación, Kardex, Registros, Tareas, Valoraciones</td>
                            <td class="py-3 px-4 text-apoyo">Resumen Global, Turnos, Asignación de Camas, Pases de Turno, Alertas, Reportes</td>
                            <td class="py-3 px-4 text-center">
                                <a wire:navigate href="{{ route('admin.enfermeria.dashboard') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-teal-600 hover:underline">
                                    <span>Ver Área</span>
                                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-fondo-hover/40 transition">
                            <td class="py-3 px-4 font-bold text-titulo flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span>Medicina y Geriatría</span>
                            </td>
                            <td class="py-3 px-4 text-emerald-600 dark:text-emerald-400 font-bold">MEDICO GENERAL/GERIATRA</td>
                            <td class="py-3 px-4 text-apoyo">Ficha Médica, Valoraciones de Admisión, Signos Vitales, Medicación Prescrita</td>
                            <td class="py-3 px-4 text-apoyo">Dashboard Médico, Interconsultas, Alertas Clínicas</td>
                            <td class="py-3 px-4 text-center">
                                <a wire:navigate href="{{ route('admin.medico.dashboard') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:underline">
                                    <span>Ver Área</span>
                                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-fondo-hover/40 transition">
                            <td class="py-3 px-4 font-bold text-titulo flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-purple-500"></span>
                                <span>Psicología y Neurocognitiva</span>
                            </td>
                            <td class="py-3 px-4 text-purple-600 dark:text-purple-400 font-bold">PSICOLOGO/A</td>
                            <td class="py-3 px-4 text-apoyo">Evaluaciones Asignadas, Cognitiva (MMSE/MoCA), Afectiva (GDS-15), Funcionamiento, Red Social</td>
                            <td class="py-3 px-4 text-apoyo">Dashboard de Psicología, Alertas Conductuales</td>
                            <td class="py-3 px-4 text-center">
                                <a wire:navigate href="{{ route('admin.psicologia.dashboard') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-600 hover:underline">
                                    <span>Ver Área</span>
                                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-fondo-hover/40 transition">
                            <td class="py-3 px-4 font-bold text-titulo flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                <span>Nutrición y Dietética</span>
                            </td>
                            <td class="py-3 px-4 text-amber-600 dark:text-amber-400 font-bold">NUTRICIONISTA</td>
                            <td class="py-3 px-4 text-apoyo">Valoración Nutricional (MNA, MUST), Seguimiento Antropométrico, Curvas de Peso e IMC</td>
                            <td class="py-3 px-4 text-apoyo">Alertas Nutricionales y de Hidratación</td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-apoyo">
                                    <span>Módulo pendiente</span>
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-fondo-hover/40 transition">
                            <td class="py-3 px-4 font-bold text-titulo flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                <span>Fisioterapia y Estimulación</span>
                            </td>
                            <td class="py-3 px-4 text-blue-600 dark:text-blue-400 font-bold">FISIOTERAPEUTA / PEDAGOGO</td>
                            <td class="py-3 px-4 text-apoyo">Talleres y Actividades de Estimulación, Fisioterapia y Movilidad</td>
                            <td class="py-3 px-4 text-apoyo">Reportes de Participación, Riesgo de Caídas</td>
                            <td class="py-3 px-4 text-center">
                                <a wire:navigate href="{{ route('admin.actividades.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:underline">
                                    <span>Ver Área</span>
                                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-fondo-hover/40 transition">
                            <td class="py-3 px-4 font-bold text-titulo flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                <span>Social y Familias</span>
                            </td>
                            <td class="py-3 px-4 text-rose-600 dark:text-rose-400 font-bold">FAMILIAR</td>
                            <td class="py-3 px-4 text-apoyo">Red Familiar, Contactos y Preadmisiones de Ingreso</td>
                            <td class="py-3 px-4 text-apoyo">Coordinación de Programas y Servicios, Entrevistas Sociales</td>
                            <td class="py-3 px-4 text-center">
                                <a wire:navigate href="{{ route('admin.familia-social.resumen') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-600 hover:underline">
                                    <span>Ver Área</span>
                                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="hover:bg-fondo-hover/40 transition">
                            <td class="py-3 px-4 font-bold text-titulo flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                                <span>Dirección y Administración</span>
                            </td>
                            <td class="py-3 px-4 text-indigo-600 dark:text-indigo-400 font-bold">ADMINISTRADOR / SUPERADMIN</td>
                            <td class="py-3 px-4 text-apoyo">Expedientes de Residentes, Habitaciones y Camas, Personal, Horarios, Áreas Institucionales</td>
                            <td class="py-3 px-4 text-apoyo">Dashboard Administrativo, Usuarios, Roles y Permisos, Bitácora y Auditoría</td>
                            <td class="py-3 px-4 text-center">
                                <a wire:navigate href="{{ route('admin.administracion.dashboard') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:underline">
                                    <span>Ver Área</span>
                                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-sistema-layout>
