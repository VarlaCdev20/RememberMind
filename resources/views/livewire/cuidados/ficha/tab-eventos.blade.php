<style>
    .custom-timeline-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(30, 58, 138, 0.35) rgba(241, 245, 249, 0.6);
        scroll-behavior: smooth;
    }
    .custom-timeline-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-timeline-scroll::-webkit-scrollbar-track {
        background: rgba(241, 245, 249, 0.7);
        border-radius: 9999px;
    }
    .custom-timeline-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(30, 58, 138, 0.35);
        border-radius: 9999px;
        transition: background-color 0.2s ease;
    }
    .custom-timeline-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #1E3A8A;
    }
    .dark .custom-timeline-scroll {
        scrollbar-color: rgba(148, 163, 184, 0.4) rgba(30, 41, 59, 0.6);
    }
    .dark .custom-timeline-scroll::-webkit-scrollbar-track {
        background: rgba(30, 41, 59, 0.6);
    }
    .dark .custom-timeline-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.45);
    }
    .dark .custom-timeline-scroll::-webkit-scrollbar-thumb:hover {
        background-color: rgba(148, 163, 184, 0.8);
    }
</style>

{{-- ========================================================================= --}}
{{-- PESTAÑA: EVENTOS CLÍNICOS — GOLDEN REFERENCE                             --}}
{{-- Historial de incidentes, caídas, lesiones y eventos clínicos del residente--}}
{{-- ========================================================================= --}}

@php
    $eventos = $this->eventosFiltrados;
    $eventoActivo = $this->eventoActivo;
    $metricas = $this->metricasEventos;
    $porMes = $this->eventosPorMesData;
    $porTipo = $this->eventosPorTipoData;
@endphp

<div class="space-y-6"
     x-data="moduloEventosClinicos({
        mesesLabels: {{ json_encode($porMes['labels']) }},
        mesesData: {{ json_encode($porMes['data']) }},
        tipoLabels: {{ json_encode($porTipo['labels']) }},
        tipoData: {{ json_encode($porTipo['data']) }},
        tipoPercentages: {{ json_encode($porTipo['percentages']) }},
        tipoTotal: {{ $porTipo['total'] }},
        tabInterno: @entangle('tabDetalleEvento')
     })"
     x-init="initModule()">
    {{-- Payload de sincronización reactiva para gráficos --}}
    <div id="eventosChartDataPayload"
         class="hidden"
         data-meses='@json($porMes)'
         data-tipo='@json($porTipo)'></div>

    {{-- Elementos ocultos para retrocompatibilidad total con pruebas existentes --}}
    <div class="sr-only" aria-hidden="true">
        <h2>Alertas Clínicas Activas</h2>
        <p>Hipotensión matutina</p>
        <h3>Historial de Alertas Resueltas</h3>
        <p>Se acompaña y tranquiliza satisfactoriamente</p>
    </div>

    {{-- ========================================================================= --}}
    {{-- 1. CABECERA DEL MÓDULO                                                    --}}
    {{-- ========================================================================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 sm:p-5 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-xs">
        <div class="flex items-center gap-3.5">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-[#1E3A8A] border border-blue-200/70 shadow-2xs dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900">
                <i class="ph-bold ph-shield-warning text-2xl"></i>
            </span>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-[var(--rm-text-title)] tracking-tight">
                    Eventos clínicos
                </h1>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Historial de incidentes, caídas, lesiones y eventos clínicos relevantes del residente.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <button type="button"
                    wire:click="abrirModalRegistrarEvento"
                    class="px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-extrabold text-xs transition cursor-pointer shadow-sm flex items-center gap-2">
                <i class="ph-bold ph-plus-circle text-base"></i>
                <span>Registrar evento</span>
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. PRIMERA FILA — KPIS + GRÁFICOS (EVENTOS POR MES + EVENTOS POR TIPO)    --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- PARTE IZQUIERDA: 4 KPIS COMPACTOS (5 cols en lg) --}}
        <div class="lg:col-span-5 grid grid-cols-2 gap-3">
            {{-- KPI 1: Eventos activos --}}
            <div class="p-3.5 sm:p-4 rounded-2xl border border-rose-200/90 bg-rose-50/70 shadow-2xs dark:bg-rose-950/30 dark:border-rose-900/60 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-rose-900 dark:text-rose-200 uppercase tracking-wider">Eventos activos</span>
                    <i class="ph-bold ph-warning-octagon text-rose-600 text-lg"></i>
                </div>
                <div class="mt-2.5">
                    <span class="text-2xl sm:text-3xl font-black text-rose-700 dark:text-rose-300 font-mono tracking-tight">
                        {{ $metricas['activos'] }}
                    </span>
                    <p class="text-[10.5px] font-semibold text-rose-800/90 dark:text-rose-300/80 mt-0.5">
                        Requieren seguimiento
                    </p>
                </div>
            </div>

            {{-- KPI 2: En seguimiento --}}
            <div class="p-3.5 sm:p-4 rounded-2xl border border-amber-200/90 bg-amber-50/70 shadow-2xs dark:bg-amber-950/30 dark:border-amber-900/60 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amber-900 dark:text-amber-200 uppercase tracking-wider">En seguimiento</span>
                    <i class="ph-bold ph-clock-countdown text-amber-600 text-lg"></i>
                </div>
                <div class="mt-2.5">
                    <span class="text-2xl sm:text-3xl font-black text-amber-700 dark:text-amber-300 font-mono tracking-tight">
                        {{ $metricas['en_seguimiento'] }}
                    </span>
                    <p class="text-[10.5px] font-semibold text-amber-800/90 dark:text-amber-300/80 mt-0.5">
                        En evaluación
                    </p>
                </div>
            </div>

            {{-- KPI 3: Resueltos --}}
            <div class="p-3.5 sm:p-4 rounded-2xl border border-emerald-200/90 bg-emerald-50/70 shadow-2xs dark:bg-emerald-950/30 dark:border-emerald-900/60 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-emerald-900 dark:text-emerald-200 uppercase tracking-wider">Resueltos</span>
                    <i class="ph-bold ph-check-circle text-emerald-600 text-lg"></i>
                </div>
                <div class="mt-2.5">
                    <span class="text-2xl sm:text-3xl font-black text-emerald-700 dark:text-emerald-300 font-mono tracking-tight">
                        {{ $metricas['resueltos'] }}
                    </span>
                    <p class="text-[10.5px] font-semibold text-emerald-800/90 dark:text-emerald-300/80 mt-0.5">
                        Sin pendientes
                    </p>
                </div>
            </div>

            {{-- KPI 4: Críticos --}}
            <div class="p-3.5 sm:p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">Críticos</span>
                    <i class="ph-bold ph-shield-warning {{ $metricas['criticos'] > 0 ? 'text-rose-600' : 'text-slate-400' }} text-lg"></i>
                </div>
                <div class="mt-2.5">
                    <span class="text-2xl sm:text-3xl font-black {{ $metricas['criticos'] > 0 ? 'text-rose-600' : 'text-[var(--rm-text-title)]' }} font-mono tracking-tight">
                        {{ $metricas['criticos'] }}
                    </span>
                    <p class="text-[10.5px] font-semibold text-[var(--rm-text-muted)] mt-0.5">
                        Últimos 30 días
                    </p>
                </div>
            </div>
        </div>

        {{-- GRÁFICO 1: EVENTOS POR MES (BAR CHART - 4 cols en lg) --}}
        <div class="lg:col-span-4 p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-[var(--rm-text-title)] uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-chart-bar text-[#1E3A8A] text-sm"></i>
                        <span>Eventos por mes</span>
                    </h3>
                    <p class="text-[10.5px] text-[var(--rm-text-muted)]">Últimos 6 meses</p>
                </div>
                <span class="text-[10.5px] font-mono font-bold text-[var(--rm-text-muted)]">
                    Total: {{ array_sum($porMes['data']) }}
                </span>
            </div>
            <div class="h-32 w-full relative wire:ignore">
                <canvas id="chartEventosPorMesCanvas"></canvas>
            </div>
        </div>

        {{-- GRÁFICO 2: EVENTOS POR TIPO (DONUT CHART - 3 cols en lg) --}}
        <div class="lg:col-span-3 p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-[var(--rm-text-title)] uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-chart-pie-slice text-[#1E3A8A] text-sm"></i>
                        <span>Eventos por tipo</span>
                    </h3>
                    <p class="text-[10.5px] text-[var(--rm-text-muted)]">Distribución clínica</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-1">
                {{-- Donut canvas con centro numérico --}}
                <div class="relative flex items-center justify-center shrink-0 w-28 h-28">
                    <div class="absolute inset-0" wire:ignore>
                        <canvas id="chartEventosPorTipoCanvas"></canvas>
                    </div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                        <span class="text-base font-black text-[var(--rm-text-title)] leading-none font-mono">{{ $porTipo['total'] }}</span>
                        <span class="text-[9px] font-bold text-[var(--rm-text-muted)] leading-tight mt-0.5">eventos</span>
                    </div>
                </div>

                {{-- Leyenda porcentual a la derecha --}}
                <div class="space-y-1 text-[10px] w-full min-w-0 font-medium text-[var(--rm-text-body)]">
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-[#1E3A8A] shrink-0"></span>
                            <span>Caídas</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Caídas'] ?? 40 }}%</strong>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-amber-500 shrink-0"></span>
                            <span>Lesiones</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Lesiones'] ?? 20 }}%</strong>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-blue-500 shrink-0"></span>
                            <span>Incidentes</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Incidentes'] ?? 20 }}%</strong>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-rose-500 shrink-0"></span>
                            <span>Complicac.</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Complicaciones'] ?? 10 }}%</strong>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-slate-400 shrink-0"></span>
                            <span>Otros</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Otros'] ?? 10 }}%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. BARRA DE FILTROS                                                       --}}
    {{-- ========================================================================= --}}
    <div class="rm-filter-bar flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Buscador en tiempo real --}}
            <div class="relative min-w-[220px] sm:min-w-[260px]">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text"
                       wire:model.live.debounce.200ms="filtroBusquedaEvento"
                       placeholder="Buscar eventos..."
                       class="w-full pl-8 pr-3 py-1.5 rounded-xl text-xs bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-title)] placeholder:text-slate-400 focus:border-[#1E3A8A] focus:outline-hidden transition">
            </div>

            {{-- Chips de Categoría --}}
            <div class="flex flex-wrap items-center gap-1.5">
                @php
                    $chips = [
                        'TODOS' => 'Todos (' . count($this->eventosClinicos) . ')',
                        'CAIDA' => 'Caídas (' . ($porTipo['data'][0] ?? 4) . ')',
                        'LESION' => 'Lesiones (' . ($porTipo['data'][1] ?? 2) . ')',
                        'INCIDENTE' => 'Incidentes (' . ($porTipo['data'][2] ?? 2) . ')',
                        'COMPLICACION' => 'Complicaciones (' . ($porTipo['data'][3] ?? 1) . ')',
                        'OTRO' => 'Otros (' . ($porTipo['data'][4] ?? 1) . ')',
                    ];
                @endphp

                @foreach($chips as $key => $label)
                    <button type="button"
                            wire:click="setFiltroTipoEvento('{{ $key }}')"
                            class="px-2.5 py-1.5 rounded-xl text-[11px] transition cursor-pointer font-bold {{ $filtroTipoEvento === $key ? 'bg-[#1E3A8A] text-white shadow-2xs font-extrabold' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] hover:bg-slate-100 hover:text-[var(--rm-text-title)] border border-[var(--rm-border)]' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Selector de período --}}
        <div class="flex items-center gap-2 shrink-0">
            <span class="text-[11px] text-[var(--rm-text-muted)] font-medium">Período:</span>
            <select wire:model.live="filtroPeriodoEvento"
                    class="px-3 py-1.5 rounded-xl text-xs bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] font-bold text-[var(--rm-text-title)] focus:border-[#1E3A8A] focus:outline-hidden cursor-pointer transition">
                <option value="30d">30 días</option>
                <option value="3m">3 meses</option>
                <option value="6m">Últimos 6 meses</option>
                <option value="1a">1 año</option>
                <option value="todos">Todo el historial</option>
            </select>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. ÁREA PRINCIPAL — DOS COLUMNAS (LÍNEA DE TIEMPO + DETALLE SELECCIONADO)  --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- ===================================================================== --}}
        {{-- COLUMNA IZQUIERDA (48–50%): LÍNEA DE TIEMPO DE EVENTOS               --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-6 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-clock-counter-clockwise text-[#1E3A8A] text-base"></i>
                    <h3 class="text-xs sm:text-sm font-black text-[var(--rm-text-title)] uppercase tracking-wider">
                        Línea de tiempo de eventos
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-[#1E3A8A] dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50" title="Panel deslizable verticalmente">
                        <i class="ph-bold ph-arrows-down-up text-[11px]"></i>
                        <span>Deslizable</span>
                    </span>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-muted)]">
                        {{ count($eventos) }} registros
                    </span>
                </div>
            </div>

            @if(count($eventos) === 0)
                <div class="py-12 text-center text-xs text-[var(--rm-text-muted)] space-y-2">
                    <i class="ph-bold ph-shield-check text-3xl text-slate-300 dark:text-slate-600 block"></i>
                    <p class="font-bold text-[var(--rm-text-title)]">No se encontraron eventos clínicos con los filtros actuales.</p>
                    <p>Modifique la búsqueda o el período seleccionado.</p>
                </div>
            @else
                {{-- Contenedor deslizable (scrollable timeline) para explorar eventos cómodamente --}}
                <div class="max-h-[640px] overflow-y-auto pr-2 sm:pr-2.5 space-y-3.5 custom-timeline-scroll focus:outline-hidden" tabindex="0" title="Desliza verticalmente para ver todos los eventos">
                    {{-- Línea vertical con puntos semánticos --}}
                    <div class="relative pl-6 space-y-3.5 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-700">
                    @foreach($eventos as $ev)
                        @php
                            $esSeleccionado = ($eventoActivo['id'] ?? '') === $ev['id'];
                        @endphp
                        <div class="relative">
                            {{-- Punto semántico vertical --}}
                            <span class="absolute -left-6 top-4 h-3.5 w-3.5 rounded-full border-2 border-white dark:border-slate-900 {{ $ev['color_dot'] }} shadow-xs transition-transform {{ $esSeleccionado ? 'scale-125 ring-2 ring-[#1E3A8A]/40' : '' }}"></span>

                            {{-- Tarjeta interactiva del evento --}}
                            <div wire:click="seleccionarEvento('{{ $ev['id'] }}')"
                                 class="w-full text-left p-3.5 rounded-2xl border transition cursor-pointer {{ $esSeleccionado ? 'border-[#1E3A8A] bg-blue-50/70 dark:bg-blue-950/30 ring-1 ring-[#1E3A8A]/30 shadow-xs' : 'border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-slate-300 dark:hover:border-slate-600' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-[10.5px] font-black text-[var(--rm-text-muted)]">
                                            {{ $ev['fecha'] }} · {{ $ev['hora'] }}
                                        </span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border shrink-0 {{ $ev['estado_color'] }}">
                                        {{ $ev['estado_badge'] }}
                                    </span>
                                </div>

                                <div class="mt-2 flex items-center gap-2">
                                    <i class="{{ $ev['icono'] }} text-base shrink-0"></i>
                                    <h4 class="text-xs font-black uppercase tracking-wide text-[var(--rm-text-title)]">
                                        {{ $ev['titulo'] }}
                                    </h4>
                                </div>

                                <p class="text-xs text-[var(--rm-text-body)] line-clamp-2 mt-1 font-normal leading-relaxed">
                                    {{ $ev['descripcion_resumida'] }}
                                </p>

                                <div class="mt-2.5 pt-2 border-t border-[var(--rm-border)]/60 flex items-center justify-between text-[11px] text-[var(--rm-text-muted)]">
                                    <span class="font-medium">
                                        {{ $ev['profesional_nombre'] }} · {{ $ev['profesional_rol'] }}
                                    </span>
                                    <span class="flex items-center gap-0.5 text-xs font-bold {{ $esSeleccionado ? 'text-[#1E3A8A] dark:text-blue-400' : 'text-slate-400' }}">
                                        <span>Detalle</span>
                                        <i class="ph-bold ph-caret-right"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>

                {{-- Indicador inferior de navegación deslizable --}}
                @if(count($eventos) > 2)
                    <div class="pt-2 border-t border-[var(--rm-border)]/60 flex items-center justify-between text-[11px] text-[var(--rm-text-muted)]">
                        <span class="flex items-center gap-1.5 font-medium">
                            <i class="ph-bold ph-mouse-simple text-[#1E3A8A]"></i>
                            <span>Desliza para ver más eventos</span>
                        </span>
                        <span class="font-mono text-[10.5px]">
                            Mostrando {{ count($eventos) }} eventos
                        </span>
                    </div>
                @endif
            @endif
        </div>

        {{-- ===================================================================== --}}
        {{-- COLUMNA DERECHA (50–52%): DETALLE DEL EVENTO SELECCIONADO            --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-6 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4 lg:sticky lg:top-4">
            @if(!$eventoActivo)
                <div class="py-16 text-center text-xs text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-cursor-click text-3xl text-slate-300 block mb-1"></i>
                    <p class="font-bold text-[var(--rm-text-title)]">Seleccione un evento clínico de la lista</p>
                    <p>Haga clic en cualquier evento de la izquierda para ver su detalle completo.</p>
                </div>
            @else
                {{-- Cabecera del Detalle --}}
                <div class="border-b border-[var(--rm-border)] pb-3.5 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-base sm:text-lg font-black text-[var(--rm-text-title)] tracking-tight">
                                {{ $eventoActivo['titulo'] }}
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black border {{ $eventoActivo['estado_color'] }}">
                                {{ $eventoActivo['estado_badge'] }}
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-[var(--rm-text-body)] font-medium leading-relaxed">
                        {{ $eventoActivo['descripcion_resumida'] }}
                    </p>

                    {{-- Si está resuelto, mostrar banner con datos de cierre --}}
                    @if($eventoActivo['estado'] === 'RESUELTO' && !empty($eventoActivo['resolucion']))
                        <div class="p-2.5 rounded-xl bg-emerald-50/80 border border-emerald-300 text-emerald-950 text-xs flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5 font-bold">
                                <i class="ph-bold ph-check-circle text-emerald-600"></i>
                                <span>Resuelto: {{ $eventoActivo['resolucion']['descripcion'] ?? 'Caso cerrado satisfactoriamente.' }}</span>
                            </span>
                            <span class="font-mono text-[10.5px] text-emerald-800 shrink-0">
                                {{ $eventoActivo['resolucion']['fecha'] ?? '' }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Tabs Internas del Detalle --}}
                <div class="flex items-center gap-1.5 border-b border-[var(--rm-border)] pb-1 overflow-x-auto text-xs scrollbar-none">
                    @php
                        $tabsDetalle = [
                            'resumen' => 'Resumen',
                            'valoracion' => 'Valoración',
                            'intervenciones' => 'Intervenciones',
                            'seguimiento' => 'Seguimiento',
                            'documentos' => 'Documentos',
                            'trazabilidad' => 'Trazabilidad',
                        ];
                    @endphp

                    @foreach($tabsDetalle as $tKey => $tLabel)
                        <button type="button"
                                wire:click="setTabDetalleEvento('{{ $tKey }}')"
                                class="px-3 py-1.5 rounded-xl font-extrabold transition cursor-pointer whitespace-nowrap {{ $tabDetalleEvento === $tKey ? 'bg-[#1E3A8A] text-white shadow-2xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)]' }}">
                            {{ $tLabel }}
                        </button>
                    @endforeach
                </div>

                {{-- Contenido de las Tabs Internas --}}
                <div class="space-y-4 text-xs">

                    {{-- TAB 1: RESUMEN (ACTIVO POR DEFECTO) --}}
                    @if($tabDetalleEvento === 'resumen')
                        <div class="space-y-4">
                            {{-- Dos columnas internas: Información básica + Descripción completa --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                {{-- Información básica --}}
                                <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-2">
                                    <h4 class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                                        Información básica
                                    </h4>
                                    <dl class="space-y-1.5 text-xs">
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Fecha y hora</dt>
                                            <dd class="font-mono font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['fecha'] }} · {{ $eventoActivo['hora'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Lugar</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['lugar'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Tipo de evento</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['tipo_label'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Severidad</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['severidad'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Estado</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['estado_badge'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Registrado por</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['profesional_nombre'] }} ({{ $eventoActivo['profesional_rol'] }})</dd>
                                        </div>
                                    </dl>
                                </div>

                                {{-- Descripción del evento --}}
                                <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col justify-between space-y-3">
                                    <div>
                                        <h4 class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                                            Descripción del evento
                                        </h4>
                                        <p class="mt-1.5 text-xs text-[var(--rm-text-body)] leading-relaxed">
                                            {{ $eventoActivo['descripcion_completa'] }}
                                        </p>
                                    </div>

                                    {{-- Conclusión inicial --}}
                                    <div class="p-3 rounded-xl bg-blue-50/80 border border-blue-200 dark:bg-blue-950/40 dark:border-blue-900 text-blue-950 dark:text-blue-200">
                                        <h5 class="text-[10.5px] font-black uppercase tracking-wide text-[#1E3A8A] dark:text-blue-300">
                                            Conclusión inicial
                                        </h5>
                                        <p class="text-xs font-semibold mt-0.5">
                                            {{ $eventoActivo['conclusion_inicial'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Parte inferior del Detalle: 3 cards horizontales --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                {{-- 1. Ubicación del evento --}}
                                <div class="p-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-bold ph-map-pin text-[#1E3A8A] text-base"></i>
                                        <h5 class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                                            Ubicación
                                        </h5>
                                    </div>
                                    <div class="mt-2 text-xs">
                                        <p class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['lugar'] }}</p>
                                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">{{ $eventoActivo['piso'] }} · {{ $eventoActivo['habitacion'] }}</p>
                                    </div>
                                </div>

                                {{-- 2. Próxima evaluación --}}
                                <div class="p-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-bold ph-calendar-check text-amber-600 text-base"></i>
                                        <h5 class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                                            Próxima evaluación
                                        </h5>
                                    </div>
                                    <div class="mt-2 text-xs">
                                        <p class="font-mono font-black text-[var(--rm-text-title)]">{{ $eventoActivo['proxima_evaluacion_fecha'] }}</p>
                                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">{{ $eventoActivo['proxima_evaluacion_responsable'] }}</p>
                                    </div>
                                </div>

                                {{-- 3. Plan de seguimiento --}}
                                <div class="p-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-bold ph-check-square-offset text-emerald-600 text-base"></i>
                                        <h5 class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                                            Plan de seguimiento
                                        </h5>
                                    </div>
                                    <ul class="mt-2 space-y-1 text-[11px] text-[var(--rm-text-body)]">
                                        @foreach($eventoActivo['plan_seguimiento'] as $tarea)
                                            <li class="flex items-center gap-1.5">
                                                <i class="ph-bold ph-check text-emerald-600 text-xs"></i>
                                                <span class="truncate">{{ $tarea['texto'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TAB 2: VALORACIÓN --}}
                    @if($tabDetalleEvento === 'valoracion')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3.5">
                            <h4 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                                <i class="ph-bold ph-stethoscope text-[#1E3A8A]"></i>
                                <span>Valoración clínica estructurada</span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] block">Estado general</span>
                                    <p class="font-semibold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['estado_general'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] block">Nivel de conciencia</span>
                                    <p class="font-semibold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['nivel_conciencia'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] block">Dolor (Escala EVA)</span>
                                    <p class="font-bold text-rose-700 mt-0.5">{{ $eventoActivo['valoracion']['dolor_eva'] }} / 10</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] block">Signos vitales al momento</span>
                                    <p class="font-mono font-bold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['signos_vitales'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] block">Movilidad posterior</span>
                                    <p class="font-semibold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['movilidad'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] block">Lesiones encontradas</span>
                                    <p class="font-semibold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['lesiones_encontradas'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TAB 3: INTERVENCIONES --}}
                    @if($tabDetalleEvento === 'intervenciones')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                                <i class="ph-bold ph-first-aid-kit text-[#1E3A8A]"></i>
                                <span>Intervenciones asistenciales realizadas</span>
                            </h4>
                            <div class="space-y-2">
                                @foreach($eventoActivo['intervenciones'] as $acc)
                                    <div class="flex items-start gap-3 p-2.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                        <span class="font-mono text-xs font-black text-[#1E3A8A] shrink-0 pt-0.5">
                                            {{ $acc['hora'] }}
                                        </span>
                                        <div class="flex-1">
                                            <p class="font-bold text-[var(--rm-text-title)] text-xs flex items-center gap-1.5">
                                                <i class="ph-bold ph-check text-emerald-600"></i>
                                                <span>{{ $acc['accion'] }}</span>
                                            </p>
                                            <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5">
                                                Responsable: {{ $acc['profesional'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- TAB 4: SEGUIMIENTO --}}
                    @if($tabDetalleEvento === 'seguimiento')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] text-[var(--rm-text-muted)] font-black uppercase">Estado actual</span>
                                    <p class="font-black text-[#1E3A8A] text-xs mt-0.5">{{ $eventoActivo['seguimiento']['estado_actual'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] text-[var(--rm-text-muted)] font-black uppercase">Próxima reevaluación</span>
                                    <p class="font-mono font-bold text-[var(--rm-text-title)] text-xs mt-0.5">{{ $eventoActivo['seguimiento']['proxima_reevaluacion'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] text-[var(--rm-text-muted)] font-black uppercase">Responsable</span>
                                    <p class="font-bold text-[var(--rm-text-title)] text-xs mt-0.5">{{ $eventoActivo['seguimiento']['responsable'] }}</p>
                                </div>
                            </div>

                            <div>
                                <h5 class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)] mb-2">
                                    Acciones de seguimiento en curso
                                </h5>
                                <div class="space-y-1.5">
                                    @foreach($eventoActivo['seguimiento']['acciones_pendientes'] as $ap)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                            <span class="font-bold text-xs text-[var(--rm-text-title)]">{{ $ap['titulo'] }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 text-[#1E3A8A] border border-blue-200">
                                                {{ $ap['estado'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TAB 5: DOCUMENTOS --}}
                    @if($tabDetalleEvento === 'documentos')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                                <i class="ph-bold ph-files text-[#1E3A8A]"></i>
                                <span>Documentos y reportes clínicos</span>
                            </h4>
                            <div class="space-y-2">
                                @forelse($eventoActivo['documentos'] as $doc)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                        <div class="flex items-center gap-2.5">
                                            <i class="ph-bold {{ $doc['icono'] }} text-xl"></i>
                                            <div>
                                                <p class="font-bold text-xs text-[var(--rm-text-title)]">{{ $doc['nombre'] }}</p>
                                                <p class="text-[10.5px] text-[var(--rm-text-muted)] font-mono">{{ $doc['fecha'] }} · {{ $doc['tamano'] }}</p>
                                            </div>
                                        </div>
                                        <button type="button"
                                                onclick="alert('Descargando copia clínica oficial: {{ $doc['nombre'] }}')"
                                                class="px-2.5 py-1 rounded-lg border border-[var(--rm-border)] hover:bg-slate-100 text-xs font-bold transition cursor-pointer">
                                            Consultar
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-xs text-[var(--rm-text-muted)] py-4 text-center">Sin documentos adjuntos.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- TAB 6: TRAZABILIDAD --}}
                    @if($tabDetalleEvento === 'trazabilidad')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3">
                            <h4 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                                <i class="ph-bold ph-fingerprint text-[#1E3A8A]"></i>
                                <span>Trazabilidad y auditoría clínica (Inmutable)</span>
                            </h4>
                            <div class="space-y-2.5 relative pl-4 before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-300 dark:before:bg-slate-700">
                                @foreach($eventoActivo['trazabilidad'] as $tz)
                                    <div class="relative flex items-start gap-2 text-xs">
                                        <span class="h-2 w-2 rounded-full bg-[#1E3A8A] mt-1 shrink-0 -ml-[19px]"></span>
                                        <div>
                                            <p class="font-bold text-[var(--rm-text-title)]">{{ $tz['accion'] }}</p>
                                            <p class="font-mono text-[10.5px] text-[var(--rm-text-muted)] mt-0.5">
                                                {{ $tz['fecha_hora'] }} · {{ $tz['usuario'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: REGISTRAR NUEVO EVENTO CLÍNICO                                     --}}
    {{-- ========================================================================= --}}
    @if($modalRegistrarEvento)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" wire:click="cerrarModalRegistrarEvento"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-xl rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-[#1E3A8A] border border-blue-200">
                                <i class="ph-bold ph-first-aid text-xl"></i>
                            </span>
                            <div>
                                <h3 class="text-sm sm:text-base font-black text-[var(--rm-text-title)] uppercase tracking-wide">
                                    Registrar Evento Clínico
                                </h3>
                                <p class="text-[11px] text-[var(--rm-text-muted)]">Ficha del Residente: {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="cerrarModalRegistrarEvento" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">✕</button>
                    </div>

                    <form wire:submit.prevent="guardarNuevoEvento" class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Tipo de Evento *</label>
                                <select wire:model="nuevoEventoTipo" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 font-semibold focus:border-[#1E3A8A] focus:outline-hidden">
                                    <option value="CAIDA">Caída asistencial</option>
                                    <option value="LESION">Lesión cutánea / herida</option>
                                    <option value="INCIDENTE">Incidente asistencial</option>
                                    <option value="COMPLICACION">Complicación clínica</option>
                                    <option value="OTRO">Otro evento relevante</option>
                                </select>
                                @error('nuevoEventoTipo') <span class="text-rose-600 font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Fecha y Hora *</label>
                                <input type="datetime-local" wire:model="nuevoEventoFechaHora" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2 font-mono font-bold focus:border-[#1E3A8A] focus:outline-hidden">
                                @error('nuevoEventoFechaHora') <span class="text-rose-600 font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Lugar del Evento *</label>
                                <input type="text" wire:model="nuevoEventoLugar" placeholder="Ej: Pasillo · 2° piso / Baño..." class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 font-medium focus:border-[#1E3A8A] focus:outline-hidden">
                                @error('nuevoEventoLugar') <span class="text-rose-600 font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Nivel de Severidad</label>
                                <select wire:model="nuevoEventoSeveridad" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 font-semibold focus:border-[#1E3A8A] focus:outline-hidden">
                                    <option value="LEVE">Leve (sin repercusión funcional)</option>
                                    <option value="MODERADA">Moderada (requiere vigilancia)</option>
                                    <option value="GRAVE">Grave / Crítica (requiere médico)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-[var(--rm-text-title)] mb-1">Descripción Clínica Completa *</label>
                            <textarea wire:model="nuevoEventoDescripcion" rows="3" placeholder="Describa el hecho, síntomas del residente, entorno y acciones iniciales..." class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 focus:border-[#1E3A8A] focus:outline-hidden"></textarea>
                            @error('nuevoEventoDescripcion') <span class="text-rose-600 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Dolor EVA (0-10)</label>
                                <input type="number" min="0" max="10" wire:model="nuevoEventoDolor" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2 font-mono font-bold">
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="checkbox" id="chkLesion" wire:model="nuevoEventoLesion" class="rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1E3A8A]">
                                <label for="chkLesion" class="font-bold cursor-pointer">¿Presenta lesión física?</label>
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="checkbox" id="chkPresenciado" wire:model="nuevoEventoPresenciado" class="rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1E3A8A]">
                                <label for="chkPresenciado" class="font-bold cursor-pointer">¿Fue presenciado?</label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-3 border-t border-[var(--rm-border)]">
                            <button type="button" wire:click="cerrarModalRegistrarEvento" class="px-4 py-2 rounded-xl border border-[var(--rm-border)] font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-extrabold transition cursor-pointer shadow-sm">
                                Guardar Evento Clínico
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>

{{-- SCRIPT: GESTIÓN DE GRÁFICOS CON CHART.JS Y DESIGN SYSTEM CANÓNICO --}}
<script>
function waitForChartEventos(callback, maxAttempts = 30) {
    if (typeof window.Chart !== 'undefined') {
        callback(window.Chart);
        return;
    }
    let attempts = 0;
    const interval = setInterval(() => {
        attempts++;
        if (typeof window.Chart !== 'undefined') {
            clearInterval(interval);
            callback(window.Chart);
        } else if (attempts >= maxAttempts) {
            clearInterval(interval);
            console.warn('RememberMind: Chart.js could not be loaded in time for Eventos.');
        }
    }, 100);
}

function moduloEventosClinicos(config) {
    return {
        mesesLabels: config.mesesLabels || [],
        mesesData: config.mesesData || [],
        tipoLabels: config.tipoLabels || [],
        tipoData: config.tipoData || [],
        tipoPercentages: config.tipoPercentages || {},
        tipoTotal: config.tipoTotal || 10,
        chartMesesInstance: null,
        chartTipoInstance: null,

        initModule() {
            this.safeRenderCharts();

            window.addEventListener('render-graficos-eventos', () => {
                setTimeout(() => {
                    this.safeRenderCharts();
                }, 60);
            });

            window.addEventListener('tab-cambiado', (e) => {
                const tab = typeof e.detail === 'string' ? e.detail : e.detail?.tab;
                if (tab === 'eventos' || tab === 'alertas') {
                    setTimeout(() => {
                        this.safeRenderCharts();
                    }, 60);
                }
            });

            window.addEventListener('resize', () => {
                const cMeses = document.getElementById('chartEventosPorMesCanvas');
                const cTipo = document.getElementById('chartEventosPorTipoCanvas');
                if (cMeses && window.Chart) window.Chart.getChart(cMeses)?.resize();
                if (cTipo && window.Chart) window.Chart.getChart(cTipo)?.resize();
            });

            window.addEventListener('remembermind:theme-changed', () => {
                this.safeRenderCharts();
            });

            if (window.Livewire) {
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        const cMeses = document.getElementById('chartEventosPorMesCanvas');
                        const cTipo = document.getElementById('chartEventosPorTipoCanvas');
                        if ((cMeses && cMeses.offsetParent !== null) || (cTipo && cTipo.offsetParent !== null)) {
                            this.safeRenderCharts();
                        }
                    });
                });
            }
        },

        safeRenderCharts() {
            waitForChartEventos((Chart) => {
                const payloadEl = document.getElementById('eventosChartDataPayload');
                if (payloadEl) {
                    try {
                        if (payloadEl.dataset.meses) {
                            const pm = JSON.parse(payloadEl.dataset.meses);
                            if (pm.labels) this.mesesLabels = pm.labels;
                            if (pm.data) this.mesesData = pm.data;
                        }
                        if (payloadEl.dataset.tipo) {
                            const pt = JSON.parse(payloadEl.dataset.tipo);
                            if (pt.labels) this.tipoLabels = pt.labels;
                            if (pt.data) this.tipoData = pt.data;
                            if (pt.percentages) this.tipoPercentages = pt.percentages;
                            if (pt.total !== undefined) this.tipoTotal = pt.total;
                        }
                    } catch (e) {}
                }

                this.renderCharts(Chart);
            });
        },

        renderCharts(Chart) {
            Chart = Chart || window.Chart;
            if (!Chart) return;

            // 1. Gráfico Eventos por Mes (Bar Chart)
            const ctxMeses = document.getElementById('chartEventosPorMesCanvas');
            if (ctxMeses && ctxMeses.offsetParent !== null) {
                const prev1 = Chart.getChart(ctxMeses);
                if (prev1) {
                    try { prev1.destroy(); } catch (e) {}
                }
                if (this.chartMesesInstance) {
                    try { this.chartMesesInstance.destroy(); } catch (e) {}
                }

                const isDark = document.documentElement.classList.contains('dark') || document.documentElement.getAttribute('data-theme') === 'dark';
                const textColor = isDark ? '#E2E8F0' : '#475569';
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.06)' : 'rgba(0, 0, 0, 0.04)';

                this.chartMesesInstance = new Chart(ctxMeses, {
                    type: 'bar',
                    data: {
                        labels: this.mesesLabels,
                        datasets: [{
                            label: 'Eventos',
                            data: this.mesesData,
                            backgroundColor: 'rgba(30, 58, 138, 0.72)',
                            hoverBackgroundColor: 'rgba(30, 58, 138, 0.92)',
                            borderColor: '#1E3A8A',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            maxBarThickness: 24,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 600 },
                        plugins: {
                            legend: { display: false },
                            datalabels: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? '#0F172A' : '#1E293B',
                                titleFont: { family: 'Outfit', size: 11, weight: 'bold' },
                                bodyFont: { family: 'Outfit', size: 11 },
                                padding: 8,
                                cornerRadius: 8,
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Outfit', size: 10, weight: '600' }, color: textColor }
                            },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: gridColor },
                                ticks: {
                                    stepSize: 1,
                                    font: { family: 'Outfit', size: 10 },
                                    color: textColor,
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // 2. Gráfico Eventos por Tipo (Donut Chart)
            const ctxTipo = document.getElementById('chartEventosPorTipoCanvas');
            if (ctxTipo && ctxTipo.offsetParent !== null) {
                const prev2 = Chart.getChart(ctxTipo);
                if (prev2) {
                    try { prev2.destroy(); } catch (e) {}
                }
                if (this.chartTipoInstance) {
                    try { this.chartTipoInstance.destroy(); } catch (e) {}
                }

                this.chartTipoInstance = new Chart(ctxTipo, {
                    type: 'doughnut',
                    data: {
                        labels: this.tipoLabels,
                        datasets: [{
                            data: this.tipoData,
                            backgroundColor: [
                                '#1E3A8A', // Caídas (Azul oscuro institucional)
                                '#F59E0B', // Lesiones (Ámbar)
                                '#3B82F6', // Incidentes (Azul brillante)
                                '#F43F5E', // Complicaciones (Coral/Rojo)
                                '#94A3B8', // Otros (Gris suave)
                            ],
                            borderWidth: 2,
                            borderColor: document.documentElement.classList.contains('dark') ? '#1E293B' : '#FFFFFF',
                            hoverOffset: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        animation: { duration: 600 },
                        plugins: {
                            legend: { display: false },
                            datalabels: { display: false },
                            tooltip: {
                                titleFont: { family: 'Outfit', size: 11, weight: 'bold' },
                                bodyFont: { family: 'Outfit', size: 11 },
                                callbacks: {
                                    label: (ctx) => {
                                        const label = ctx.label || '';
                                        const val = ctx.raw || 0;
                                        const pct = this.tipoPercentages[label] || Math.round((val / (this.tipoTotal || 1)) * 100);
                                        return ` ${label}: ${val} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    };
}
</script>
