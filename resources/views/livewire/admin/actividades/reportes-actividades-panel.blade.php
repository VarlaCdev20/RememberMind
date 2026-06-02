@php
    $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#D9A05B]/20';
    $labelCls = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1';

    $ne = fn(string $e) => \App\Models\ActividadAdulto::normalizarEstado($e);
@endphp

<div class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8"
     x-data>
<div class="mx-auto max-w-7xl space-y-5">

{{-- ══ CABECERA ══════════════════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
    <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
    <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
                <i class="ph-bold ph-chart-bar text-sm"></i>
                CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — Reportes
            </span>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">Reportes de actividades</h1>
            <p class="mt-1.5 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
                Evidencia institucional de actividades, participación, asistencia y evaluación.
                @if($hasFiltros)<span class="inline-flex items-center gap-1 rounded-full bg-estado-advertenciaBg px-2 py-0.5 text-[9px] font-bold text-estado-advertencia"><i class="ph-bold ph-funnel text-[9px]"></i> Filtros activos</span>@endif
            </p>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ $urlPreview }}"
                class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-3.5 py-2 text-xs font-bold text-apoyo transition hover:text-titulo">
                <i class="ph-bold ph-eye text-sm"></i> Vista previa
            </a>
            @can('reportes.exportar_pdf')
            <a href="{{ $urlPdf }}" x-data
                @click.prevent="{{ $stats['total'] === 0 ? 'window.SwalAmandita?.fire({icon:\'warning\',title:\'Sin datos para exportar\'})' : 'window.location.href=\'' . $urlPdf . '\'' }}"
                class="inline-flex items-center gap-2 rounded-xl border border-borde-focus bg-estado-peligroBg px-3.5 py-2 text-xs font-bold text-boton-acento transition hover:bg-estado-peligroBg">
                <i class="ph-bold ph-file-pdf text-sm"></i> PDF
            </a>
            <a href="{{ $urlExcel }}" x-data
                @click.prevent="{{ $stats['total'] === 0 ? 'window.SwalAmandita?.fire({icon:\'warning\',title:\'Sin datos para exportar\'})' : 'window.location.href=\'' . $urlExcel . '\'' }}"
                class="inline-flex items-center gap-2 rounded-xl border border-estado-exitoBorde bg-estado-exitoBg px-3.5 py-2 text-xs font-bold text-estado-exito transition hover:bg-estado-exitoBg">
                <i class="ph-bold ph-file-xls text-sm"></i> Excel
            </a>
            @endcan
            @if($hasFiltros)
            <button wire:click="limpiarFiltros"
                class="inline-flex items-center gap-1.5 rounded-xl border border-borde-suave bg-fondo-app px-3 py-2 text-xs font-bold text-apoyo hover:text-boton-acento transition">
                <i class="ph-bold ph-x text-xs"></i> Limpiar
            </button>
            @endif
        </div>
    </div>
</section>

{{-- ══ FILTROS ══════════════════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm" x-data="{ open: true }">
    <button @click="open = !open"
        class="flex w-full items-center justify-between border-b border-borde-suave px-5 py-3 text-left">
        <div class="flex items-center gap-2">
            <i class="ph-bold ph-funnel text-apoyo text-sm"></i>
            <span class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">Filtros de reporte</span>
            @if($hasFiltros)
            <span class="rounded-full bg-estado-advertenciaBg px-2 py-0.5 text-[9px] font-bold text-estado-advertencia">Activos</span>
            @endif
        </div>
        <i class="ph-bold ph-caret-down text-xs text-apoyo transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
    </button>

    <div x-show="open" x-transition class="p-4">
        {{-- Fila 1: fechas + tipo + categoría + estado --}}
        <div class="flex flex-wrap gap-3 mb-3">
            <div class="min-w-[120px] flex-1">
                <label class="{{ $labelCls }}">Desde</label>
                <input wire:model.live="fechaDesde" type="date" class="{{ $inputCls }}" />
            </div>
            <div class="min-w-[120px] flex-1">
                <label class="{{ $labelCls }}">Hasta</label>
                <input wire:model.live="fechaHasta" type="date" class="{{ $inputCls }}" />
            </div>
            <div class="min-w-[160px] flex-1">
                <label class="{{ $labelCls }}">Tipo de actividad</label>
                <select wire:model.live="filtroTipo" class="{{ $inputCls }}">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $t)
                        <option value="{{ $t->cod_tipo_act }}">{{ $t->tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px] flex-1">
                <label class="{{ $labelCls }}">Categoría</label>
                <select wire:model.live="filtroCategoria" class="{{ $inputCls }}">
                    <option value="">Todas</option>
                    @foreach($categorias as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[130px] flex-1">
                <label class="{{ $labelCls }}">Estado</label>
                <select wire:model.live="filtroEstado" class="{{ $inputCls }}">
                    <option value="">Todos</option>
                    <option value="PROGRAMADA">Programada</option>
                    <option value="EN_CURSO">En curso</option>
                    <option value="REALIZADA">Realizada</option>
                    <option value="EVALUADA">Evaluada</option>
                    <option value="CANCELADA">Cancelada</option>
                    <option value="REPROGRAMADA">Reprogramada</option>
                </select>
            </div>
        </div>

        {{-- Fila 2: asistencia + nivel + buscar + checkboxes --}}
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[140px] flex-1">
                <label class="{{ $labelCls }}">Asistencia</label>
                <select wire:model.live="filtroAsistencia" class="{{ $inputCls }}">
                    <option value="">Todas</option>
                    <option value="ASISTIO">Asistió</option>
                    <option value="FALTO">Faltó</option>
                    <option value="JUSTIFICADO">Justificado</option>
                    <option value="INSCRITO">Inscrito (pendiente)</option>
                </select>
            </div>
            <div class="min-w-[130px] flex-1">
                <label class="{{ $labelCls }}">Nivel participación</label>
                <select wire:model.live="filtroNivel" class="{{ $inputCls }}">
                    <option value="">Todos</option>
                    <option value="ALTA">Alta</option>
                    <option value="MEDIA">Media</option>
                    <option value="BAJA">Baja</option>
                    <option value="NO_APLICA">No aplica</option>
                </select>
            </div>
            <div class="min-w-[180px] flex-1">
                <label class="{{ $labelCls }}">Buscar</label>
                <div class="relative">
                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-apoyo"></i>
                    <input wire:model.live.debounce.400ms="buscar" type="text"
                        placeholder="Nombre, tipo, lugar..."
                        class="{{ $inputCls }} pl-8" />
                </div>
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-2">
                <label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    <input wire:model.live="soloConSeguimiento" type="checkbox" class="h-4 w-4 rounded text-boton-acento" />
                    <span>Con seguimiento</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    <input wire:model.live="soloConIncidencias" type="checkbox" class="h-4 w-4 rounded text-boton-acento" />
                    <span>Con incidencias</span>
                </label>
                <label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                    <input wire:model.live="soloEvaluadas" type="checkbox" class="h-4 w-4 rounded text-boton-acento" />
                    <span>Solo evaluadas</span>
                </label>
            </div>
        </div>

        <p class="mt-2 text-[10px] font-bold text-apoyo">
            <i class="ph-bold ph-info mr-1"></i>
            Período: <strong>{{ $stats['periodo'] }}</strong>
        </p>
    </div>
</section>

{{-- ══ KPIs ACTIVIDADES (12 cards) ════════════════════════════════════════ --}}
<div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6" wire:loading.class="opacity-50">
    @php
        $kpiCards = [
            ['label'=>'Total',        'valor'=>$stats['total'],           'icono'=>'ph-calendar-blank', 'cls'=>'text-titulo', 'sub'=>$stats['periodo']],
            ['label'=>'Programadas',  'valor'=>$stats['programadas'],     'icono'=>'ph-clock',           'cls'=>'text-estado-advertencia', 'sub'=>'Pendientes'],
            ['label'=>'Realizadas',   'valor'=>$stats['realizadas'],      'icono'=>'ph-check-circle',    'cls'=>'text-estado-exito', 'sub'=>'Completadas'],
            ['label'=>'Evaluadas',    'valor'=>$stats['evaluadas'],       'icono'=>'ph-clipboard-text',  'cls'=>'text-[#5A8A70]', 'sub'=>'Con cierre'],
            ['label'=>'Canceladas',   'valor'=>$stats['canceladas'],      'icono'=>'ph-x-circle',        'cls'=>'text-boton-acento', 'sub'=>'Anuladas'],
            ['label'=>'Pend. eval.',  'valor'=>$stats['pendientes_evaluacion'],'icono'=>'ph-warning-circle','cls'=>'text-estado-advertencia', 'sub'=>'Sin evaluación final'],
            ['label'=>'Inscritos',    'valor'=>$kpisPartic['total_inscritos'],'icono'=>'ph-users-four',  'cls'=>'text-titulo', 'sub'=>'Total participantes'],
            ['label'=>'Asistencias',  'valor'=>$kpisPartic['asistencias'], 'icono'=>'ph-check-square',  'cls'=>'text-estado-exito', 'sub'=>'Registradas'],
            ['label'=>'Tasa asist.',  'valor'=>$kpisPartic['tasa_asistencia'].'%','icono'=>'ph-trend-up','cls'=>'text-estado-exito', 'sub'=>'Sobre inscritos'],
            ['label'=>'Faltas',       'valor'=>$kpisPartic['faltas'],      'icono'=>'ph-user-minus',     'cls'=>'text-boton-acento', 'sub'=>'Registradas'],
            ['label'=>'Seguimiento',  'valor'=>$kpisPartic['seguimiento'], 'icono'=>'ph-warning',        'cls'=>'text-boton-acento', 'sub'=>'Requieren atención'],
            ['label'=>'Incidencias',  'valor'=>$stats['con_incidencias'], 'icono'=>'ph-warning-octagon', 'cls'=>'text-estado-advertencia', 'sub'=>'Actividades afectadas'],
        ];
    @endphp
    @foreach($kpiCards as $k)
    <article class="flex flex-col justify-between overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-3.5 shadow-sm">
        <div class="flex items-start justify-between gap-1.5">
            <p class="text-[9px] font-bold uppercase tracking-[0.12em] text-apoyo">{{ $k['label'] }}</p>
            <i class="ph-bold {{ $k['icono'] }} text-base {{ $k['cls'] }} shrink-0"></i>
        </div>
        <p class="mt-2 text-2xl font-black {{ $k['cls'] }}">{{ $k['valor'] }}</p>
        <p class="mt-0.5 text-[9px] font-bold text-apoyo truncate">{{ $k['sub'] }}</p>
    </article>
    @endforeach
</div>

{{-- ══ KPIs PARTICIPACIÓN (barra visual) ═════════════════════════════════ --}}
@if($kpisPartic['total_inscritos'] > 0)
@php
    $totalPart  = max(1, $kpisPartic['total_inscritos']);
    $pctAsistio = round(($kpisPartic['asistencias'] / $totalPart) * 100);
    $pctFalto   = round(($kpisPartic['faltas'] / $totalPart) * 100);
    $pctJust    = round(($kpisPartic['justificados'] / $totalPart) * 100);
    $pctNivelAlta = ($kpisPartic['nivel_alta'] + $kpisPartic['nivel_media'] + $kpisPartic['nivel_baja']) > 0
        ? round(($kpisPartic['nivel_alta'] / max(1, $kpisPartic['nivel_alta'] + $kpisPartic['nivel_media'] + $kpisPartic['nivel_baja'])) * 100)
        : 0;
@endphp
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="border-b border-borde-suave px-5 py-3.5">
        <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">
            <i class="ph-bold ph-users mr-2 text-apoyo"></i>Distribución de participación
        </h2>
    </div>
    <div class="p-5 space-y-3">
        @foreach([
            ['label'=>'Asistió', 'val'=>$pctAsistio, 'color'=>'bg-estado-exito', 'count'=>$kpisPartic['asistencias']],
            ['label'=>'Faltó',   'val'=>$pctFalto,   'color'=>'bg-boton-acento', 'count'=>$kpisPartic['faltas']],
            ['label'=>'Justif.', 'val'=>$pctJust,    'color'=>'bg-estado-advertencia', 'count'=>$kpisPartic['justificados']],
        ] as $barra)
        <div class="flex items-center gap-3">
            <span class="w-16 shrink-0 text-[11px] font-bold text-apoyo text-right">{{ $barra['label'] }}</span>
            <div class="flex-1 h-2.5 overflow-hidden rounded-full bg-fondo-panel">
                <div class="h-2.5 rounded-full {{ $barra['color'] }} transition-all duration-700"
                     style="width: {{ $barra['val'] }}%"></div>
            </div>
            <span class="w-20 shrink-0 text-[11px] font-bold text-titulo">{{ $barra['val'] }}% ({{ $barra['count'] }})</span>
        </div>
        @endforeach
        <div class="grid grid-cols-3 gap-3 mt-3 pt-3 border-t border-borde-suave text-center">
            <div>
                <p class="text-[10px] font-bold text-estado-exito">{{ $kpisPartic['nivel_alta'] }}</p>
                <p class="text-[9px] text-apoyo">Participación alta</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-estado-advertencia">{{ $kpisPartic['nivel_media'] }}</p>
                <p class="text-[9px] text-apoyo">Participación media</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-boton-acento">{{ $kpisPartic['nivel_baja'] }}</p>
                <p class="text-[9px] text-apoyo">Participación baja</p>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ══ GRÁFICOS CHART.JS ══════════════════════════════════════════════════ --}}
{{-- Contenedor wire:ignore para que Livewire no destruya los canvas --}}
<div wire:ignore class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

    {{-- A. Por estado --}}
    <div class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
        <div class="border-b border-borde-suave px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">
                <i class="ph-bold ph-chart-pie mr-1 text-apoyo"></i> Por estado
            </p>
        </div>
        <div class="p-4 flex items-center justify-center" style="height:220px">
            <canvas id="chart-estado" style="max-height:200px"></canvas>
        </div>
    </div>

    {{-- B. Por tipo --}}
    <div class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
        <div class="border-b border-borde-suave px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">
                <i class="ph-bold ph-chart-bar mr-1 text-apoyo"></i> Por tipo de actividad
            </p>
        </div>
        <div class="p-4 flex items-center justify-center" style="height:220px">
            <canvas id="chart-tipo" style="max-height:200px"></canvas>
        </div>
    </div>

    {{-- C. Por mes --}}
    <div class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
        <div class="border-b border-borde-suave px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">
                <i class="ph-bold ph-trend-up mr-1 text-apoyo"></i> Actividades por mes
            </p>
        </div>
        <div class="p-4 flex items-center justify-center" style="height:220px">
            <canvas id="chart-mes" style="max-height:200px"></canvas>
        </div>
    </div>

    {{-- D. Asistencia por mes --}}
    <div class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
        <div class="border-b border-borde-suave px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">
                <i class="ph-bold ph-users-four mr-1 text-apoyo"></i> Asistencia mensual
            </p>
        </div>
        <div class="p-4 flex items-center justify-center" style="height:220px">
            <canvas id="chart-asistencia" style="max-height:200px"></canvas>
        </div>
    </div>

    {{-- E. Nivel de participación --}}
    <div class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
        <div class="border-b border-borde-suave px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">
                <i class="ph-bold ph-star mr-1 text-apoyo"></i> Nivel de participación
            </p>
        </div>
        <div class="p-4 flex items-center justify-center" style="height:220px">
            <canvas id="chart-nivel" style="max-height:200px"></canvas>
        </div>
    </div>

    {{-- F. Seguimiento por mes --}}
    <div class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
        <div class="border-b border-borde-suave px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-titulo">
                <i class="ph-bold ph-warning-circle mr-1 text-apoyo"></i> Seguimiento institucional
            </p>
        </div>
        <div class="p-4 flex items-center justify-center" style="height:220px">
            <canvas id="chart-seguimiento" style="max-height:200px"></canvas>
        </div>
    </div>
</div>

{{-- ══ TABLA ENRIQUECIDA ══════════════════════════════════════════════════ --}}
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="flex items-center justify-between border-b border-borde-suave px-5 py-3.5">
        <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">
            <i class="ph-bold ph-list-bullets mr-2 text-apoyo"></i>Registro detallado
        </h2>
        <span class="text-[10px] font-bold text-apoyo">Últimos {{ $preview->count() }} registros filtrados</span>
    </div>

    @if($preview->isEmpty())
    <div class="flex flex-col items-center gap-4 py-14 text-center">
        <i class="ph-bold ph-calendar-blank text-4xl text-apoyo"></i>
        <div>
            <p class="text-sm font-bold text-titulo">Sin datos con los filtros actuales</p>
            @if($hasFiltros)
            <p class="text-xs text-apoyo mt-1">Intente ampliar el rango de fechas o limpiar los filtros.</p>
            <button wire:click="limpiarFiltros" class="mt-2 text-xs font-bold text-boton-acento hover:underline">Limpiar filtros</button>
            @endif
        </div>
    </div>
    @else
    <div class="overflow-x-auto" wire:loading.class="opacity-50">
        <table class="w-full min-w-[1100px] text-[11px]">
            <thead>
                <tr class="border-b border-borde-suave bg-fondo-panel">
                    @foreach(['Actividad / Tipo', 'Categoría', 'Fecha', 'Lugar', 'Estado', 'Particip.', 'Asistió', 'Faltó', 'Justif.', 'Seguim.', 'Cumplim.', 'Incidencias'] as $h)
                    <th class="px-3 pb-2.5 pt-3 text-left font-bold uppercase tracking-[0.10em] text-apoyo">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-[#C7B5A3]/20">
                @foreach($preview as $row)
                @php
                    $est = $ne(strtoupper($row->estado ?? ''));
                    $hasIncid = ! empty($row->incidencias);
                @endphp
                <tr class="hover:bg-fondo-panel transition {{ $row->seguimiento > 0 ? 'bg-estado-peligroBg/10' : '' }}">
                    <td class="px-3 py-2.5">
                        <p class="font-black text-titulo truncate max-w-[160px]">
                            {{ $row->nombre ?? $row->tipo_actividad ?? '—' }}
                        </p>
                        @if($row->nombre)
                        <p class="text-[10px] text-apoyo">{{ $row->tipo_actividad }}</p>
                        @endif
                        @if($row->adulto)
                        <p class="text-[10px] text-apoyo italic">{{ $row->adulto }}</p>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-apoyo">{{ $row->categoria ?? '—' }}</td>
                    <td class="px-3 py-2.5 text-apoyo whitespace-nowrap">
                        {{ $row->fecha ? \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') : '—' }}
                        @if($row->hora)<p class="text-[10px]">{{ substr($row->hora, 0, 5) }}</p>@endif
                    </td>
                    <td class="px-3 py-2.5 text-apoyo max-w-[100px] truncate">{{ $row->lugar ?? '—' }}</td>
                    <td class="px-3 py-2.5">
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[9px] font-bold {{ $est['clase'] }}">
                            {{ $est['etiqueta'] }}
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-center font-black text-titulo">{{ $row->total_participantes }}</td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="font-bold {{ $row->asistieron > 0 ? 'text-estado-exito' : 'text-apoyo' }}">{{ $row->asistieron }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="font-bold {{ $row->faltaron > 0 ? 'text-boton-acento' : 'text-apoyo' }}">{{ $row->faltaron }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="font-bold {{ $row->justificados > 0 ? 'text-estado-advertencia' : 'text-apoyo' }}">{{ $row->justificados }}</span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @if($row->seguimiento > 0)
                        <span class="inline-flex items-center gap-1 rounded-full border border-borde-focus bg-estado-peligroBg px-1.5 py-0.5 text-[9px] font-bold text-boton-acento">
                            <i class="ph-bold ph-bell text-[9px]"></i> {{ $row->seguimiento }}
                        </span>
                        @else
                        <span class="text-apoyo">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5">
                        @if($row->nivel_cumplimiento)
                        <span class="text-[9px] font-bold {{ $row->nivel_cumplimiento === 'ALTO' ? 'text-estado-exito' : ($row->nivel_cumplimiento === 'BAJO' ? 'text-boton-acento' : 'text-estado-advertencia') }}">
                            {{ $row->nivel_cumplimiento }}
                        </span>
                        @else
                        <span class="text-apoyo">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5">
                        @if($hasIncid)
                        <span class="inline-flex items-center gap-1 text-[9px] font-bold text-estado-advertencia" title="{{ $row->incidencias }}">
                            <i class="ph-bold ph-warning-circle text-[9px]"></i>
                            {{ mb_strimwidth($row->incidencias, 0, 25, '…') }}
                        </span>
                        @else
                        <span class="text-apoyo">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</section>

{{-- ══ DASHBOARD INSTITUCIONAL ═══════════════════════════════════════════ --}}
@if(! empty($dashboard))
<section class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-sm">
    <div class="border-b border-borde-suave px-5 py-3.5">
        <h2 class="text-sm font-bold uppercase tracking-[0.15em] text-titulo">
            <i class="ph-bold ph-buildings mr-2 text-apoyo"></i>Resumen institucional
        </h2>
    </div>
    <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">

        {{-- Tipo más frecuente --}}
        <div class="rounded-xl border border-borde-suave bg-fondo-app p-4">
            <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Actividad más frecuente</p>
            <p class="mt-2 text-sm font-black text-titulo">{{ $dashboard['tipo_mas_frecuente']?->tipo ?? 'Sin datos' }}</p>
            @if($dashboard['tipo_mas_frecuente'])
            <p class="text-[10px] text-apoyo mt-0.5">{{ $dashboard['tipo_mas_frecuente']->total }} ocurrencias</p>
            @endif
        </div>

        {{-- Adulto más activo --}}
        <div class="rounded-xl border border-borde-suave bg-fondo-app p-4">
            <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Mayor asistencia</p>
            <p class="mt-2 text-sm font-black text-titulo">{{ $dashboard['adulto_mas_activo']?->nombre ?? 'Sin datos' }}</p>
            @if($dashboard['adulto_mas_activo'])
            <p class="text-[10px] text-apoyo mt-0.5">{{ $dashboard['adulto_mas_activo']->total }} asistencias registradas</p>
            @endif
        </div>

        {{-- Pendientes de evaluación --}}
        <div class="rounded-xl border {{ $dashboard['pendientes_evaluacion'] > 0 ? 'border-estado-advertenciaBorde bg-estado-advertenciaBg' : 'border-borde-suave bg-fondo-app' }} p-4">
            <p class="text-[10px] font-bold uppercase tracking-[0.13em] {{ $dashboard['pendientes_evaluacion'] > 0 ? 'text-estado-advertencia' : 'text-apoyo' }}">Pendientes de evaluación</p>
            <p class="mt-2 text-2xl font-black {{ $dashboard['pendientes_evaluacion'] > 0 ? 'text-estado-advertencia' : 'text-titulo' }}">
                {{ $dashboard['pendientes_evaluacion'] }}
            </p>
            <p class="text-[10px] mt-0.5 {{ $dashboard['pendientes_evaluacion'] > 0 ? 'text-estado-advertencia' : 'text-apoyo' }}">
                Realizadas sin evaluación final
            </p>
            @if($dashboard['pendientes_evaluacion'] > 0)
            <a href="{{ route('admin.actividades.asistencia') }}"
                class="mt-2 inline-flex items-center gap-1 text-[10px] font-bold text-estado-advertencia hover:underline">
                <i class="ph-bold ph-arrow-right text-[10px]"></i> Ir a asistencia
            </a>
            @endif
        </div>

        {{-- Alertas baja participación --}}
        @if($dashboard['alertas_baja_participacion'] > 0)
        <div class="rounded-xl border border-borde-focus bg-estado-peligroBg p-4">
            <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-boton-acento">Alertas participación baja</p>
            <p class="mt-2 text-2xl font-black text-boton-acento">{{ $dashboard['alertas_baja_participacion'] }}</p>
            <p class="text-[10px] font-bold text-boton-acento mt-0.5">Adultos con ≥3 registros de baja participación</p>
        </div>
        @endif

        {{-- Próximas actividades --}}
        @if($dashboard['proximas']->isNotEmpty())
        <div class="col-span-2 rounded-xl border border-borde-suave bg-fondo-app p-4">
            <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo mb-3">Próximas actividades programadas</p>
            <div class="space-y-2">
                @foreach($dashboard['proximas'] as $prox)
                <div class="flex items-center justify-between gap-3 text-xs">
                    <div class="min-w-0">
                        <p class="font-black text-titulo truncate">{{ $prox->nombre ?? $prox->tipo ?? 'Actividad' }}</p>
                        @if($prox->lugar)<p class="text-[10px] text-apoyo"><i class="ph-bold ph-map-pin mr-0.5"></i>{{ $prox->lugar }}</p>@endif
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="font-bold text-titulo">{{ \Carbon\Carbon::parse($prox->fecha)->format('d/m/Y') }}</p>
                        @if($prox->hora)<p class="text-[10px] text-apoyo">{{ substr($prox->hora, 0, 5) }}</p>@endif
                    </div>
                </div>
                @endforeach
            </div>
            <a href="{{ route('admin.actividades.calendario') }}"
                class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-bold text-boton-acento hover:underline">
                <i class="ph-bold ph-calendar-dots text-xs"></i> Ver en calendario
            </a>
        </div>
        @endif

    </div>
</section>
@endif

</div>{{-- /max-w-7xl --}}

{{-- ════════════════════════════════════════════════════════════════════════ --}}
{{-- INICIALIZACIÓN CHART.JS --}}
{{-- ════════════════════════════════════════════════════════════════════════ --}}
@script
<script>
// ── Datos iniciales desde PHP ──────────────────────────────────────────────
const _chartDataInit = @json($chartData);

// ── Instancias de Chart.js ─────────────────────────────────────────────────
const _charts = {};

// ── Opciones base ──────────────────────────────────────────────────────────
const _fontFamily = '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
Chart.defaults.font.family = _fontFamily;
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#5C4A3A';

function _baseOpts(extra = {}) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false, ...extra.legend },
            tooltip: {
                backgroundColor: '#FDFAF7',
                titleColor: '#2F3E5C',
                bodyColor: '#5C4A3A',
                borderColor: '#C7B5A3',
                borderWidth: 1,
                padding: 10,
                titleFont: { weight: 'bold', size: 12 },
            },
        },
        ...extra,
    };
}

// ── Crear o actualizar un chart ────────────────────────────────────────────
function _upsertChart(id, type, data, opts = {}) {
    const canvas = document.getElementById(id);
    if (!canvas) return;

    if (_charts[id]) {
        _charts[id].data = data;
        _charts[id].update('active');
        return;
    }

    _charts[id] = new Chart(canvas, {
        type,
        data,
        options: Object.assign(_baseOpts(), opts),
    });
}

// ── Inicializar todos los gráficos ────────────────────────────────────────
function _initAllCharts(d) {
    // A. Estado (dona)
    if (d.estado?.labels?.length) {
        _upsertChart('chart-estado', 'doughnut', {
            labels: d.estado.labels,
            datasets: [{ data: d.estado.data, backgroundColor: d.estado.colors, borderWidth: 2, borderColor: '#FDFAF7' }],
        }, { plugins: { legend: { display: true, position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } }, cutout: '60%' });
    }

    // B. Tipo (barras horizontales)
    if (d.tipo?.labels?.length) {
        _upsertChart('chart-tipo', 'bar', {
            labels: d.tipo.labels,
            datasets: [{ data: d.tipo.data, backgroundColor: d.tipo.colors || '#4A90D9', borderRadius: 4 }],
        }, {
            indexAxis: 'y',
            scales: { x: { grid: { color: '#C7B5A3' }, ticks: { font: { size: 10 } } }, y: { ticks: { font: { size: 9 } } } },
        });
    }

    // C. Por mes (barras verticales)
    if (d.mes?.labels?.length) {
        _upsertChart('chart-mes', 'bar', {
            labels: d.mes.labels,
            datasets: [{ label: 'Actividades', data: d.mes.data, backgroundColor: '#8DA280', borderRadius: 4 }],
        }, {
            scales: { x: { grid: { display: false }, ticks: { font: { size: 9 } } }, y: { grid: { color: '#C7B5A3' }, ticks: { stepSize: 1 } } },
        });
    }

    // D. Asistencia por mes (líneas)
    if (d.asistencia?.labels?.length) {
        _upsertChart('chart-asistencia', 'line', {
            labels: d.asistencia.labels,
            datasets: [
                { label: 'Asistió',    data: d.asistencia.asistio,     borderColor: '#2A9D8F', backgroundColor: 'rgba(42,157,143,0.1)',  tension: 0.3, fill: true },
                { label: 'Faltó',      data: d.asistencia.falto,       borderColor: '#E27D60', backgroundColor: 'rgba(226,125,96,0.1)',   tension: 0.3, fill: true },
                { label: 'Justificado',data: d.asistencia.justificado, borderColor: '#D9A05B', backgroundColor: 'rgba(217,160,91,0.1)',   tension: 0.3, fill: true },
            ],
        }, {
            plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#C7B5A3' }, ticks: { stepSize: 1 } } },
        });
    }

    // E. Nivel de participación (dona)
    if (d.nivel?.data?.some(v => v > 0)) {
        _upsertChart('chart-nivel', 'doughnut', {
            labels: d.nivel.labels,
            datasets: [{ data: d.nivel.data, backgroundColor: d.nivel.colors, borderWidth: 2, borderColor: '#FDFAF7' }],
        }, { plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } } }, cutout: '55%' });
    }

    // F. Seguimiento (barras)
    if (d.seguimiento?.labels?.length) {
        _upsertChart('chart-seguimiento', 'bar', {
            labels: d.seguimiento.labels,
            datasets: [{ label: 'Con seguimiento', data: d.seguimiento.seguimiento, backgroundColor: '#E27D60', borderRadius: 4 }],
        }, {
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#C7B5A3' }, ticks: { stepSize: 1 } } },
        });
    }
}

// ── Inicializar en mount ───────────────────────────────────────────────────
_initAllCharts(_chartDataInit);

// ── Actualizar cuando cambien los filtros (Livewire dispatch) ─────────────
$wire.on('charts-data-updated', ({ payload }) => {
    _initAllCharts(payload);
});
</script>
@endscript

</div>
