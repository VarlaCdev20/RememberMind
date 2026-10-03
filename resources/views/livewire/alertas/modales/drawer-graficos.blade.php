@if($drawerGrafico && $adultoDrawer)
@php
 $ultSigno = $signosDrawer->first();
 $historialSignos = $signosDrawer->take(8);

 $puntos = $signosDrawer->take(8)->reverse()->values();
 $labels = $puntos->map(fn($s) => $s->fecha ? $s->fecha->format('d/m') . ($s->hora ? ' ' . substr($s->hora, 0, 5) : '') : 'Control')->values()->toArray();
 $sisData = $puntos->map(fn($s) => $s->presion_sistolica !== null ? (float)$s->presion_sistolica : null)->values()->toArray();
 $diaData = $puntos->map(fn($s) => $s->presion_diastolica !== null ? (float)$s->presion_diastolica : null)->values()->toArray();
 $fcData = $puntos->map(fn($s) => $s->frecuencia_cardiaca !== null ? (float)$s->frecuencia_cardiaca : null)->values()->toArray();
 $spo2Data = $puntos->map(fn($s) => $s->saturacion !== null ? (float)$s->saturacion : null)->values()->toArray();
 $tempData = $puntos->map(fn($s) => $s->temperatura !== null ? (float)$s->temperatura : null)->values()->toArray();

 $alergiasTexto = collect($adultoDrawer->alergias ?? [])
  ->map(function ($alergia) {
   if (is_string($alergia)) {
    return trim($alergia);
   }

   $sustancia = trim((string) ($alergia->sustancia ?? ''));
   $reaccion = trim((string) ($alergia->reaccion ?? ''));

   return $sustancia !== '' ? $sustancia.($reaccion !== '' ? " ({$reaccion})" : '') : null;
  })
  ->filter()
  ->implode(', ');

 $diagnosticosTexto = collect($adultoDrawer->diagnosticos ?? [])
  ->map(fn($diagnostico) => trim((string) ($diagnostico->diagnostico ?? $diagnostico->nombre ?? $diagnostico->descripcion ?? '')))
  ->filter()
  ->implode(', ');

 // Registros clínicos recientes para el timeline (Evoluciones / Valoraciones)
 $registrosTimeline = collect();
 if ($adultoDrawer->valoracionesEnfermeria && $adultoDrawer->valoracionesEnfermeria->count()) {
 foreach ($adultoDrawer->valoracionesEnfermeria->take(3) as $val) {
  $registrosTimeline->push([
  'fecha' => $val->created_at ? $val->created_at->format('d/m/Y H:i') : 'Fecha no registrada',
  'tipo' => 'Enfermería',
  'profesional' => $val->profesional->name ?? ($val->registradoPor->name ?? 'No registrado'),
  'resumen' => $val->observaciones ?: ($val->diagnostico_enfermeria ?: 'Sin resumen registrado.'),
  'estado' => $val->estado ?? 'Registrado',
  'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
  ]);
 }
 }
 if ($registrosTimeline->isEmpty() && $adultoDrawer->seguimientosDiarios && $adultoDrawer->seguimientosDiarios->count()) {
 foreach ($adultoDrawer->seguimientosDiarios->take(3) as $seg) {
  $registrosTimeline->push([
  'fecha' => $seg->created_at ? $seg->created_at->format('d/m/Y H:i') : 'Fecha no registrada',
  'tipo' => 'Seguimiento',
  'profesional' => $seg->profesional->name ?? 'No registrado',
  'resumen' => $seg->observacion ?: 'Sin resumen registrado.',
  'estado' => $seg->estado ?? 'Registrado',
  'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
  ]);
 }
 }
@endphp
<div class="fixed inset-0 z-50 overflow-hidden font-sans"
 role="dialog"
 aria-modal="true"
 aria-labelledby="drawer-graficos-title"
 tabindex="-1"
 x-init="$nextTick(() => $el.focus({ preventScroll: true }))"
 x-data="{
 tab: 'presion',
 labels: @js($labels),
 sisData: @js($sisData),
 diaData: @js($diaData),
 fcData: @js($fcData),
 spo2Data: @js($spo2Data),
 tempData: @js($tempData),
 init() {
  this.$watch('tab', () => this.renderCurrentChart());
  this.$nextTick(() => {
  this.renderCurrentChart();
  });
  window.RMCharts?.onThemeChange(() => {
  this.renderCurrentChart();
  }, 'alertas-drawer');
 },
 renderCurrentChart() {
  if (typeof Chart === 'undefined') {
  setTimeout(() => this.renderCurrentChart(), 80);
  return;
  }
  const canvas = document.getElementById('chart-drawer-evolucion');
  if (!canvas) return;

  const api = window.RMCharts;
  const css = (token) => api.getCss(token);
  const fill = (tone, opacity = '--rm-line-area-opacity') => api.hexToRgba(api.color(tone), api.number(opacity, .12));
  const gridColor = css('--rm-chart-grid');
  const axisTextColor = css('--rm-chart-axis-text');
  const surfaceRaised = css('--rm-chart-tooltip-bg');
  const tooltipText = css('--rm-chart-tooltip-text');

  let datasets = [];
  let yMin = undefined;
  let yMax = undefined;
  let yStep = undefined;
  let unit = '';

  if (this.tab === 'presion') {
  unit = 'mmHg';
  datasets = [
   {
   label: 'Sistólica',
   data: this.sisData,
   borderColor: api.color('clinical'),
   backgroundColor: fill('clinical'),
   fill: true,
   },
   {
   label: 'Diastólica',
   data: this.diaData,
   borderColor: api.color('reference'),
   backgroundColor: fill('reference', '--rm-line-area-secondary-opacity'),
   fill: true,
   }
  ];
  yMin = 40;
  yMax = 190;
  yStep = 20;
  } else if (this.tab === 'pulso') {
  unit = 'lpm';
  datasets = [{
   label: 'Frecuencia Cardíaca',
   data: this.fcData,
   borderColor: api.color('clinical'),
   backgroundColor: fill('clinical'),
   fill: true,
  }];
  yMin = 40;
  yMax = 140;
  yStep = 20;
  } else if (this.tab === 'spo2') {
  unit = '%';
  datasets = [{
   label: 'Saturación SpO₂',
   data: this.spo2Data,
   borderColor: api.color('clinical'),
   backgroundColor: fill('clinical'),
   fill: true,
  }];
  yMin = 80;
  yMax = 100;
  yStep = 5;
  } else if (this.tab === 'temp') {
  unit = '°C';
  datasets = [{
   label: 'Temperatura',
   data: this.tempData,
   borderColor: api.color('clinical'),
   backgroundColor: fill('clinical'),
   fill: true,
  }];
  yMin = 34.5;
  yMax = 40.5;
  yStep = 1;
  }

  datasets = datasets.map(dataset => ({
   ...dataset,
   borderWidth: api.number('--rm-line-stroke-width', 3),
   pointRadius: api.number('--rm-line-dot-size', 5) / 2,
   pointHoverRadius: api.number('--rm-line-dot-size', 5),
   tension: .38,
  }));

  api.init('alertas-drawer-evolucion', canvas, {
  type: 'line',
  data: {
   labels: this.labels,
   datasets: datasets
  },
  options: {
   responsive: true,
   maintainAspectRatio: false,
   animation: { duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : api.number('--rm-chart-line-enter-duration', 650), easing: api.getCss('--rm-chart-js-easing') },
   interaction: { mode: 'index', intersect: false },
   scales: {
   x: {
    display: true,
    grid: { color: gridColor, drawBorder: false },
    ticks: {
    color: axisTextColor,
    font: { family: 'Outfit, Nunito Sans, system-ui, sans-serif', size: 10, weight: '600' },
    maxRotation: 0,
    }
   },
   y: {
    display: true,
    min: yMin,
    max: yMax,
    grid: { color: gridColor, drawBorder: false },
    ticks: {
    color: axisTextColor,
    font: { family: 'Outfit, Nunito Sans, system-ui, sans-serif', size: 10, weight: '600' },
    stepSize: yStep,
    callback: (val) => val + ' ' + unit
    }
   }
   },
   plugins: {
   legend: { display: false },
   datalabels: {
    display: true,
    align: 'top',
    offset: 3,
    borderRadius: 4,
    padding: 2,
    backgroundColor: surfaceRaised,
    borderColor: (ctx) => ctx.dataset.borderColor,
    borderWidth: 1,
    color: (ctx) => ctx.dataset.borderColor,
    font: { size: 9.5, weight: 'bold', family: 'Outfit, Nunito Sans, sans-serif' },
    formatter: (v) => v !== null && v !== undefined ? v : ''
   },
   tooltip: {
    enabled: true,
    backgroundColor: surfaceRaised,
    titleColor: tooltipText,
    bodyColor: tooltipText,
    padding: 10,
    cornerRadius: 8,
    titleFont: { family: 'Outfit, Nunito Sans, sans-serif', size: 11, weight: '700' },
    bodyFont: { family: 'Outfit, Nunito Sans, sans-serif', size: 11, weight: '500' },
    callbacks: {
    label: (c) => ` ${c.dataset.label}: ${c.parsed.y} ${unit}`
    }
   }
   }
  }
  });
 }
 }"
 x-on:keydown.escape.window="$wire.cerrarDrawer()">

 {{-- 1. BACKDROP OSCURO SUAVE (Sin blur invasivo) --}}
 <div class="rm-drawer-backdrop" wire:click="cerrarDrawer"></div>

 {{-- 2. CONTENEDOR DESLIZANTE NÍTIDO (680-760px) --}}
 <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-4 sm:inset-y-3 sm:right-3 sm:pl-10">
 <div class="pointer-events-auto flex h-full w-screen max-w-[760px] transform flex-col overflow-hidden rm-drawer transition duration-300 ease-in-out md:w-[740px]">

  {{-- HEADER FIJO (Badge de Consulta + Título + Subtítulo + Botón X) --}}
  <header class="rm-drawer-header">
  <div class="flex items-start justify-between gap-3">
   <div class="space-y-1">
   <span class="rm-drawer-badge">
    <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
    PANEL LATERAL DE CONSULTA
   </span>
   <div class="flex items-center gap-2 pt-0.5">
    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface)] text-[var(--rm-action-primary)] border border-[var(--rm-border)] text-sm shadow-2xs">
    <i class="ph-bold ph-chart-line-up"></i>
    </span>
    <h2 id="drawer-graficos-title" class="rm-drawer-title">Gráficos de Evolución Clínica</h2>
   </div>
   <p class="rm-drawer-subtitle">
    Monitoreo hemodinámico, curva de tendencia y registro clínico longitudinal
   </p>
   </div>
   <button type="button"
    wire:click="cerrarDrawer"
    class="rm-btn-icon text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] p-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer"
    aria-label="Cerrar panel lateral">
   <i class="ph-bold ph-x text-base"></i>
   </button>
  </div>
  </header>

  {{-- BODY CON SCROLL EXCLUSIVO (100% NÍTIDO, CÁLIDO Y ESTRUCTURADO) --}}
  <div class="rm-drawer-body">

  {{-- 3. CARD DEL RESIDENTE (Horizontal Compacta) --}}
  <div class="rm-drawer-resident">
   <div class="flex items-center gap-3 min-w-0">
   <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-white font-black text-sm shadow-xs">
    {{ substr($adultoDrawer->nombres, 0, 1) }}{{ substr($adultoDrawer->ap_paterno, 0, 1) }}
   </div>
   <div class="min-w-0">
    <h4 class="font-bold text-[var(--rm-text-title)] text-sm leading-tight truncate uppercase">
    {{ $adultoDrawer->ap_paterno }} {{ $adultoDrawer->ap_materno }} {{ $adultoDrawer->nombres }}
    </h4>
    <div class="flex items-center gap-2 text-xs text-[var(--rm-text-muted)] mt-0.5 flex-wrap">
    <span>{{ $adultoDrawer->edad_texto }}</span>
    <span>•</span>
    <span>CI: {{ $adultoDrawer->ci ?: 'Documento S/D' }}</span>
    <span>•</span>
    <span class="font-mono text-[11px]">{{ $adultoDrawer->cod_residente }}</span>
    </div>
   </div>
   </div>

   <div class="text-right flex-shrink-0">
   <span class="inline-block px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-title)] font-semibold text-xs">
    {{ $adultoDrawer->ubicacion_texto }}
   </span>
   <div class="mt-1 flex items-center justify-end gap-1.5">
    <span class="text-[10.5px] text-[var(--rm-text-muted)]">Estado:</span>
    <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $adultoDrawer->estado_badge_color }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ $adultoDrawer->estado_humano }}
    </span>
   </div>
   </div>
  </div>

  {{-- 4. ÚLTIMO CONTROL CLÍNICO (4 MÉTRICAS COMPACTAS CON LÍNEA SEMÁNTICA) --}}
  <div class="space-y-2">
   <div class="flex items-center justify-between">
   <span class="rm-drawer-section">
    <i class="ph-bold ph-heartbeat text-rose-600 text-sm"></i>
    <span>Último Control de Signos Vitales</span>
   </span>
   <span class="text-[11px] text-[var(--rm-text-muted)] font-mono">
    {{ $ultSigno ? ($ultSigno->fecha ? $ultSigno->fecha->format('d/m/Y') : '') . ' ' . ($ultSigno->hora ? substr($ultSigno->hora, 0, 5) : '') : 'Sin tomas recientes' }}
   </span>
   </div>

   <div class="rm-drawer-metrics">
   {{-- Presión Arterial --}}
   <div class="rm-drawer-metric-card metric-danger">
    <div class="flex items-center justify-between">
    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)]">Presión Art.</span>
    <i class="ph-bold ph-heartbeat text-rose-500 text-xs"></i>
    </div>
    <div class="mt-1">
    <span class="text-base font-black text-[var(--rm-text-title)] font-mono">
     {{ $ultSigno && $ultSigno->presion_sistolica ? $ultSigno->presion_sistolica.'/'.$ultSigno->presion_diastolica : ($ultSigno->presion_arterial ?? '--/--') }}
    </span>
    <span class="text-[10px] text-[var(--rm-text-muted)] font-sans">mmHg</span>
    </div>
    <span class="text-[9.5px] font-semibold text-rose-600 mt-0.5 block">
    {{ ! $ultSigno?->presion_sistolica ? 'Sin datos' : ($ultSigno->presion_sistolica >= 140 ? 'Elevada' : 'Dentro de rango') }}
    </span>
   </div>

   {{-- Pulso / FC --}}
   <div class="rm-drawer-metric-card metric-warning">
    <div class="flex items-center justify-between">
    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)]">Pulso / F.C.</span>
    <i class="ph-bold ph-heart text-[var(--rm-warning)] text-xs"></i>
    </div>
    <div class="mt-1">
    <span class="text-base font-black text-[var(--rm-text-title)] font-mono">
     {{ $ultSigno && $ultSigno->frecuencia_cardiaca ? $ultSigno->frecuencia_cardiaca : '--' }}
    </span>
    <span class="text-[10px] text-[var(--rm-text-muted)] font-sans">lpm</span>
    </div>
    <span class="text-[9.5px] font-semibold text-emerald-600 mt-0.5 block">
    {{ ! $ultSigno?->frecuencia_cardiaca ? 'Sin datos' : (in_array((int) $ultSigno->frecuencia_cardiaca, range(60, 100), true) ? 'Dentro de rango' : 'Revisar') }}
    </span>
   </div>

   {{-- Saturación SpO2 --}}
   <div class="rm-drawer-metric-card metric-info">
    <div class="flex items-center justify-between">
    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)]">Saturación O₂</span>
    <i class="ph-bold ph-drop text-sky-500 text-xs"></i>
    </div>
    <div class="mt-1">
    <span class="text-base font-black text-[var(--rm-text-title)] font-mono">
     {{ $ultSigno && $ultSigno->saturacion ? $ultSigno->saturacion.'%' : '--' }}
    </span>
    <span class="text-[10px] text-[var(--rm-text-muted)] font-sans">SpO₂</span>
    </div>
    <span class="text-[9.5px] font-semibold text-emerald-600 mt-0.5 block">
    {{ ! $ultSigno?->saturacion ? 'Sin datos' : ((int) $ultSigno->saturacion >= 95 ? 'Adecuada' : 'Revisar') }}
    </span>
   </div>

   {{-- Temperatura --}}
   <div class="rm-drawer-metric-card metric-success">
    <div class="flex items-center justify-between">
    <span class="text-[10px] font-bold uppercase text-[var(--rm-text-muted)]">Temperatura</span>
    <i class="ph-bold ph-thermometer text-[var(--rm-warning)] text-xs"></i>
    </div>
    <div class="mt-1">
    <span class="text-base font-black text-[var(--rm-text-title)] font-mono">
     {{ $ultSigno && $ultSigno->temperatura ? $ultSigno->temperatura.'°' : '--' }}
    </span>
    <span class="text-[10px] text-[var(--rm-text-muted)] font-sans">C</span>
    </div>
    <span class="text-[9.5px] font-semibold text-emerald-600 mt-0.5 block">
    {{ ! $ultSigno?->temperatura ? 'Sin datos' : (((float) $ultSigno->temperatura >= 36 && (float) $ultSigno->temperatura <= 37.5) ? 'Afebril' : 'Revisar') }}
    </span>
   </div>
   </div>
  </div>

  {{-- 5. GRÁFICO DE TENDENCIA (Tabs + Chart.js Interactivo) --}}
  <div class="rm-drawer-card rm-chart-card rm-chart-glass space-y-3">
   <div class="rm-chart-header flex-col sm:flex-row sm:items-center">
   <span class="rm-drawer-section">
    <i class="ph-bold ph-chart-line text-blue-600 text-sm"></i>
    <span>CURVA DE TENDENCIA TEMPORAL</span>
   </span>

   {{-- Tabs de Métricas --}}
   <div class="flex items-center gap-1 bg-[var(--rm-surface-alt)] p-1 rounded-xl border border-[var(--rm-border)]">
    <button type="button"
    @click="tab = 'presion'"
    :class="tab === 'presion' ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs font-bold' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] font-medium'"
    class="px-2.5 py-1 text-[11px] rounded-lg transition cursor-pointer">
    Presión
    </button>
    <button type="button"
    @click="tab = 'pulso'"
    :class="tab === 'pulso' ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs font-bold' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] font-medium'"
    class="px-2.5 py-1 text-[11px] rounded-lg transition cursor-pointer">
    Pulso
    </button>
    <button type="button"
    @click="tab = 'spo2'"
    :class="tab === 'spo2' ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs font-bold' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] font-medium'"
    class="px-2.5 py-1 text-[11px] rounded-lg transition cursor-pointer">
    SpO₂
    </button>
    <button type="button"
    @click="tab = 'temp'"
    :class="tab === 'temp' ? 'bg-[var(--rm-action-primary)] text-white shadow-2xs font-bold' : 'text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] font-medium'"
    class="px-2.5 py-1 text-[11px] rounded-lg transition cursor-pointer">
    Temp.
    </button>
   </div>
   </div>

   {{-- Canvas del Gráfico --}}
   <div class="rm-chart-body w-full h-44 sm:h-48 relative" wire:ignore>
   <canvas id="chart-drawer-evolucion"></canvas>
   </div>
  </div>

  {{-- 6. BLOQUE DOBLE: DETALLE CLÍNICO (IZQ) + REGISTRO CLÍNICO (DER) --}}
  <div class="rm-drawer-clinical-grid">
   {{-- Columna Izquierda: DETALLE CLÍNICO (Definición Etiqueta -> Valor) --}}
   <div class="rm-drawer-card space-y-2.5">
   <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-1.5">
    <span class="rm-drawer-section">
    <i class="ph-bold ph-stethoscope text-indigo-600 text-sm"></i>
    <span>DETALLE CLÍNICO</span>
    </span>
    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.2 rounded border border-indigo-200">
    Diagnóstico activo
    </span>
   </div>

   <div class="rm-drawer-def-list">
    <div class="rm-drawer-def-item">
    <span class="rm-drawer-def-label">Estado general:</span>
    <span class="rm-drawer-def-value">{{ $adultoDrawer->estado_humano ?? 'Sin evaluación registrada' }}</span>
    </div>
    <div class="rm-drawer-def-item">
    <span class="rm-drawer-def-label">Nivel de cuidado:</span>
    <span class="rm-drawer-def-value">{{ $adultoDrawer->planCuidadoActivo?->nivel_cuidado ?: 'Sin nivel registrado' }}</span>
    </div>
    <div class="rm-drawer-def-item">
    <span class="rm-drawer-def-label">Riesgo de caídas:</span>
    <span class="rm-drawer-def-value text-[var(--rm-warning)] font-bold">Sin cálculo disponible</span>
    </div>
    <div class="rm-drawer-def-item">
    <span class="rm-drawer-def-label">Diagnóstico ppal:</span>
    <span class="rm-drawer-def-value truncate max-w-[170px]" title="{{ $diagnosticosTexto ?: 'Sin diagnóstico principal registrado' }}">
     {{ $diagnosticosTexto ?: 'Sin diagnóstico principal registrado' }}
    </span>
    </div>
    <div class="rm-drawer-def-item">
    <span class="rm-drawer-def-label">Alergias:</span>
    <span class="rm-drawer-def-value text-rose-600 font-bold">
     {{ $alergiasTexto ?: 'Sin alergias conocidas' }}
    </span>
    </div>
    <div class="rm-drawer-def-item">
    <span class="rm-drawer-def-label">Plan actual:</span>
    <span class="rm-drawer-def-value truncate max-w-[170px]" title="{{ $adultoDrawer->planCuidadoActivo->titulo ?? 'Plan Integral de Cuidados Activo' }}">
     {{ $adultoDrawer->planCuidadoActivo->titulo ?? 'Plan Integral Activo' }}
    </span>
    </div>
   </div>
   </div>

   {{-- Columna Derecha: REGISTRO CLÍNICO (Timeline Compacto) --}}
   <div class="rm-drawer-card space-y-2.5">
   <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-1.5">
    <span class="rm-drawer-section">
    <i class="ph-bold ph-clock-counter-clockwise text-blue-600 text-sm"></i>
    <span>REGISTRO CLÍNICO</span>
    </span>
    @if(\Illuminate\Support\Facades\Route::has('admin.cuidados.pacientes.ficha'))
    <a href="{{ route('admin.cuidados.pacientes.ficha', $adultoDrawer->cod_residente) }}"
     class="text-[10px] font-bold text-[var(--rm-action-primary-hover)] hover:underline">
     Ver todos →
    </a>
    @else
    <span class="text-[10px] text-[var(--rm-text-muted)] font-bold">Recientes</span>
    @endif
   </div>

   <div class="rm-drawer-timeline">
    @foreach($registrosTimeline as $reg)
    <div class="rm-drawer-timeline-item space-y-0.5">
     <div class="flex items-center justify-between">
     <span class="font-bold text-[10.5px] text-[var(--rm-text-title)]">
      {{ $reg['tipo'] }} · <span class="text-[var(--rm-text-muted)] font-normal">{{ $reg['profesional'] }}</span>
     </span>
     <span class="text-[9.5px] font-mono text-[var(--rm-text-muted)]">{{ $reg['fecha'] }}</span>
     </div>
     <p class="text-[10.5px] text-[var(--rm-text-body)] line-clamp-2 leading-tight">
     {{ $reg['resumen'] }}
     </p>
    </div>
    @endforeach
   </div>
   </div>
  </div>

  {{-- 7. HISTORIAL DE CONTROLES (Tabla Compacta) --}}
  <div class="space-y-2">
   <div class="flex items-center justify-between">
   <span class="rm-drawer-section">
    <i class="ph-bold ph-table text-blue-600 text-sm"></i>
    <span>Historial de Controles Recientes</span>
   </span>
   <span class="text-[10.5px] text-[var(--rm-text-muted)]">Últimos {{ count($historialSignos) }} registros</span>
   </div>

   <div class="rounded-xl border border-[var(--rm-border)] overflow-hidden shadow-2xs">
   <table class="rm-data-table rm-drawer-table">
    <thead>
    <tr>
     <th>Fecha / Hora</th>
     <th>PA</th>
     <th>Pulso</th>
     <th>SpO₂</th>
     <th>Temp.</th>
     <th>Estado</th>
     <th>Registrado por</th>
    </tr>
    </thead>
    <tbody class="divide-y divide-[var(--rm-border)]/50">
    @forelse($historialSignos as $s)
     <tr class="hover:bg-[var(--rm-surface-alt)]/60 transition">
     <td class="font-mono text-[10.5px] whitespace-nowrap">
      {{ ($s->fecha ? $s->fecha->format('d/m/Y') : '') . ' ' . ($s->hora ? substr($s->hora, 0, 5) : '') }}
     </td>
     <td class="font-mono font-bold text-[var(--rm-text-title)] whitespace-nowrap">
      {{ $s->presion_sistolica ? $s->presion_sistolica.'/'.$s->presion_diastolica : ($s->presion_arterial ?: '--') }}
     </td>
     <td class="font-mono whitespace-nowrap">
      {{ $s->frecuencia_cardiaca ? $s->frecuencia_cardiaca.' lpm' : '--' }}
     </td>
     <td class="font-mono whitespace-nowrap">
      {{ $s->saturacion ? $s->saturacion.'%' : '--' }}
     </td>
     <td class="font-mono whitespace-nowrap">
      {{ $s->temperatura ? $s->temperatura.'°C' : '--' }}
     </td>
     <td class="whitespace-nowrap">
      <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold border border-[var(--rm-border)] text-[var(--rm-text-muted)]">
      Registrada
      </span>
     </td>
     <td class="text-[10px] text-[var(--rm-text-muted)] truncate max-w-[110px]" title="{{ $s->registradoPor->name ?? 'Profesional no registrado' }}">
      {{ $s->registradoPor->name ?? 'Profesional no registrado' }}
     </td>
     </tr>
    @empty
     <tr>
     <td colspan="7" class="py-5 text-center text-xs text-[var(--rm-text-muted)] italic">
      No hay registros de signos vitales disponibles para este residente.
     </td>
     </tr>
    @endforelse
    </tbody>
   </table>
   </div>
  </div>

  </div>

  {{-- 8. FOOTER FIJO (Siempre visible, con hasta 1 acción primaria azul oscuro) --}}
  <footer class="rm-drawer-footer">
  <div class="flex items-center gap-2">
   <button type="button"
    wire:click="verUbicacion('{{ $adultoDrawer->cod_residente }}')"
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer shadow-2xs">
   <i class="ph-bold ph-bed text-sm text-[var(--rm-text-muted)]"></i>
   <span>Ver ubicación y cama</span>
   </button>

   @if(\Illuminate\Support\Facades\Route::has('admin.cuidados.pacientes.ficha'))
   <a href="{{ route('admin.cuidados.pacientes.ficha', $adultoDrawer->cod_residente) }}"
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer shadow-2xs">
    <i class="ph-bold ph-user-circle text-sm text-[var(--rm-text-muted)]"></i>
    <span>Ver ficha médica</span>
   </a>
   @endif
  </div>

  <div class="flex items-center gap-2">
   @if(isset($alertaId) && $alertaId)
   <button type="button"
    wire:click="atenderAlerta('{{ $alertaId }}')"
    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[var(--rm-action-primary)] text-white text-xs font-bold hover:bg-blue-900 transition active:scale-98 cursor-pointer shadow-xs">
    <i class="ph-bold ph-plus text-sm"></i>
    <span>+ Registrar atención</span>
   </button>
   @endif

   <button type="button"
    wire:click="cerrarDrawer"
    class="px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs font-semibold text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
   Cerrar panel
   </button>
  </div>
  </footer>
 </div>
 </div>
</div>
@endif
