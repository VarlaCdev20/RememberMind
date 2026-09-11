<div class="space-y-6 pb-12 font-sans">
    {{-- Encabezado Institucional Flotante --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl bg-white dark:bg-slate-800 p-6 shadow-xl border border-slate-100 dark:border-slate-700/80">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-600 text-white shadow-lg shadow-rose-600/30">
                    <i class="ph ph-shield-warning text-2xl"></i>
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold font-outfit text-slate-800 dark:text-white tracking-tight">
                    Centro de Alertas Clínicas y Cuidados
                </h1>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-2xl pl-1">
                Monitoreo continuo de Alertas clínicas, signos vitales, administración de fármacos y eventos asistenciales en Jardín de los Recuerdos.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 sm:self-start">
            @canany(['alertas.crear','alertas.gestionar','salud.alertas.gestionar'])
                <button type="button"
                    wire:click="abrirCrear"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-600/30 hover:shadow-xl hover:shadow-rose-600/40 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                    <i class="ph ph-plus-circle text-lg"></i>
                    <span>Registrar alerta</span>
                </button>

                <button type="button"
                    wire:click="detectarAlertas"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer disabled:opacity-60">
                    <span wire:loading.remove wire:target="detectarAlertas" class="flex items-center gap-2">
                        <i class="ph ph-waveform text-lg text-rose-600"></i>
                        <span>Detectar pendientes</span>
                    </span>
                    <span wire:loading wire:target="detectarAlertas" class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-rose-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Escaneando...</span>
                    </span>
                </button>
            @endcanany

            <button type="button"
                wire:click="$refresh"
                title="Actualizar datos"
                class="inline-flex items-center justify-center h-10 w-10 rounded-xl bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-200 shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                <i class="ph ph-arrows-clockwise text-lg" wire:loading.class="animate-spin" wire:target="$refresh"></i>
            </button>
        </div>
    </header>

    {{-- Notificación Flash --}}
    @if(session('mensaje'))
        <div role="status"
            x-data="{ show: true }"
            x-show="show"
            x-transition
            class="flex items-center justify-between gap-3 p-4 rounded-xl bg-emerald-600 text-white shadow-xl shadow-emerald-600/25">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-white">
                    <i class="ph ph-check-circle text-xl"></i>
                </span>
                <span class="text-sm font-bold">{{ session('mensaje') }}</span>
            </div>
            <button type="button" @click="show = false" class="text-white/80 hover:text-white transition cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
    @endif

    <x-validation-errors class="mb-2" />

    {{-- TARJETAS KPI FLOTANTES CON COLORES ENTEROS Y VIBRANTES --}}
    <section class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Card 1: Total Alertas (Color entero: Pizarra Oscuro) --}}
        <div wire:click="limpiarFiltros"
            class="group rounded-2xl bg-slate-800 text-white p-5 shadow-xl shadow-slate-900/25 hover:shadow-2xl hover:shadow-slate-900/40 hover:-translate-y-1.5 transition-all duration-300 cursor-pointer relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Total Registro</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 text-white group-hover:scale-110 transition-transform">
                    <i class="ph ph-bell text-xl"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold font-outfit">{{ $conteos['total'] ?? 0 }}</span>
            </div>
            <div class="mt-2 text-xs text-slate-300 flex items-center justify-between font-medium">
                <span>Alertas en sistema</span>
                <span class="underline underline-offset-2">Ver todas</span>
            </div>
        </div>

        {{-- Card 2: Críticas y Altas (Color entero: Carmesíí Intenso) --}}
        <div wire:click="setFiltroRapido('filtroNivel', 'CRITICO')"
            class="group rounded-2xl bg-rose-600 text-white p-5 shadow-xl shadow-rose-600/35 hover:shadow-2xl hover:shadow-rose-600/50 hover:-translate-y-1.5 transition-all duration-300 cursor-pointer relative overflow-hidden {{ $filtroNivel === 'CRITICO' ? 'ring-4 ring-rose-300' : '' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-80"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-100">Críticas y Altas</span>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 text-white group-hover:scale-110 transition-transform">
                    <i class="ph ph-warning-octagon text-xl"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold font-outfit">
                    {{ ($conteos['criticas'] ?? 0) + ($conteos['altas'] ?? 0) }}
                </span>
            </div>
            <div class="mt-2 text-xs text-rose-100 flex items-center justify-between font-medium">
                <span>{{ $conteos['criticas'] ?? 0 }} críticas</span>
                <span>{{ $conteos['altas'] ?? 0 }} altas</span>
            </div>
        </div>

        {{-- Card 3: Por Atender (Color entero: ÁÁmbar Oro) --}}
        <div wire:click="setFiltroRapido('filtroEstado', 'ABIERTA')"
            class="group rounded-2xl bg-amber-500 text-white p-5 shadow-xl shadow-amber-500/35 hover:shadow-2xl hover:shadow-amber-500/50 hover:-translate-y-1.5 transition-all duration-300 cursor-pointer relative overflow-hidden {{ $filtroEstado === 'ABIERTA' ? 'ring-4 ring-amber-300' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-100">Por Atender</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 text-white group-hover:scale-110 transition-transform">
                    <i class="ph ph-clock-countdown text-xl"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold font-outfit">{{ $conteos['abiertas'] ?? 0 }}</span>
            </div>
            <div class="mt-2 text-xs text-amber-100 flex items-center gap-1.5 font-medium">
                <i class="ph ph-hourglass-high"></i>
                <span>Abiertas sin atención</span>
            </div>
        </div>

        {{-- Card 4: En Atención (Color entero: Azul Real) --}}
        <div wire:click="setFiltroRapido('filtroEstado', 'EN_ATENCION')"
            class="group rounded-2xl bg-blue-600 text-white p-5 shadow-xl shadow-blue-600/35 hover:shadow-2xl hover:shadow-blue-600/50 hover:-translate-y-1.5 transition-all duration-300 cursor-pointer relative overflow-hidden {{ $filtroEstado === 'EN_ATENCION' ? 'ring-4 ring-blue-300' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-100">En Atención</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 text-white group-hover:scale-110 transition-transform">
                    <i class="ph ph-first-aid text-xl"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold font-outfit">{{ $conteos['en_atencion'] ?? 0 }}</span>
            </div>
            <div class="mt-2 text-xs text-blue-100 flex items-center gap-1.5 font-medium">
                <i class="ph ph-activity"></i>
                <span>Protocolo en curso</span>
            </div>
        </div>

        {{-- Card 5: Resueltas (Color entero: Esmeralda Verde) --}}
        <div wire:click="setFiltroRapido('filtroEstado', 'CERRADA')"
            class="group rounded-2xl bg-emerald-600 text-white p-5 shadow-xl shadow-emerald-600/35 hover:shadow-2xl hover:shadow-emerald-600/50 hover:-translate-y-1.5 transition-all duration-300 cursor-pointer relative overflow-hidden col-span-2 sm:col-span-1 {{ $filtroEstado === 'CERRADA' ? 'ring-4 ring-emerald-300' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-100">Resueltas</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20 text-white group-hover:scale-110 transition-transform">
                    <i class="ph ph-check-circle text-xl"></i>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold font-outfit">{{ $conteos['cerradas'] ?? 0 }}</span>
            </div>
            <div class="mt-2 text-xs text-emerald-100 flex items-center gap-1.5 font-medium">
                <i class="ph ph-archive-box"></i>
                <span>Cerradas y archivadas</span>
            </div>
        </div>
    </section>
    {{-- SECCIÓN DE GRÁFICOS DINÁMICOS FLOTANTES CON MOVIMIENTO --}}
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-5" wire:ignore x-data="alertasDashboardComponent(@js($chartData), @js($conteos))" x-init="initDashboard()">
        {{-- Gráfico 1: Severidad y Triaje Clínico --}}
        <div class="lg:col-span-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/80 p-5 shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                    <h2 class="font-bold font-outfit text-base text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded-full bg-rose-600 shadow-sm"></span>
                        Nivel de Severidad
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Priorización de atención clínica</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-600 text-white shadow-sm">
                    Triaje
                </span>
            </div>

            <div class="relative flex items-center justify-center my-2 h-56 w-full">
                <canvas id="chartAlertasSeveridad"></canvas>
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-2xl font-extrabold font-outfit text-slate-800 dark:text-white" x-text="totalSeveridad">0</span>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Alertas</span>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-1.5 pt-3 border-t border-slate-100 dark:border-slate-700/80 text-center text-xs">
                <div class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/30">
                    <span class="block text-[10px] font-bold text-rose-700 dark:text-rose-400 uppercase">Crítico</span>
                    <span class="font-extrabold text-sm text-rose-600" x-text="chartData.niveles?.CRITICO ?? 0">0</span>
                </div>
                <div class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/30">
                    <span class="block text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase">Alto</span>
                    <span class="font-extrabold text-sm text-amber-600" x-text="chartData.niveles?.ALTO ?? 0">0</span>
                </div>
                <div class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/30">
                    <span class="block text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase">Medio</span>
                    <span class="font-extrabold text-sm text-blue-600" x-text="chartData.niveles?.MEDIO ?? 0">0</span>
                </div>
                <div class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30">
                    <span class="block text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase">Bajo</span>
                    <span class="font-extrabold text-sm text-emerald-600" x-text="chartData.niveles?.BAJO ?? 0">0</span>
                </div>
            </div>
        </div>

        {{-- Gráfico 2: Orígenes y Canales Asistenciales --}}
        <div class="lg:col-span-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/80 p-5 shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                    <h2 class="font-bold font-outfit text-base text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded-full bg-blue-600 shadow-sm"></span>
                        Canales y Orígenes Clínicos
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Distribución por área de procedencia</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-600 text-white shadow-sm">
                    Canales
                </span>
            </div>

            <div class="relative my-2 h-56 w-full">
                <canvas id="chartAlertasOrigen"></canvas>
            </div>

            <p class="text-[11px] text-slate-400 text-center pt-2 border-t border-slate-100 dark:border-slate-700/80 font-medium">
                Pasa el cursor sobre las barras para ver el detalle de cada origen.
            </p>
        </div>

        {{-- Gráfico 3: Ciclo de Vida y Tasa de Resolución --}}
        <div class="lg:col-span-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/80 p-5 shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                    <h2 class="font-bold font-outfit text-base text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="inline-block h-3 w-3 rounded-full bg-emerald-600 shadow-sm"></span>
                        Ciclo de Vida
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Estado de atención actual</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-sm">
                    Resolución
                </span>
            </div>

            <div class="relative flex items-center justify-center my-2 h-56 w-full">
                <canvas id="chartAlertasEstado"></canvas>
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-2xl font-extrabold font-outfit text-emerald-600 dark:text-emerald-400" x-text="porcentajeResolucion + '%'">0%</span>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Eficacia</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-1.5 pt-3 border-t border-slate-100 dark:border-slate-700/80 text-center text-xs">
                <div class="p-1 rounded-lg bg-amber-50 dark:bg-amber-950/30">
                    <span class="block text-[10px] font-bold text-amber-700 dark:text-amber-400">Abiertas</span>
                    <span class="font-extrabold text-xs text-amber-600" x-text="chartData.estados?.ABIERTA ?? 0">0</span>
                </div>
                <div class="p-1 rounded-lg bg-blue-50 dark:bg-blue-950/30">
                    <span class="block text-[10px] font-bold text-blue-700 dark:text-blue-400">En Curso</span>
                    <span class="font-extrabold text-xs text-blue-600" x-text="chartData.estados?.EN_ATENCION ?? 0">0</span>
                </div>
                <div class="p-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/30">
                    <span class="block text-[10px] font-bold text-emerald-700 dark:text-emerald-400">Resueltas</span>
                    <span class="font-extrabold text-xs text-emerald-600" x-text="chartData.estados?.CERRADA ?? 0">0</span>
                </div>
            </div>
        </div>
    </section>
    {{-- BARRA DE FILTROS Y BÚSQUEDA CLÍNICA --}}
    <section class="rounded-2xl bg-white dark:bg-slate-800 p-5 shadow-xl border border-slate-100 dark:border-slate-700/80 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            {{-- Búsqueda textual --}}
            <div class="lg:col-span-4 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <i class="ph ph-magnifying-glass text-lg"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por residente, diagnóstico o motivo..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none" />
            </div>

            {{-- Filtro Estado --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroEstado"
                    class="w-full py-2.5 px-3 rounded-xl text-sm border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none">
                    <option value="">Todos los estados</option>
                    <option value="ABIERTA">Abierta / Pendiente</option>
                    <option value="EN_ATENCION">En Atención</option>
                    <option value="CERRADA">Cerrada / Resuelta</option>
                </select>
            </div>

            {{-- Filtro Nivel --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroNivel"
                    class="w-full py-2.5 px-3 rounded-xl text-sm border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none">
                    <option value="">Todos los niveles</option>
                    <option value="CRITICO">Crítico</option>
                    <option value="ALTO">Alto</option>
                    <option value="MEDIO">Medio</option>
                    <option value="BAJO">Bajo</option>
                </select>
            </div>

            {{-- Filtro Origen --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroOrigen"
                    class="w-full py-2.5 px-3 rounded-xl text-sm border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none">
                    <option value="">Todos los orígenes</option>
                    <option value="SIGNOS">Signos Vitales</option>
                    <option value="MEDICACION">Medicación</option>
                    <option value="INCIDENTE">Incidente Asistencial</option>
                    <option value="PLAN">Plan de Cuidado</option>
                    <option value="SEGUIMIENTO">Seguimiento Clínico</option>
                    <option value="SOLICITUD_MEDICA">Solicitud Médica</option>
                    <option value="MANUAL">Registro Manual</option>
                    <option value="FICHA">Ficha Clínica</option>
                    <option value="VALORACION">Valoración</option>
                </select>
            </div>

            {{-- Filtro Residente --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroAdulto"
                    class="w-full py-2.5 px-3 rounded-xl text-sm border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none">
                    <option value="">Todos los residentes</option>
                    @foreach($adultos as $ad)
                        <option value="{{ $ad->cod_am }}">
                            {{ $ad->ap_paterno }} {{ $ad->nombres }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Indicador de filtros activos y reset rápido --}}
        @if($search || $filtroEstado || $filtroNivel || $filtroOrigen || $filtroAdulto)
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700/60 text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">
                    Filtros activos aplicados a la vista.
                </span>
                <button type="button"
                    wire:click="limpiarFiltros"
                    class="inline-flex items-center gap-1 font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400 cursor-pointer">
                    <i class="ph ph-trash"></i>
                    <span>Restablecer todos los filtros</span>
                </button>
            </div>
        @endif
    </section>

    {{-- TABLA FLOTANTE DE ALERTAS CLÍNICAS --}}
    <section class="rounded-2xl bg-white dark:bg-slate-800 shadow-xl border border-slate-100 dark:border-slate-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-200/80 dark:border-slate-700 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="py-4 px-5">Residente y Ubicación</th>
                        <th class="py-4 px-4">Clasificación y Nivel</th>
                        <th class="py-4 px-4">Motivo y Diagnóstico Clínico</th>
                        <th class="py-4 px-4">Responsable / Turno</th>
                        <th class="py-4 px-4 text-center">Estado</th>
                        <th class="py-4 px-5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                    @forelse($alertas as $alerta)
                        @php
                            $nivelBadge = match($alerta->nivel) {
                                'CRITICO' => 'bg-rose-600 text-white shadow-sm shadow-rose-600/30',
                                'ALTO' => 'bg-amber-500 text-white shadow-sm shadow-amber-500/30',
                                'MEDIO' => 'bg-blue-600 text-white shadow-sm shadow-blue-600/30',
                                'BAJO' => 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30',
                                default => 'bg-slate-500 text-white'
                            };
                            $nivelTexto = match($alerta->nivel) {
                                'CRITICO' => 'Crítico',
                                'ALTO' => 'Alto',
                                'MEDIO' => 'Medio',
                                'BAJO' => 'Bajo',
                                default => $alerta->nivel
                            };
                            $estadoBadge = match($alerta->estado) {
                                'ABIERTA' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-700',
                                'EN_ATENCION' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300 dark:border-blue-700',
                                'CERRADA' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700',
                                default => 'bg-slate-100 text-slate-800'
                            };
                            $estadoTexto = match($alerta->estado) {
                                'ABIERTA' => 'Abierta',
                                'EN_ATENCION' => 'En Atención',
                                'CERRADA' => 'Resuelta',
                                default => $alerta->estado
                            };
                            $origenBadge = match($alerta->origen) {
                                'SIGNOS' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                                'MEDICACION' => 'bg-estado-infoBg text-estado-info border-estado-infoBorde',
                                'INCIDENTE' => 'bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-300 border-orange-200 dark:border-orange-800',
                                'PLAN' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                'SEGUIMIENTO' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                                'SOLICITUD_MEDICA' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/40 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
                                default => 'bg-slate-50 text-slate-700 dark:bg-slate-900 dark:text-slate-300 border-slate-200 dark:border-slate-700'
                            };
                            $origenTexto = match($alerta->origen) {
                                'SIGNOS' => 'Signos Vitales',
                                'MEDICACION' => 'Medicación',
                                'INCIDENTE' => 'Incidente Asistencial',
                                'PLAN' => 'Plan de Cuidado',
                                'SEGUIMIENTO' => 'Seguimiento Clínico',
                                'SOLICITUD_MEDICA' => 'Solicitud Médica',
                                'MANUAL' => 'Registro Manual',
                                'FICHA' => 'Ficha Clínica',
                                'VALORACION' => 'Valoración',
                                default => $alerta->origen
                            };
                        @endphp
                        <tr wire:key="alerta-{{ $alerta->cod_alerta }}" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            {{-- Residente y Ubicación --}}
                            <td class="py-3.5 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl font-extrabold text-sm {{ $alerta->nivel === 'CRITICO' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                        {{ substr($alerta->adultoMayor?->nombres ?? 'A', 0, 1) }}{{ substr($alerta->adultoMayor?->ap_paterno ?? 'M', 0, 1) }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <button type="button"
                                        wire:click="verUbicacion('{{ $alerta->cod_am }}')"
                                        title="Ver ubicación y ficha en barra lateral"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-fondo-app hover:bg-boton-acento text-boton-acento hover:text-white border border-borde transition shadow-sm cursor-pointer active:scale-95">
                                        <i class="ph-bold ph-bed text-base"></i>
                                    </button>
                                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                            <span class="inline-flex items-center gap-1">
                                                <i class="ph ph-door text-slate-400"></i>
                                                {{ $alerta->adultoMayor?->habitacion?->nombre ?? ($alerta->adultoMayor?->cod_habitacion ? 'Hab. '.$alerta->adultoMayor->cod_habitacion : 'Sin habitación') }}
                                            </span>
                                            <span>·</span>
                                            <span class="inline-flex items-center gap-1">
                                                <i class="ph ph-bed text-slate-400"></i>
                                                {{ $alerta->adultoMayor?->cama?->codigo ?? ($alerta->adultoMayor?->cod_cama ? 'Cama '.$alerta->adultoMayor->cod_cama : 'Sin cama') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Clasificación y Nivel --}}
                            <td class="py-3.5 px-4">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-lg text-xs font-extrabold {{ $nivelBadge }}">
                                            {{ $nivelTexto }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold border {{ $origenBadge }}">
                                            {{ $origenTexto }}
                                        </span>
                                    </div>
                                    <div class="font-mono text-xs font-bold text-slate-600 dark:text-slate-300">
                                        {{ $alerta->tipo_alerta }}
                                    </div>
                                </div>
                            </td>

                            {{-- Motivo y Diagnóstico Clínico --}}
                            <td class="py-3.5 px-4 max-w-xs">
                                <p class="text-xs text-slate-700 dark:text-slate-200 line-clamp-2 leading-relaxed" title="{{ $alerta->motivo }}">
                                    {{ $alerta->motivo }}
                                </p>
                                <div class="mt-1 flex items-center gap-2 text-[11px] text-slate-400 font-medium">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ph ph-clock"></i>
                                        {{ $alerta->created_at?->diffForHumans() }}
                                    </span>
                                    @if($alerta->acciones_count > 0 || $alerta->acciones->count() > 0)
                                        <span>·</span>
                                        <span class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 font-bold">
                                            <i class="ph ph-chat-circle-dots"></i>
                                            {{ $alerta->acciones->count() }} notas
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Responsable / Turno --}}
                            <td class="py-3.5 px-4">
                                <div class="space-y-0.5 text-xs">
                                    <span class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                                        <i class="ph ph-user-circle text-base text-slate-400"></i>
                                        {{ $alerta->responsable?->name ?? 'Sin asignar' }}
                                    </span>
                                    <span class="text-slate-400 text-[11px] flex items-center gap-1">
                                        <i class="ph ph-sun-horizon"></i>
                                        {{ $alerta->turno ? 'Turno '.$alerta->turno->nombre : 'Horario continuo' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Estado --}}
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $estadoBadge }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $alerta->estado === 'ABIERTA' ? 'bg-amber-500' : ($alerta->estado === 'EN_ATENCION' ? 'bg-blue-600 animate-pulse' : 'bg-emerald-600') }}"></span>
                                    <span>{{ $estadoTexto }}</span>
                                </span>
                            </td>

                            {{-- Acciones Rápidas --}}
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Gráfico Clínico --}}
                                    <button type="button"
                                        wire:click="verGraficos('{{ $alerta->cod_am }}')"
                                        title="Ver gráficos clínicos en barra lateral"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-fondo-app hover:bg-boton-principal text-boton-principal hover:text-inverso border border-borde transition shadow-sm cursor-pointer active:scale-95">
                                        <i class="ph-bold ph-chart-line-up text-base"></i>
                                    </button>

                                    {{-- Ficha Cuidados y Ubicación --}}
                                    <button type="button"
                                        wire:click="verUbicacion('{{ $alerta->cod_am }}')"
                                        title="Ver ubicación y ficha en barra lateral"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-fondo-app hover:bg-boton-acento text-boton-acento hover:text-white border border-borde transition shadow-sm cursor-pointer active:scale-95">
                                        <i class="ph-bold ph-bed text-base"></i>
                                    </button>

                                    {{-- Ver Detalle --}}
                                    <button type="button"
                                        wire:click="verDetalle('{{ $alerta->cod_alerta }}')"
                                        title="Ver expediente e historial de la alerta"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-fondo-app hover:bg-fondo-hover text-parrafo border border-borde transition cursor-pointer active:scale-95">
                                        <i class="ph-bold ph-eye text-base"></i>
                                    </button>

                                    {{-- Atender (si está ABIERTA) --}}
                                    @if($alerta->estado === 'ABIERTA')
                                        @canany(['alertas.atender','alertas.gestionar','salud.alertas.gestionar'])
                                            <button type="button"
                                                wire:click="atenderAlerta('{{ $alerta->cod_alerta }}')"
                                                title="Iniciar atención asistencial inmediata"
                                                class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white dark:bg-blue-950/60 dark:hover:bg-blue-600 dark:text-blue-300 transition cursor-pointer shadow-sm">
                                                <i class="ph ph-stethoscope text-base"></i>
                                            </button>
                                        @endcanany
                                    @endif

                                    {{-- Cerrar (si está ABIERTA o EN_ATENCION) --}}
                                    @if($alerta->puedeCerrarse())
                                        @canany(['alertas.cerrar','alertas.gestionar','salud.alertas.gestionar'])
                                            <button type="button"
                                                wire:click="cerrarAlerta('{{ $alerta->cod_alerta }}')"
                                                title="Dar por resuelta y archivar alerta"
                                                class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white dark:bg-emerald-950/60 dark:hover:bg-emerald-600 dark:text-emerald-300 transition cursor-pointer shadow-sm">
                                                <i class="ph ph-check text-base"></i>
                                            </button>
                                        @endcanany
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 text-3xl">
                                        <i class="ph ph-bell-slash"></i>
                                    </span>
                                    <div class="space-y-1">
                                        <p class="font-bold text-slate-700 dark:text-slate-300 text-base">
                                            No se encontraron alertas clínicas registradas
                                        </p>
                                        <p class="text-xs text-slate-400 max-w-sm">
                                            Intenta ajustar los filtros de búsqueda o restablecer la selección actual.
                                        </p>
                                    </div>
                                    @if($search || $filtroEstado || $filtroNivel || $filtroOrigen || $filtroAdulto)
                                        <button type="button"
                                            wire:click="limpiarFiltros"
                                            class="mt-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 transition cursor-pointer">
                                            Restablecer filtros
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación Institucional --}}
        @if($alertas->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $alertas->links() }}
            </div>
        @endif
    </section>
    {{-- Modales de Gestión Clínica --}}
    @include('livewire.alertas.modales.crear')

    {{-- Modales Flotantes de Evoluci?n y Ubicaci?n --}}
    @include('livewire.alertas.modales.drawer-graficos')
    @include('livewire.alertas.modales.drawer-ubicacion')

    @include('livewire.alertas.modales.atender')
    @include('livewire.alertas.modales.cerrar')
    @include('livewire.alertas.modales.detalle')


{{-- SCRIPT ALPINE Y CHART.JS CON MOVIMIENTO Y DINAMISMO --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    window.alertasDashboardComponent = function alertasDashboardComponent(initialChartData, initialConteos) {
        return {
            chartData: initialChartData || {},
            conteos: initialConteos || {},
            charts: {
                severidad: null,
                origen: null,
                estado: null
            },

            get totalSeveridad() {
                const n = this.chartData.niveles || {};
                return (n.CRITICO || 0) + (n.ALTO || 0) + (n.MEDIO || 0) + (n.BAJO || 0);
            },

            get porcentajeResolucion() {
                const e = this.chartData.estados || {};
                const total = (e.ABIERTA || 0) + (e.EN_ATENCION || 0) + (e.CERRADA || 0);
                if (total === 0) return 0;
                return Math.round(((e.CERRADA || 0) / total) * 100);
            },

            initDashboard() {
                this.$nextTick(() => {
                    this.crearGraficos();
                });

                this.$watch('$wire.chartData', (newData) => {
                    if (newData) {
                        this.chartData = newData;
                        this.actualizarGraficos();
                    }
                });
            },

            crearGraficos() {
                if (typeof Chart === 'undefined') {
                    setTimeout(() => this.crearGraficos(), 100);
                    return;
                }

                this.crearGraficoSeveridad();
                this.crearGraficoOrigen();
                this.crearGraficoEstado();
            },

            crearGraficoSeveridad() {
                const ctx = document.getElementById('chartAlertasSeveridad');
                if (!ctx) return;

                if (this.charts.severidad) {
                    this.charts.severidad.destroy();
                }

                const n = this.chartData.niveles || {};

                this.charts.severidad = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Crítico', 'Alto', 'Medio', 'Bajo'],
                        datasets: [{
                            data: [
                                n.CRITICO || 0,
                                n.ALTO || 0,
                                n.MEDIO || 0,
                                n.BAJO || 0
                            ],
                            backgroundColor: [
                                '#E11D48', // Carmesíí Crítico
                                '#F59E0B', // ÁÁmbar Alto
                                '#3B82F6', // Azul Medio
                                '#10B981'  // Esmeralda Bajo
                            ],
                            borderWidth: 3,
                            borderColor: document.documentElement.classList.contains('dark') ? '#1E293B' : '#FFFFFF',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1200
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0F172A',
                                titleColor: '#FFFFFF',
                                bodyColor: '#CBD5E1',
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return ` ${context.label}: ${context.raw} alertas`;
                                    }
                                }
                            }
                        }
                    }
                });
            },

            crearGraficoOrigen() {
                const ctx = document.getElementById('chartAlertasOrigen');
                if (!ctx) return;

                if (this.charts.origen) {
                    this.charts.origen.destroy();
                }

                const origenes = this.chartData.origenes || {};
                const nombresEspanol = {
                    'SIGNOS': 'Signos Vitales',
                    'MEDICACION': 'Medicación',
                    'INCIDENTE': 'Incidente',
                    'PLAN': 'Plan Cuidado',
                    'SEGUIMIENTO': 'Seguimiento',
                    'SOLICITUD_MEDICA': 'Solicitud Médica',
                    'MANUAL': 'Manual',
                    'FICHA': 'Ficha Clínica',
                    'VALORACION': 'Valoración'
                };

                const labels = Object.keys(origenes).map(k => nombresEspanol[k] || k);
                const values = Object.values(origenes);

                const paleta = [
                    '#2563EB', '#0D9488', '#E11D48', '#8B5CF6',
                    '#D97706', '#059669', '#4F46E5', '#64748B'
                ];

                this.charts.origen = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels.length ? labels : ['Sin datos'],
                        datasets: [{
                            data: values.length ? values : [0],
                            backgroundColor: labels.map((_, i) => paleta[i % paleta.length]),
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 22
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1200
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0F172A',
                                titleColor: '#FFFFFF',
                                bodyColor: '#CBD5E1',
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return ` Total: ${context.raw} eventos`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)'
                                },
                                ticks: {
                                    precision: 0,
                                    font: { size: 11, weight: 'bold' }
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11, weight: '600' }
                                }
                            }
                        }
                    }
                });
            },

            crearGraficoEstado() {
                const ctx = document.getElementById('chartAlertasEstado');
                if (!ctx) return;

                if (this.charts.estado) {
                    this.charts.estado.destroy();
                }

                const e = this.chartData.estados || {};

                this.charts.estado = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Abiertas', 'En Atención', 'Resueltas'],
                        datasets: [{
                            data: [
                                e.ABIERTA || 0,
                                e.EN_ATENCION || 0,
                                e.CERRADA || 0
                            ],
                            backgroundColor: [
                                '#F59E0B', // ÁÁmbar Abiertas
                                '#3B82F6', // Azul En Atención
                                '#10B981'  // Esmeralda Resueltas
                            ],
                            borderWidth: 3,
                            borderColor: document.documentElement.classList.contains('dark') ? '#1E293B' : '#FFFFFF',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1200
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0F172A',
                                titleColor: '#FFFFFF',
                                bodyColor: '#CBD5E1',
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return ` ${context.label}: ${context.raw} alertas`;
                                    }
                                }
                            }
                        }
                    }
                });
            },

            actualizarGraficos() {
                if (this.charts.severidad && this.chartData.niveles) {
                    const n = this.chartData.niveles;
                    this.charts.severidad.data.datasets[0].data = [
                        n.CRITICO || 0,
                        n.ALTO || 0,
                        n.MEDIO || 0,
                        n.BAJO || 0
                    ];
                    this.charts.severidad.update();
                }

                if (this.charts.origen && this.chartData.origenes) {
                    const origenes = this.chartData.origenes;
                    const nombresEspanol = {
                        'SIGNOS': 'Signos Vitales',
                        'MEDICACION': 'Medicación',
                        'INCIDENTE': 'Incidente',
                        'PLAN': 'Plan Cuidado',
                        'SEGUIMIENTO': 'Seguimiento',
                        'SOLICITUD_MEDICA': 'Solicitud Médica',
                        'MANUAL': 'Manual',
                        'FICHA': 'Ficha Clínica',
                        'VALORACION': 'Valoración'
                    };
                    this.charts.origen.data.labels = Object.keys(origenes).map(k => nombresEspanol[k] || k);
                    this.charts.origen.data.datasets[0].data = Object.values(origenes);
                    this.charts.origen.update();
                }

                if (this.charts.estado && this.chartData.estados) {
                    const e = this.chartData.estados;
                    this.charts.estado.data.datasets[0].data = [
                        e.ABIERTA || 0,
                        e.EN_ATENCION || 0,
                        e.CERRADA || 0
                    ];
                    this.charts.estado.update();
                }
            }
        };
    }
</script>
</div>
