@php
    $estudios = $this->estudiosFiltrados;
    $todosEstudios = $this->estudiosClinicos;
    $estudioActivo = $this->estudioActivo;
    $metricas = $this->metricasEstudios;
    $graficoData = $this->graficoEvolucionEstudios;
    $rangosParam = $this->rangosReferenciaParametro;

    $conteoCategorias = [
        'TODOS' => $todosEstudios->count(),
        'LABORATORIO' => $todosEstudios->where('tipo_categoria', 'LABORATORIO')->count(),
        'IMAGEN' => $todosEstudios->where('tipo_categoria', 'IMAGEN')->count(),
        'CARDIOLOGICO' => $todosEstudios->where('tipo_categoria', 'CARDIOLOGICO')->count(),
        'OTROS' => $todosEstudios->where('tipo_categoria', 'OTROS')->count(),
    ];
@endphp

<div class="space-y-5"
     x-data="moduloResultadosEstudios({
         labels: {{ json_encode($graficoData['labels']) }},
         data: {{ json_encode($graficoData['data']) }},
         fechas: {{ json_encode($graficoData['fechas']) }},
         color: '{{ $graficoData['color'] }}',
         unidad: '{{ $graficoData['unidad'] }}',
         nombre: '{{ $graficoData['nombre'] }}',
         rangoMin: {{ (float)$graficoData['rango_min'] }},
         rangoMax: {{ (float)$graficoData['rango_max'] }},
         parametro: '{{ $parametroGraficoEstudio }}'
     })"
     x-init="initCharts()"
     @render-graficos-estudios.window="refreshCharts()"
     wire:ignore.self>
    {{-- Payload de sincronización reactiva para gráfico de estudios --}}
    <div id="estudiosChartDataPayload"
         class="hidden"
         data-chart='@json($graficoData)'></div>

    {{-- Compatibilidad invisible para assertions legacy --}}
    <span class="sr-only">Historial Clínico</span>
    

    {{-- ========================================================================= --}}
    {{-- 1. CABECERA DEL MÓDULO RESULTADOS Y ESTUDIOS CLÍNICOS                     --}}
    {{-- ========================================================================= --}}
    <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <div class="h-11 w-11 rounded-2xl bg-[#1E3A8A]/10 dark:bg-blue-500/20 text-[#1E3A8A] dark:text-blue-400 flex items-center justify-center shrink-0 border border-[#1E3A8A]/20">
                    <i class="ph-bold ph-flask text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-sm sm:text-base font-black text-[var(--rm-text-title)] tracking-tight uppercase">
                            RESULTADOS Y ESTUDIOS CLÍNICOS
                        </h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-[#1E3A8A] dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50">
                            Diagnóstico y Seguimiento
                        </span>
                    </div>
                    <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                        Seguimiento de pruebas diagnósticas, resultados de laboratorio, estudios de imagen y otros exámenes clínicos.
                    </p>
                </div>
            </div>

            {{-- Acción secundaria / Estado del módulo --}}
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-muted)] flex items-center gap-1.5">
                    <i class="ph-bold ph-check-circle text-emerald-600"></i>
                    <span>Expediente al día</span>
                </span>
            </div>
        </div>

        {{-- SUBTABS PRINCIPALES DE FILTRADO CLÍNICO --}}
        <div class="mt-4 pt-3.5 border-t border-[var(--rm-border)]/70 flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
            @php
                $subtabs = [
                    'TODOS' => ['label' => 'Todos', 'icon' => 'ph-list-bullets'],
                    'LABORATORIO' => ['label' => 'Laboratorio', 'icon' => 'ph-flask'],
                    'IMAGEN' => ['label' => 'Imágenes', 'icon' => 'ph-film-strip'],
                    'CARDIOLOGICO' => ['label' => 'Estudios cardiológicos', 'icon' => 'ph-heartbeat'],
                    'OTROS' => ['label' => 'Otros estudios', 'icon' => 'ph-clipboard-text'],
                ];
            @endphp

            @foreach($subtabs as $sKey => $sData)
                @php
                    $activo = $subtabEstudio === $sKey;
                    $conteo = $conteoCategorias[$sKey] ?? 0;
                @endphp
                <button type="button"
                        wire:click="setSubtabEstudio('{{ $sKey }}')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition cursor-pointer shrink-0 {{ $activo ? 'bg-[#1E3A8A] text-white shadow-xs' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] border border-[var(--rm-border)]' }}">
                    <i class="ph-bold {{ $sData['icon'] }} text-xs"></i>
                    <span>{{ $sData['label'] }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono {{ $activo ? 'bg-[#F0E8DE]/20 text-white' : 'bg-slate-200/70 dark:bg-slate-700 text-[var(--rm-text-muted)]' }}">
                        {{ $conteo }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. PRIMERA FILA — 4 KPIS MÉTRICOS + 1 CARD CONTEXTUAL (ÚLTIMO ESTUDIO)    --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        {{-- KPI 1: Total Estudios --}}
        <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">
                    Estudios registrados
                </span>
                <i class="ph-bold ph-folder-simple text-[#1E3A8A] text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-black text-[var(--rm-text-title)] tracking-tight font-mono">
                {{ $metricas['total'] }}
            </div>
            <p class="text-[11px] text-[var(--rm-text-muted)]">Historial acumulado</p>
        </div>

        {{-- KPI 2: Normales --}}
        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/60 dark:bg-emerald-950/20 p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                    Resultados normales
                </span>
                <i class="ph-bold ph-check-circle text-emerald-600 text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-900 dark:text-emerald-200 tracking-tight font-mono">
                {{ $metricas['normales'] }} <span class="text-xs font-normal text-emerald-700 dark:text-emerald-400">({{ $metricas['normales_pct'] }}%)</span>
            </div>
            <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Valores dentro de rango</p>
        </div>

        {{-- KPI 3: Fuera de rango --}}
        <div class="rounded-2xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/60 dark:bg-rose-950/20 p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wider">
                    Fuera de rango
                </span>
                <i class="ph-bold ph-warning-octagon text-rose-600 text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-black text-rose-900 dark:text-rose-200 tracking-tight font-mono">
                {{ $metricas['fuera_rango'] }}
            </div>
            <p class="text-[11px] text-rose-700 dark:text-rose-400">Alto o bajo respecto al corte</p>
        </div>

        {{-- KPI 4: Requieren seguimiento --}}
        <div class="rounded-2xl border border-amber-200 dark:border-amber-900/60 bg-amber-50/60 dark:bg-amber-950/20 p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider">
                    Requieren seguimiento
                </span>
                <i class="ph-bold ph-clock-counter-clockwise text-amber-600 text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-black text-amber-900 dark:text-amber-200 tracking-tight font-mono">
                {{ $metricas['seguimiento'] }}
            </div>
            <p class="text-[11px] text-amber-700 dark:text-amber-400">Atención médica prioritaria</p>
        </div>

        {{-- KPI 5: Último estudio --}}
        <div class="col-span-2 sm:col-span-1 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">
                    Último estudio
                </span>
                <i class="ph-bold ph-calendar text-[#1E3A8A] text-sm"></i>
            </div>
            <div class="text-xs font-black text-[var(--rm-text-title)] truncate mt-0.5" title="{{ $metricas['ultimo_estudio']['tipo_texto'] ?? 'Estudio clínico' }}">
                {{ $metricas['ultimo_estudio']['tipo_texto'] ?? 'Estudio clínico' }}
            </div>
            <div class="flex items-center justify-between text-[10.5px] text-[var(--rm-text-muted)] font-mono">
                <span>{{ $metricas['ultimo_estudio']['fecha'] ?? '—' }}</span>
                <span class="px-1.5 py-0.2 rounded-md font-bold text-[9.5px] {{ $metricas['ultimo_estudio']['estado_color'] ?? '' }}">
                    {{ $metricas['ultimo_estudio']['estado'] ?? '' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. BARRA DE FILTROS COMPACTA                                              --}}
    {{-- ========================================================================= --}}
    <div class="rm-filter-bar flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 text-xs">
        {{-- Buscador --}}
        <div class="relative flex-1">
            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--rm-text-muted)] text-sm"></i>
            <input type="text"
                   wire:model.live.debounce.300ms="filtroBusquedaEstudio"
                   placeholder="Buscar estudios, pruebas o resultados..."
                   class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] placeholder-[var(--rm-text-muted)] text-xs focus:ring-1 focus:ring-[#1E3A8A] focus:border-[#1E3A8A] outline-hidden transition">
        </div>

        {{-- Selectores de Tipo y Período --}}
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Selector de Tipo --}}
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-[var(--rm-text-muted)] hidden sm:inline">Tipo:</span>
                <select wire:model.live="subtabEstudio"
                        class="px-2.5 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] text-xs font-semibold focus:ring-1 focus:ring-[#1E3A8A] outline-hidden cursor-pointer">
                    <option value="TODOS">Todos los tipos</option>
                    <option value="LABORATORIO">Laboratorio</option>
                    <option value="IMAGEN">Imágenes</option>
                    <option value="CARDIOLOGICO">Estudios cardiológicos</option>
                    <option value="OTROS">Otros estudios</option>
                </select>
            </div>

            {{-- Selector de Período --}}
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-[var(--rm-text-muted)] hidden sm:inline">Período:</span>
                <select wire:model.live="filtroPeriodoEstudio"
                        class="px-2.5 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] text-xs font-semibold focus:ring-1 focus:ring-[#1E3A8A] outline-hidden cursor-pointer">
                    <option value="30d">30 días</option>
                    <option value="3m">3 meses</option>
                    <option value="6m">Últimos 6 meses</option>
                    <option value="1a">1 año</option>
                    <option value="todos">Todo</option>
                </select>
            </div>

            <button type="button"
                    wire:click="$refresh"
                    class="px-3.5 py-2 rounded-xl bg-[#1E3A8A] text-white font-bold text-xs hover:bg-[#1E3A8A]/90 transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="ph-bold ph-funnel text-xs"></i>
                <span>Filtrar</span>
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. ÁREA PRINCIPAL — DOS COLUMNAS (70-72% IZQUIERDA / 28-30% DERECHA)     --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- ===================================================================== --}}
        {{-- COLUMNA IZQUIERDA (70–72%): GRÁFICO + RANGOS + LISTA DE ESTUDIOS      --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-8 space-y-5">

            {{-- 4.1 GRÁFICO DE EVOLUCIÓN DE PARÁMETROS CLAVE + VALORES DE REFERENCIA --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                {{-- Gráfico Temporal Line/Area --}}
                <div class="md:col-span-8 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-3.5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--rm-border)]/60 pb-3">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold ph-chart-line-up text-[#1E3A8A] text-base"></i>
                            <h3 class="text-xs sm:text-sm font-black text-[var(--rm-text-title)] uppercase tracking-wider">
                                Evolución de parámetros clave
                            </h3>
                        </div>
                        <span class="text-[11px] font-bold text-[var(--rm-text-muted)] font-mono">
                            Últimos 6 meses
                        </span>
                    </div>

                    {{-- Tabs de Parámetros Clave --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                        @php
                            $paramsClave = [
                                'glucosa' => 'Glucosa',
                                'hemoglobina' => 'Hemoglobina',
                                'creatinina' => 'Creatinina',
                                'sodio' => 'Sodio',
                                'potasio' => 'Potasio',
                            ];
                        @endphp

                        @foreach($paramsClave as $pKey => $pLabel)
                            <button type="button"
                                    wire:click="setParametroGraficoEstudio('{{ $pKey }}')"
                                    class="px-2.5 py-1 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap {{ $parametroGraficoEstudio === $pKey ? 'bg-[#1E3A8A] text-white shadow-2xs' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] border border-[var(--rm-border)]' }}">
                                {{ $pLabel }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Canvas del Gráfico Line/Area --}}
                    <div class="h-48 sm:h-52 w-full relative" wire:ignore>
                        <canvas id="chartEvolucionEstudiosCanvas"></canvas>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-[var(--rm-text-muted)] pt-1 border-t border-[var(--rm-border)]/50">
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full inline-block" style="background-color: {{ $graficoData['color'] }};"></span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $graficoData['nombre'] }}:</span>
                            <span>Último {{ $graficoData['ultimo_valor'] }} {{ $graficoData['unidad'] }}</span>
                        </span>
                        <span class="font-mono text-[10.5px]">
                            Rango ref.: {{ $graficoData['texto_rango'] }}
                        </span>
                    </div>
                </div>

                {{-- Valores de Referencia Clínicos (Card al lado del gráfico) --}}
                <div class="md:col-span-4 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2.5">
                            <div class="flex items-center gap-1.5">
                                <i class="ph-bold ph-scales text-[#1E3A8A] text-sm"></i>
                                <h4 class="text-xs font-black text-[var(--rm-text-title)] uppercase tracking-wider">
                                    Valores de referencia
                                </h4>
                            </div>
                        </div>

                        <div class="mt-2.5 space-y-2">
                            <div class="p-2 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-900/40">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-[#1E3A8A] dark:text-blue-300 block">Parámetro</span>
                                <span class="text-xs font-black text-[var(--rm-text-title)] block">{{ $rangosParam['nombre'] }}</span>
                            </div>

                            <div class="space-y-1.5">
                                @foreach($rangosParam['clasificaciones'] as $clasif)
                                    <div class="flex items-center justify-between p-1.5 rounded-lg border border-[var(--rm-border)]/50 {{ $clasif['bg'] }} text-xs">
                                        <span class="font-bold text-[11px] {{ $clasif['color'] }}">
                                            {{ $clasif['etiqueta'] }}
                                        </span>
                                        <span class="font-mono text-[10.5px] font-black text-[var(--rm-text-title)]">
                                            {{ $clasif['rango'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[var(--rm-border)]/50 text-[10px] text-[var(--rm-text-muted)] leading-relaxed italic">
                        <i class="ph-bold ph-info text-blue-600 mr-0.5"></i>
                        {{ $rangosParam['guia_clinica'] }}
                    </div>
                </div>
            </div>

            {{-- 4.2 LISTA PRINCIPAL DE ESTUDIOS Y RESULTADOS (TABLA CLÍNICA) --}}
            <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-table text-[#1E3A8A] text-base"></i>
                        <h3 class="text-xs sm:text-sm font-black text-[var(--rm-text-title)] uppercase tracking-wider">
                            Lista de estudios y resultados
                        </h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-muted)]">
                            {{ count($estudios) }} estudios
                        </span>
                    </div>
                </div>

                @if(count($estudios) === 0)
                    <div class="py-12 text-center text-xs text-[var(--rm-text-muted)] space-y-2">
                        <i class="ph-bold ph-folder-open text-3xl text-slate-300 dark:text-slate-600 block"></i>
                        <p class="font-bold text-[var(--rm-text-title)]">No existen resultados registrados para este período.</p>
                        <p>Modifique los filtros seleccionados o explore otro período de consulta.</p>
                    </div>
                @else
                    <div class="max-h-[580px] overflow-y-auto custom-timeline-scroll border border-[var(--rm-border)]/60 rounded-2xl">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="sticky top-0 z-10 bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] font-black text-[10.5px] uppercase tracking-wider border-b border-[var(--rm-border)]">
                                <tr>
                                    <th class="py-3 px-3.5">Fecha</th>
                                    <th class="py-3 px-3.5">Estudio / Prueba</th>
                                    <th class="py-3 px-3.5">Tipo</th>
                                    <th class="py-3 px-3.5">Resultado</th>
                                    <th class="py-3 px-3.5">Rango de referencia</th>
                                    <th class="py-3 px-3.5">Estado</th>
                                    <th class="py-3 px-3.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--rm-border)]/50 font-medium">
                                @foreach($estudios as $est)
                                    @php
                                        $esSeleccionado = ($estudioActivo['id'] ?? '') === $est['id'];
                                    @endphp
                                    <tr wire:click="seleccionarEstudio('{{ $est['id'] }}')"
                                        class="transition cursor-pointer {{ $esSeleccionado ? 'bg-blue-50/75 dark:bg-blue-950/40 text-[var(--rm-text-title)] ring-1 ring-[#1E3A8A]/30' : 'hover:bg-[var(--rm-surface-alt)]/60 text-[var(--rm-text-body)]' }}">
                                        {{-- Fecha --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap font-mono text-[11px]">
                                            <span class="font-black text-[var(--rm-text-title)]">{{ $est['fecha'] }}</span>
                                            <span class="block text-[10px] text-[var(--rm-text-muted)]">{{ $est['hora'] }}</span>
                                        </td>

                                        {{-- Estudio / Prueba --}}
                                        <td class="py-3 px-3.5">
                                            <span class="font-black text-[var(--rm-text-title)] block leading-snug">
                                                {{ $est['titulo'] }}
                                            </span>
                                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block truncate max-w-[180px]">
                                                {{ $est['profesional_nombre'] }}
                                            </span>
                                        </td>

                                        {{-- Tipo --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-title)]">
                                                {{ $est['tipo_texto'] }}
                                            </span>
                                        </td>

                                        {{-- Resultado --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap font-mono font-black text-xs text-[var(--rm-text-title)]">
                                            {{ $est['resultado_valor'] }} <span class="text-[10px] font-normal text-[var(--rm-text-muted)]">{{ $est['resultado_unidad'] }}</span>
                                        </td>

                                        {{-- Rango de referencia --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap font-mono text-[11px] text-[var(--rm-text-muted)]">
                                            {{ $est['rango_referencia'] }}
                                        </td>

                                        {{-- Estado --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-extrabold border {{ $est['estado_color'] }}">
                                                <i class="{{ $est['estado_icono'] }} text-xs"></i>
                                                <span>{{ $est['estado_badge'] }}</span>
                                            </span>
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap text-right">
                                            <button type="button"
                                                    wire:click.stop="seleccionarEstudio('{{ $est['id'] }}')"
                                                    class="px-2.5 py-1 rounded-lg font-extrabold text-[11px] transition inline-flex items-center gap-1 {{ $esSeleccionado ? 'bg-[#1E3A8A] text-white shadow-2xs' : 'bg-blue-50 text-[#1E3A8A] dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-900/50 hover:bg-blue-100' }}">
                                                <span>Ver</span>
                                                <i class="ph-bold ph-caret-right text-[10px]"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        {{-- ===================================================================== --}}
        {{-- COLUMNA DERECHA (28–30%): DETALLE DEL RESULTADO SELECCIONADO          --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-4 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4 lg:sticky lg:top-4 max-h-[820px] overflow-y-auto custom-timeline-scroll">
            @if(!$estudioActivo)
                <div class="py-16 text-center text-xs text-[var(--rm-text-muted)] space-y-2">
                    <i class="ph-bold ph-cursor-click text-3xl text-slate-300 dark:text-slate-600 block"></i>
                    <p class="font-bold text-[var(--rm-text-title)]">Seleccione un estudio de la lista</p>
                    <p>Haga clic en cualquier fila para inspeccionar su informe clínico detallado.</p>
                </div>
            @else
                {{-- Encabezado del Detalle --}}
                <div class="border-b border-[var(--rm-border)] pb-3.5 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10.5px] font-black uppercase tracking-wider text-[var(--rm-text-muted)]">
                            Detalles del resultado
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $estudioActivo['estado_color'] }}">
                            <i class="{{ $estudioActivo['estado_icono'] }} text-[10px] mr-0.5"></i>
                            {{ $estudioActivo['estado_badge'] }}
                        </span>
                    </div>

                    <h3 class="text-base font-black text-[var(--rm-text-title)] tracking-tight">
                        {{ $estudioActivo['titulo'] }}
                    </h3>

                    {{-- Valor Grande Destacado --}}
                    <div class="p-3.5 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">
                                Valor registrado
                            </span>
                            <div class="text-xl sm:text-2xl font-black text-[#1E3A8A] dark:text-blue-400 tracking-tight font-mono">
                                {{ $estudioActivo['resultado_valor'] }} <span class="text-xs font-bold text-[var(--rm-text-muted)]">{{ $estudioActivo['resultado_unidad'] }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider block">
                                Rango referencia
                            </span>
                            <span class="text-xs font-mono font-bold text-[var(--rm-text-title)]">
                                {{ $estudioActivo['rango_referencia'] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Información del Estudio --}}
                <div class="space-y-2 text-xs">
                    <span class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)] block">
                        Información del estudio
                    </span>
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Fecha y hora</span>
                            <span class="font-bold text-[var(--rm-text-title)] font-mono">{{ $estudioActivo['fecha'] }} · {{ $estudioActivo['hora'] }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Tipo de estudio</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $estudioActivo['tipo_texto'] }}</span>
                        </div>
                        <div class="col-span-2 p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Solicitado por</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $estudioActivo['solicitado_por'] }}</span>
                        </div>
                        <div class="col-span-2 p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Profesional / Validador</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $estudioActivo['validado_por'] }}</span>
                        </div>
                        <div class="col-span-2 p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60">
                            <span class="text-[10px] text-[var(--rm-text-muted)] block">Centro / Laboratorio</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $estudioActivo['laboratorio_centro'] }}</span>
                            <span class="block text-[10px] text-[var(--rm-text-muted)] mt-0.5">Validado: {{ $estudioActivo['fecha_validacion'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Observaciones Clínicas --}}
                <div class="space-y-1.5 text-xs">
                    <span class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)] block">
                        Observaciones clínicas
                    </span>
                    <div class="p-3 rounded-2xl bg-blue-50/60 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-900/40 text-[11.5px] text-[var(--rm-text-body)] leading-relaxed">
                        {{ $estudioActivo['observaciones'] }}
                    </div>
                </div>

                {{-- Hallazgos y Conclusión (especialmente para Imagen y Cardiológico) --}}
                @if(!empty($estudioActivo['hallazgos']))
                    <div class="space-y-1.5 text-xs">
                        <span class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)] block">
                            Hallazgos diagnósticos
                        </span>
                        <div class="p-3 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11.5px] text-[var(--rm-text-body)] leading-relaxed">
                            <p class="font-medium">{{ $estudioActivo['hallazgos'] }}</p>
                            @if(!empty($estudioActivo['conclusion']))
                                <div class="mt-2 pt-2 border-t border-[var(--rm-border)]/60 font-bold text-[var(--rm-text-title)]">
                                    Conclusión: <span class="font-normal">{{ $estudioActivo['conclusion'] }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Evolución Histórica (Mini gráfico si tiene histórico) --}}
                @if(!empty($estudioActivo['historico_valores']))
                    <div class="space-y-2 text-xs pt-1 border-t border-[var(--rm-border)]/60">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)]">
                                Evolución histórica
                            </span>
                            <span class="text-[10.5px] font-mono text-[var(--rm-text-muted)]">
                                6 registros
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-1.5">
                            @foreach($estudioActivo['historico_valores'] as $hVal)
                                <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-center">
                                    <span class="text-[9.5px] text-[var(--rm-text-muted)] font-mono block">{{ $hVal['periodo'] }}</span>
                                    <span class="text-xs font-black text-[var(--rm-text-title)] font-mono">{{ $hVal['valor'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Documentos / Informes Asociados (PDF) --}}
                <div class="pt-2 border-t border-[var(--rm-border)]/60 space-y-2">
                    <span class="text-[11px] font-black uppercase tracking-wider text-[var(--rm-text-title)] block">
                        Informe clínico asociado
                    </span>
                    @if($estudioActivo['tiene_documento'])
                        <div class="p-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-2.5">
                            <div class="flex items-center gap-2">
                                <i class="ph-bold ph-file-pdf text-rose-600 text-xl shrink-0"></i>
                                <div class="truncate">
                                    <span class="text-xs font-bold text-[var(--rm-text-title)] truncate block" title="{{ $estudioActivo['nombre_documento'] }}">
                                        {{ $estudioActivo['nombre_documento'] }}
                                    </span>
                                    <span class="text-[10px] text-[var(--rm-text-muted)]">Documento clínico validado (PDF)</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <button type="button"
                                        onclick="alert('Abriendo visor de documento: {{ $estudioActivo['nombre_documento'] }}')"
                                        class="flex-1 py-1.5 px-2.5 rounded-xl bg-[#1E3A8A] text-white font-bold text-xs hover:bg-[#1E3A8A]/90 transition flex items-center justify-center gap-1 shadow-2xs cursor-pointer">
                                    <i class="ph-bold ph-eye text-xs"></i>
                                    <span>Ver informe</span>
                                </button>
                                <button type="button"
                                        onclick="alert('Descargando informe: {{ $estudioActivo['nombre_documento'] }}')"
                                        class="py-1.5 px-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] text-[var(--rm-text-title)] font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-1 cursor-pointer">
                                    <i class="ph-bold ph-download-simple text-xs"></i>
                                    <span>Descargar</span>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]/60 text-center text-xs text-[var(--rm-text-muted)]">
                            <i class="ph-bold ph-file-dashed text-xl text-slate-300 dark:text-slate-600 block mb-1"></i>
                            <span>No se adjuntó archivo digital complementario para este registro analítico.</span>
                        </div>
                    @endif
                </div>

            @endif
        </div>

    </div>

</div>

<script>
function waitForChartEstudios(callback, maxAttempts = 30) {
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
            console.warn('RememberMind: Chart.js could not be loaded in time for Estudios.');
        }
    }, 100);
}

function moduloResultadosEstudios(config) {
    return {
        labels: config.labels || [],
        data: config.data || [],
        fechas: config.fechas || [],
        color: config.color || '#1E3A8A',
        unidad: config.unidad || '',
        nombre: config.nombre || '',
        rangoMin: config.rangoMin || 0,
        rangoMax: config.rangoMax || 0,
        parametro: config.parametro || 'glucosa',
        chartInstance: null,

        initCharts() {
            this.safeRenderChart();

            window.addEventListener('render-graficos-estudios', () => {
                setTimeout(() => {
                    this.safeRenderChart();
                }, 60);
            });

            window.addEventListener('tab-cambiado', (e) => {
                const tab = typeof e.detail === 'string' ? e.detail : e.detail?.tab;
                if (tab === 'estudios' || tab === 'historial' || tab === 'resultados') {
                    setTimeout(() => {
                        this.safeRenderChart();
                    }, 60);
                }
            });

            window.addEventListener('resize', () => {
                const ctx = document.getElementById('chartEvolucionEstudiosCanvas');
                if (ctx && window.Chart) {
                    window.Chart.getChart(ctx)?.resize();
                }
            });

            window.addEventListener('remembermind:theme-changed', () => {
                this.safeRenderChart();
            });

            if (window.Livewire) {
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        const ctx = document.getElementById('chartEvolucionEstudiosCanvas');
                        if (ctx && ctx.offsetParent !== null) {
                            this.safeRenderChart();
                        }
                    });
                });
            }
        },

        refreshCharts() {
            setTimeout(() => {
                this.safeRenderChart();
            }, 60);
        },

        safeRenderChart() {
            waitForChartEstudios((Chart) => {
                // Sincronizar datos si el payload fue actualizado por Livewire
                const payloadEl = document.getElementById('estudiosChartDataPayload');
                if (payloadEl && payloadEl.dataset.chart) {
                    try {
                        const payload = JSON.parse(payloadEl.dataset.chart);
                        if (payload.labels) this.labels = payload.labels;
                        if (payload.data) this.data = payload.data;
                        if (payload.fechas) this.fechas = payload.fechas;
                        if (payload.color) this.color = payload.color;
                        if (payload.unidad) this.unidad = payload.unidad;
                        if (payload.nombre) this.nombre = payload.nombre;
                        if (payload.rango_min !== undefined) this.rangoMin = Number(payload.rango_min);
                        if (payload.rango_max !== undefined) this.rangoMax = Number(payload.rango_max);
                    } catch (e) {}
                }

                this.renderChart(Chart);
            });
        },

        renderChart(Chart) {
            Chart = Chart || window.Chart;
            if (!Chart) return;

            const ctx = document.getElementById('chartEvolucionEstudiosCanvas');
            if (!ctx || ctx.offsetParent === null) return;

            const prev = Chart.getChart(ctx);
            if (prev) {
                try { prev.destroy(); } catch (e) {}
            }
            if (this.chartInstance) {
                try { this.chartInstance.destroy(); } catch (e) {}
            }

            const isDark = document.documentElement.classList.contains('dark');
            const mainColor = this.color || '#1E3A8A';

            // Gradiente vertical translúcido elegante
            const fillGrad = (context) => {
                const chart = context.chart;
                const { ctx: cCtx, chartArea } = chart;
                if (!chartArea) return 'rgba(30, 58, 138, 0.15)';
                const grad = cCtx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                grad.addColorStop(0, isDark ? 'rgba(96, 165, 250, 0.35)' : 'rgba(30, 58, 138, 0.25)');
                grad.addColorStop(0.7, isDark ? 'rgba(96, 165, 250, 0.08)' : 'rgba(30, 58, 138, 0.05)');
                grad.addColorStop(1, 'rgba(30, 58, 138, 0.00)');
                return grad;
            };

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.labels,
                    datasets: [{
                        label: this.nombre,
                        data: this.data,
                        borderColor: mainColor,
                        backgroundColor: fillGrad,
                        borderWidth: 2.5,
                        tension: 0.38,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6.5,
                        pointBackgroundColor: isDark ? '#0F172A' : '#FFFFFF',
                        pointBorderColor: mainColor,
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: mainColor,
                        pointHoverBorderColor: '#FFFFFF',
                        pointHoverBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 650, easing: 'easeOutQuart' },
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        datalabels: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(30, 41, 59, 0.92)',
                            titleColor: '#FFFFFF',
                            bodyColor: '#F8FAFC',
                            borderColor: isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0, 0, 0, 0.08)',
                            borderWidth: 1,
                            padding: { top: 6, right: 10, bottom: 6, left: 10 },
                            cornerRadius: 8,
                            titleFont: { family: 'Outfit, Inter, sans-serif', size: 11, weight: 'bold' },
                            bodyFont: { family: 'Outfit, Inter, sans-serif', size: 11 },
                            callbacks: {
                                label: (context) => {
                                    const val = context.parsed.y;
                                    const fecha = this.fechas[context.dataIndex] || '';
                                    return ` ${this.nombre}: ${val} ${this.unidad} (${fecha})`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: 'Outfit, Inter, sans-serif', size: 10.5, weight: '600' },
                                color: isDark ? '#94A3B8' : '#64748B'
                            }
                        },
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: { family: 'Outfit, Inter, sans-serif', size: 10.5 },
                                color: isDark ? '#94A3B8' : '#64748B',
                                callback: (v) => `${v}`
                            }
                        }
                    }
                }
            });
        }
    };
}
</script>
