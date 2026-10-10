<div class="rm-page-layout relative mx-auto max-w-7xl space-y-5 text-[var(--rm-text-primary)]">
 <div class="relative z-10 space-y-5">
 @if($seccionActiva === 'resumen')
 {{-- ENCABEZADO PRINCIPAL --}}
 <x-ui.page-header
  overline="Área clínico asistencial"
  title="Salud y seguimiento"
  subtitle="Revisión integral de fichas médicas, controles, valoraciones, medicación y alertas preventivas."
  icon="ph-heartbeat"
 >
  @can('atenciones.crear')
   <x-ui.action-button variant="primary" size="sm" icono="ph-plus-circle" wire:click="cambiarSeccion('ficha')">
    Nuevo registro de salud
   </x-ui.action-button>
  @endcan
  <x-ui.action-button variant="secondary" size="sm" icono="ph-chart-bar" wire:click="cambiarSeccion('reportes')">
   Reportes
  </x-ui.action-button>
 </x-ui.page-header>

 <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6" aria-label="Indicadores de seguimiento clínico">
 @php
 $metricasHeader = [
 ['label' => 'Seguimientos activos', 'valor' => $stats['seguimientos_activos'] ?? 0, 'icono' => 'ph-users-three', 'variant' => 'primary'],
 ['label' => 'Alertas pendientes', 'valor' => $stats['alertas_pendientes'] ?? 0, 'icono' => 'ph-warning-circle', 'variant' => 'danger'],
 ['label' => 'Medicaciones activas', 'valor' => $stats['medicaciones_activas'] ?? 0, 'icono' => 'ph-pill', 'variant' => 'success'],
 ['label' => 'Controles recientes', 'valor' => $stats['signos_recientes'] ?? 0, 'icono' => 'ph-activity', 'variant' => 'info'],
 ['label' => 'Valoraciones registradas', 'valor' => $stats['valoraciones'] ?? 0, 'icono' => 'ph-person-simple-walk', 'variant' => 'clinical'],
 ['label' => 'Controles de hoy', 'valor' => $stats['controles_hoy'] ?? 0, 'icono' => 'ph-calendar-check', 'variant' => 'warning'],
 ];
 @endphp
 @foreach($metricasHeader as $metrica)
  <x-ui.metric-card
   :etiqueta="$metrica['label']"
   :valor="$metrica['valor']"
   :icono="$metrica['icono']"
   :variant="$metrica['variant']"
   class="p-3.5"
  />
 @endforeach
 </section>

 {{-- TABS --}}
 <nav class="overflow-x-auto rounded-[1.45rem] border border-borde/70 bg-fondo-panel p-2 shadow-sm backdrop-blur-xl scrollbar-hidden">
 <div class="flex min-w-max items-center gap-2">
 @php
 $tabsRaw = [
 'resumen' => ['label' => 'Resumen clínico', 'icon' => 'ph-squares-four', 'permission' => 'salud.ver'],
 'ficha' => ['label' => 'Ficha medica', 'icon' => 'ph-file-text', 'permission' => 'atenciones.ver'],
 'signos' => ['label' => 'Signos vitales', 'icon' => 'ph-activity', 'permission' => 'signos_vitales.ver'],
 'medicacion' => ['label' => 'Medicación', 'icon' => 'ph-pill', 'permission' => 'prescripciones.ver'],
 'administracion' => ['label' => 'Administración', 'icon' => 'ph-prescription', 'permission' => 'administraciones_medicacion.ver'],
 'valoracion' => ['label' => 'Valoracion funcional', 'icon' => 'ph-person-simple-walk', 'permission' => 'valoraciones_funcionales.ver'],
 'evaluaciones' => ['label' => 'Evaluaciones cognitivas', 'icon' => 'ph-brain', 'permission' => 'aplicaciones_instrumento.ver'],
 'nutricion' => ['label' => 'Nutricion', 'icon' => 'ph-apple-pod', 'permission' => 'valoraciones_nutricionales.ver'],
 'alertas' => ['label' => 'Alertas clinicas', 'icon' => 'ph-warning-circle', 'permission' => 'alertas.ver'],
 'reportes' => ['label' => 'Reportes clínicos', 'icon' => 'ph-chart-bar', 'permission' => 'reportes.ver'],
 ];

 $tabs = array_filter($tabsRaw, function($tab) {
 if (auth()->user()->hasRole('SUPERADMINISTRADOR')) return true;

 $hasPerm = auth()->user()->can($tab['permission']);
 return $hasPerm;
 });
 @endphp

 @foreach($tabs as $key => $tab)
 <button
 wire:click="cambiarSeccion('{{ $key }}')"
 type="button"
 class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-3.5 text-[11px] font-bold uppercase tracking-wide transition active:scale-95 {{ $seccionActiva === $key ? 'border-borde-fuerte bg-boton-principal text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.18)]' : 'border-transparent bg-fondo-panel text-parrafo/72 hover:border-borde/70 hover:bg-fondo-panel hover:text-boton-acento' }}"
 >
 <i class="ph-bold {{ $tab['icon'] }} text-sm {{ $seccionActiva === $key ? 'text-boton-acento' : 'text-parrafo/52' }}"></i>
 {{ $tab['label'] }}
 </button>
 @endforeach
 </div>
 </nav>

 {{-- CONTENIDO --}}
 @php
 $totalBase = max(($stats['seguimientos_activos'] ?? 0), 1);
 $porcentajeFichas = min(100, round((($stats['total_fichas'] ?? 0) / $totalBase) * 100));
 $porcentajeMedicacion = min(100, round((($stats['medicaciones_activas'] ?? 0) / $totalBase) * 100));
 $porcentajeControles = min(100, round((($stats['signos_recientes'] ?? 0) / $totalBase) * 100));
 @endphp

 <section class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
 <div class="space-y-5">
 <div class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
 <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">Resumen ejecutivo</span>
 <h2 class="mt-1 text-xl font-extrabold text-parrafo">Mapa operativo de salud</h2>
 </div>
 <span class="inline-flex items-center gap-2 rounded-full bg-fondo-panel px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-parrafo/65">
 <i class="ph-bold ph-clock"></i>
 Actualizado en tiempo real
 </span>
 </div>

 <div class="grid gap-4 md:grid-cols-3">
 @foreach([
 ['label' => 'Cobertura de fichas', 'valor' => $porcentajeFichas, 'icono' => 'ph-file-text', 'color' => 'var(--rm-danger)'],
 ['label' => 'Medicacion activa', 'valor' => $porcentajeMedicacion, 'icono' => 'ph-pill', 'color' => 'var(--rm-success)'],
 ['label' => 'Controles 7 dias', 'valor' => $porcentajeControles, 'icono' => 'ph-activity', 'color' => 'var(--rm-info)'],
 ] as $barra)
 <div class="rounded-2xl border border-borde/45 bg-fondo-panel p-4">
 <div class="mb-3 flex items-center justify-between gap-2">
 <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-apoyo">{{ $barra['label'] }}</p>
 <i class="ph-bold {{ $barra['icono'] }} text-lg" style="color: {{ $barra['color'] }}"></i>
 </div>
 <div class="h-2 overflow-hidden rounded-full bg-fondo-panel">
 <div class="h-full rounded-full" style="width: {{ $barra['valor'] }}%; background-color: {{ $barra['color'] }}"></div>
 </div>
 <p class="mt-2 text-2xl font-black leading-none" style="color: {{ $barra['color'] }}">{{ $barra['valor'] }}%</p>
 </div>
 @endforeach
 </div>
 </div>

 <div class="grid gap-5 xl:grid-cols-2">
 <div class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
 <div class="mb-4 flex items-center justify-between">
 <h3 class="text-sm font-bold uppercase tracking-wider text-parrafo">Ultimos controles</h3>
 <i class="ph-bold ph-activity text-xl text-boton-acento"></i>
 </div>
 <div class="space-y-3">
 @forelse($resumenData['controlesRecientes'] as $control)
 <div class="flex items-center justify-between gap-3 rounded-2xl border border-borde/35 bg-fondo-panel px-3 py-3">
 <div class="min-w-0">
 <p class="truncate text-xs font-bold text-parrafo">{{ $control->adultoMayor?->nombres }} {{ $control->adultoMayor?->ap_paterno }}</p>
 <p class="mt-0.5 text-xs font-bold text-parrafo/55">{{ $control->fecha?->format('d/m/Y') }} - {{ $control->hora_formateada }}</p>
 </div>
 <div class="flex shrink-0 gap-1.5 text-[10px] font-bold">
 <span class="rounded-full bg-fondo-panel px-2 py-1 text-parrafo">{{ $control->presion_formateada ?? 'S/D' }}</span>
 <span class="rounded-full bg-estado-peligroBg px-2 py-1 text-boton-acento">{{ $control->saturacion !== null ? $control->saturacion . '%' : 'SpO2' }}</span>
 </div>
 </div>
 @empty
 <div class="rounded-2xl border border-dashed border-borde bg-fondo-panel p-6 text-center">
 <p class="text-xs font-bold text-parrafo/55">Aun no hay controles recientes.</p>
 </div>
 @endforelse
 </div>
 </div>

 <div class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
 <div class="mb-4 flex items-center justify-between">
 <h3 class="text-sm font-bold uppercase tracking-wider text-parrafo">Medicacion activa</h3>
 <i class="ph-bold ph-pill text-xl text-estado-exito"></i>
 </div>
 <div class="space-y-3">
 @forelse($resumenData['medicaciones'] as $med)
 <div class="rounded-2xl border border-borde/35 bg-fondo-panel px-3 py-3">
 <div class="flex items-start justify-between gap-3">
 <div class="min-w-0">
 <p class="truncate text-xs font-bold text-parrafo">{{ $med->nombre_medicamento }}</p>
 <p class="mt-0.5 text-[10px] font-bold text-parrafo/55">{{ $med->adultoMayor?->nombres }} {{ $med->adultoMayor?->ap_paterno }}</p>
 </div>
 <span class="rounded-full bg-estado-exitoBg px-2 py-1 text-xs font-bold uppercase text-estado-exito">Activo</span>
 </div>
 <p class="mt-2 text-xs font-bold text-parrafo/65">{{ $med->dosis }} - {{ $med->frecuencia }} - {{ $med->via_administracion }}</p>
 </div>
 @empty
 <div class="rounded-2xl border border-dashed border-borde bg-fondo-panel p-6 text-center">
 <p class="text-xs font-bold text-parrafo/55">No hay medicaciones activas registradas.</p>
 </div>
 @endforelse
 </div>
 </div>
 </div>
 </div>

 <aside class="space-y-5">
 <div class="rounded-[1.6rem] border border-borde-focus bg-estado-peligroBg p-5 shadow-sm backdrop-blur-xl">
 <div class="mb-4 flex items-center justify-between">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-boton-acento">Alertas</span>
 <h3 class="mt-1 text-lg font-extrabold text-parrafo">Prioridades preventivas</h3>
 </div>
 <i class="ph-bold ph-warning-circle text-2xl text-boton-acento"></i>
 </div>
 <div class="space-y-3">
 @forelse($resumenData['alertas'] as $alerta)
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-3">
 <div class="flex items-start gap-3">
 <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold {{ $alerta['icono'] }}"></i>
 </span>
 <div class="min-w-0 flex-1">
 <div class="flex flex-wrap items-center gap-2">
 <p class="truncate text-xs font-bold text-parrafo">{{ $alerta['titulo'] }}</p>
 <span class="rounded-full bg-fondo-panel px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-apoyo">{{ $alerta['tipo'] }}</span>
 </div>
 <p class="mt-1 text-[11px] font-bold leading-relaxed text-parrafo/65">{{ $alerta['detalle'] }}</p>
 </div>
 </div>
 </div>
 @empty
 <div class="rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-5 text-center">
 <i class="ph-bold ph-check-circle text-3xl text-estado-exito"></i>
 <p class="mt-2 text-xs font-bold text-parrafo">Sin alertas pendientes</p>
 </div>
 @endforelse
 </div>
 </div>

 <div class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
 <h3 class="text-sm font-bold uppercase tracking-wider text-parrafo">Accesos rapidos</h3>
 <div class="mt-4 grid grid-cols-2 gap-2">
 @foreach([
 ['key' => 'ficha', 'label' => 'Ficha medica', 'icon' => 'ph-file-text'],
 ['key' => 'signos', 'label' => 'Signos vitales', 'icon' => 'ph-activity'],
 ['key' => 'medicacion', 'label' => 'Medicación', 'icon' => 'ph-pill'],
 ['key' => 'alertas', 'label' => 'Alertas', 'icon' => 'ph-warning'],
 ] as $atajo)
 <button wire:click="cambiarSeccion('{{ $atajo['key'] }}')" type="button" class="group rounded-2xl border border-borde/45 bg-fondo-panel px-3 py-3 text-left transition hover:-translate-y-0.5 hover:border-borde-focus hover:bg-fondo-panel">
 <i class="ph-bold {{ $atajo['icon'] }} text-lg text-boton-acento transition group-hover:scale-110"></i>
 <p class="mt-2 text-xs font-bold uppercase leading-tight tracking-wide text-parrafo">{{ $atajo['label'] }}</p>
 </button>
 @endforeach
 </div>
 </div>
 </aside>
 </section>
 @else
 {{-- BOTÓN GLOBAL"VOLVER AL RESUMEN" PARA LOS DEMÁS SUBMÓDULOS --}}
 <div class="mb-2">
 <button wire:click="cambiarSeccion('resumen')" class="inline-flex items-center gap-2 rounded-xl bg-fondo-panel px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo transition-all hover:bg-fondo-panel hover:shadow-sm">
 <i class="ph-bold ph-arrow-left text-sm"></i>
 Volver al resumen
 </button>
 </div>

 @if($seccionActiva === 'alertas')
 <section class="animate-in fade-in duration-200">
 @livewire('alertas.salud-alertas-panel', key('alertas'))
 </section>
 @elseif($seccionActiva === 'reportes')
 <section class="animate-in fade-in duration-200">
 @livewire('reportes.salud-reportes-panel', key('reportes'))
 </section>
 @elseif($seccionActiva === 'medicacion')
 <section class="animate-in fade-in duration-200">
 @livewire('medicacion.salud-medicacion-panel', key('medicacion'))
 </section>
 @elseif($seccionActiva === 'signos')
 <section class="animate-in fade-in duration-200">
 @livewire('clinica.salud-signos-panel', key('signos'))
 </section>
 @elseif($seccionActiva === 'ficha')
 <section class="animate-in fade-in duration-200">
 @livewire('clinica.salud-ficha-panel', key('ficha-general'))
 </section>
 @elseif($seccionActiva === 'evaluaciones')
 <section class="animate-in fade-in duration-200">
 @livewire('valoraciones.salud-evaluaciones-geriatricas-panel', key('evaluaciones'))
 </section>
 @elseif($seccionActiva === 'nutricion')
 <section class="space-y-5 animate-in fade-in duration-200">
 <div class="rounded-[1.6rem] border border-dashed border-borde/70 bg-fondo-panel p-12 text-center shadow-inner">
  <i class="ph-bold ph-apple-pod text-4xl text-parrafo/25"></i>
  <h3 class="mt-3 text-base font-extrabold text-parrafo">Modulo de Nutricion en desarrollo</h3>
  <p class="mt-1 text-xs font-bold text-parrafo/55">Proximamente podras gestionar los planes nutricionales desde aqui.</p>
 </div>
 </section>
 @else
 <section class="space-y-4 animate-in fade-in duration-200">
 <x-ui.filter-bar class="mb-4">
 <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-[var(--rm-border-soft)] pb-2">
  <div>
  <span class="text-[10px] font-black uppercase tracking-[0.15em] text-[var(--rm-action-primary)] ">{{ $contexto['titulo'] }}</span>
  <h2 class="text-sm font-extrabold text-[var(--rm-text-primary)] ">Seleccionar expediente clínico</h2>
  </div>
 </div>

 {{-- GRID ESTRUCTURAL DE FILTROS UNIFICADA --}}
 <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
  {{-- Buscador Principal --}}
  <div class="lg:col-span-7 relative flex items-center">
  <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-muted)]">
   <i class="ph-bold ph-magnifying-glass text-base"></i>
  </span>
  <input type="text"
   wire:model.live.debounce.300ms="search"
   placeholder="Buscar por nombre, apellido o documento..."
   class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-input-placeholder)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  @if(!empty($search))
   <button type="button"
    wire:click="limpiarFiltro('search')"
    class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-action-primary)] cursor-pointer"
    title="Limpiar búsqueda">
   <i class="ph-bold ph-x-circle text-base"></i>
   </button>
  @endif
  </div>

  {{-- Filtro Estado del Paciente --}}
  <div class="lg:col-span-4">
  <select wire:model.live="filtroEstado"
   class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
   <option value="">Todos los estados clínicos</option>
   <option value="ACTIVO">Activo</option>
   <option value="OBSERVADO">En Observación</option>
   <option value="SEGUIMIENTO_ESPECIAL">Seguimiento Especial</option>
   <option value="ADMITIDO">Admitido</option>
  </select>
  </div>

  {{-- Botón de refresco --}}
  <div class="lg:col-span-1 flex justify-end">
  <button type="button"
   wire:click="$refresh"
   title="Actualizar datos"
   class="w-full h-[38px] flex items-center justify-center rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] hover:text-[var(--rm-action-primary)] transition cursor-pointer">
   <i class="ph-bold ph-arrows-clockwise text-base"></i>
  </button>
  </div>
 </div>

 {{-- Fila de chips de filtros activos --}}
 @php
  $hasFiltrosActivos = !empty($search) || !empty($filtroEstado);
 @endphp
 @if($hasFiltrosActivos)
  <div class="rm-filter-bar__active">
  <div class="flex flex-wrap items-center gap-1.5">
   <span class="rm-filter-bar__active-label">
   <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
   </span>
   @if(!empty($search))
   <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
    <span>Búsqueda: "{{ Str::limit($search, 18) }}"</span>
    <button type="button" wire:click="limpiarFiltro('search')" class="hover:text-[var(--rm-action-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
   @if(!empty($filtroEstado))
   <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-warning)]/15 border border-[var(--rm-warning)]/30 text-[11px] font-bold text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-soft)]">
    <span>Estado: {{ str_replace('_', ' ', $filtroEstado) }}</span>
    <button type="button" wire:click="limpiarFiltro('filtroEstado')" class="hover:text-[var(--rm-action-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
  </div>
  <div class="flex items-center gap-2.5">
   <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[var(--rm-text-primary)]/10 text-[var(--rm-text-primary)]">
   {{ $adultos->total() ?? count($adultos) }} coincidentes
   </span>
   <button type="button"
    wire:click="limpiarFiltros"
    class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-action-primary-soft)] hover:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] py-1 px-2.5 text-xs font-bold transition cursor-pointer">
   <i class="ph-bold ph-arrow-counter-clockwise"></i>
   <span>Limpiar filtros</span>
   </button>
  </div>
  </div>
 @endif
 </x-ui.filter-bar>
 </div>

 <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
 @forelse($adultos as $adulto)
 @php
 $estadoTexto = strtoupper(is_object($adulto->estado) ? ($adulto->estado->estado ?? 'ACTIVO') : ($adulto->estado ?? 'ACTIVO'));
 $ficha = $adulto->fichasMedicas->sortByDesc('updated_at')->first();
 $signo = $adulto->signosVitales->sortByDesc('fecha')->first();
 $valoracion = $adulto->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
 $medicacionesActivas = $adulto->medicaciones->where('estado', 'ACTIVO')->count();
 $edad = $adulto->fecha_nac ? \Carbon\Carbon::parse($adulto->fecha_nac)->age . ' anos' : 'Sin edad';
 $estadoClase = in_array($estadoTexto, ['ACTIVO', 'ACTIVA']) ? 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde' : 'bg-fondo-panel text-apoyo border-borde-suave';
 @endphp

 <article class="group relative overflow-hidden rounded-[1.55rem] border border-borde bg-fondo-panel shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:border-borde-focus hover:shadow-[0_18px_38px_rgba(47,62,92,0.14)]">
 <div class="h-1.5 w-full bg-gradient-to-r from-[var(--rm-danger)] via-[var(--rm-warning)] to-[var(--rm-action-primary)]"></div>
 <div class="relative bg-gradient-to-b from-[var(--rm-border-soft)]/50 to-[var(--rm-surface-soft)]/30 px-5 pb-5 pt-4 text-center">
 <div class="mb-3 flex items-center justify-between gap-2">
 <span class="rounded-full border px-2.5 py-1 text-xs font-bold uppercase tracking-wide {{ $estadoClase }}">{{ $estadoTexto }}</span>
 <span class="rounded-full border border-borde/45 bg-fondo-panel px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-parrafo/55">{{ $adulto->edad ?? 'Edad no registrada' }}{{ $adulto->edad ? ' años' : '' }}</span>
 </div>

 <div class="mx-auto h-20 w-20 overflow-hidden rounded-2xl border-[4px] border-borde bg-boton-principal shadow-md transition group-hover:scale-105">
 @if($adulto->foto)
 <img src="{{ Storage::url($adulto->foto) }}" alt="{{ $adulto->nombres }}" class="h-full w-full object-cover">
 @else
 <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[var(--rm-coffee-800)] to-[var(--rm-coffee-600)] text-xl font-extrabold text-inverso">
 {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
 </div>
 @endif
 </div>
 <h3 class="mt-3 text-base font-extrabold leading-tight text-parrafo">{{ $adulto->nombres }}</h3>
 <p class="text-[11px] font-bold text-parrafo/65">{{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</p>
 </div>

 <div class="space-y-3 px-5 py-4">
 <div class="grid grid-cols-2 gap-2">
 <div class="rounded-xl border border-borde/35 bg-fondo-panel px-3 py-2">
 <p class="text-[10px] font-bold uppercase tracking-wide text-parrafo/55">Edad</p>
 <p class="mt-0.5 text-xs font-bold text-parrafo">{{ $edad }}</p>
 </div>
 <div class="rounded-xl border border-borde/35 bg-fondo-panel px-3 py-2">
 <p class="text-[10px] font-bold uppercase tracking-wide text-parrafo/55">C.I.</p>
 <p class="mt-0.5 truncate text-xs font-bold text-parrafo">{{ $adulto->ci ?: 'S/D' }}</p>
 </div>
 </div>

 @if($seccionActiva === 'ficha')
 <div class="rounded-2xl border border-borde/35 bg-fondo-panel p-3">
 <div class="flex items-center justify-between gap-2">
 <span class="text-[10px] font-bold uppercase tracking-wide text-apoyo">Estado de ficha</span>
 <span class="rounded-full px-2.5 py-0.5 text-xs font-bold uppercase {{ $ficha ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">{{ $ficha ? 'Registrada' : 'Pendiente' }}</span>
 </div>
 <p class="mt-2 text-xs font-bold text-parrafo/62">{{ $ficha ? 'Actualizada ' . $ficha->updated_at->format('d/m/Y') : 'Requiere apertura de expediente medico base.' }}</p>
 </div>
 @elseif($seccionActiva === 'signos')
 <div class="grid grid-cols-3 gap-2 text-center">
 <div class="rounded-xl bg-fondo-panel px-2 py-2">
 <p class="text-[10px] font-bold uppercase text-parrafo/55">P.A.</p>
 <p class="text-xs font-bold text-parrafo">{{ $signo?->presion_formateada ?? 'S/D' }}</p>
 </div>
 <div class="rounded-xl bg-fondo-panel px-2 py-2">
 <p class="text-[10px] font-bold uppercase text-parrafo/55">Temp.</p>
 <p class="text-xs font-bold text-boton-acento">{{ $signo?->temperatura ? number_format($signo->temperatura, 1) . 'C' : 'S/D' }}</p>
 </div>
 <div class="rounded-xl bg-fondo-panel px-2 py-2">
 <p class="text-[10px] font-bold uppercase text-parrafo/55">SpO2</p>
 <p class="text-xs font-bold text-estado-exito">{{ $signo?->saturacion !== null ? $signo->saturacion . '%' : 'S/D' }}</p>
 </div>
 </div>
 @elseif($seccionActiva === 'valoracion')
 <div class="rounded-2xl border border-borde/35 bg-fondo-panel p-3">
 <div class="flex items-center justify-between gap-2">
 <span class="text-[10px] font-bold uppercase tracking-wide text-apoyo">Dependencia</span>
 <span class="rounded-full bg-fondo-panel px-2.5 py-0.5 text-xs font-bold uppercase text-parrafo">{{ $valoracion?->nivel_dependencia ?? 'Sin dato' }}</span>
 </div>
 <p class="mt-2 text-xs font-bold text-parrafo/62">Riesgo de caida: <span class="font-black text-boton-acento">{{ $valoracion?->riesgo_caida ?? 'Sin valorar' }}</span></p>
 </div>
 @else
 <div class="rounded-2xl border border-borde/35 bg-fondo-panel p-3">
 <div class="flex items-center justify-between gap-2">
 <span class="text-[10px] font-bold uppercase tracking-wide text-apoyo">Tratamientos activos</span>
 <span class="rounded-full bg-estado-exitoBg px-2.5 py-0.5 text-xs font-bold uppercase text-estado-exito">{{ $medicacionesActivas }}</span>
 </div>
 <p class="mt-2 text-xs font-bold text-parrafo/62">{{ $medicacionesActivas > 0 ? 'Listo para revisar prescripciones y administraciones.' : 'Sin medicación activa registrada.' }}</p>
 </div>
 @endif
 </div>

 <div class="border-t border-borde/35 bg-fondo-panel p-4">
 <button wire:click="abrirExpediente('{{ $adulto->cod_residente }}')" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold {{ $contexto['icono'] }}"></i>
 {{ $contexto['boton'] }}
 </button>
 </div>
 </article>
 @empty
 <div class="col-span-full rounded-[1.6rem] border border-dashed border-borde/70 bg-fondo-panel p-12 text-center shadow-inner">
 <i class="ph-bold ph-users-three text-4xl text-parrafo/25"></i>
 <h3 class="mt-3 text-base font-extrabold text-parrafo">No se encontraron expedientes</h3>
 <p class="mt-1 text-xs font-bold text-parrafo/55">Ajusta la busqueda o actualiza la vista para revisar otros registros.</p>
 </div>
 @endforelse
 </div>

 <div class="mt-6 flex justify-center">
 {{ $adultos->links() }}
 </div>
 </section>
 @endif
 @endif
 </div>

 {{-- MODAL LATERAL DE EXPEDIENTE INDIVIDUAL --}}
 @if($adultoSeleccionadoParaModal)
 <div class="fixed inset-0 z-[var(--rm-z-drawer,500)] overflow-hidden font-sans" role="dialog" aria-modal="true" aria-labelledby="expediente-seguimiento-title">
 <div class="rm-drawer-backdrop" wire:click="cerrarExpediente"></div>

 <div class="pointer-events-none fixed inset-y-0 right-0 z-[var(--rm-z-drawer,500)] flex max-w-full pl-4 sm:inset-y-3 sm:right-3 sm:pl-10">
 <aside class="salud-slide-panel rm-drawer rm-drawer-wide pointer-events-auto relative flex h-full flex-col overflow-hidden">
 <div class="pointer-events-none absolute inset-0 dash-noise opacity-[0.035]"></div>
 <div class="rm-drawer-header relative z-10 flex-row items-center justify-between px-5 py-4 sm:px-7">
 <div class="flex min-w-0 items-center gap-4">
 <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl border-[3px] border-borde bg-boton-principal shadow-sm">
 @if($adultoSeleccionadoParaModal->foto)
 <img src="{{ Storage::url($adultoSeleccionadoParaModal->foto) }}" alt="{{ $adultoSeleccionadoParaModal->nombres }}" class="h-full w-full object-cover">
 @else
 <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[var(--rm-coffee-800)] to-[var(--rm-coffee-600)] text-sm font-bold text-inverso">
 {{ substr($adultoSeleccionadoParaModal->nombres, 0, 1) }}{{ substr($adultoSeleccionadoParaModal->ap_paterno, 0, 1) }}
 </div>
 @endif
 </div>
 <div class="min-w-0">
 <span class="inline-flex items-center gap-1.5 rounded-full bg-estado-peligroBg px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-boton-acento">
 <i class="ph-bold {{ $contexto['icono'] }}"></i>
 {{ $contexto['titulo'] }}
 </span>
 <h2 id="expediente-seguimiento-title" class="mt-1 truncate text-lg font-extrabold text-[var(--rm-text-primary)]">
 {{ $adultoSeleccionadoParaModal->nombres }} {{ $adultoSeleccionadoParaModal->ap_paterno }} {{ $adultoSeleccionadoParaModal->ap_materno }}
 </h2>
 <p class="text-[10px] font-bold uppercase tracking-wider text-parrafo/45">{{ $adultoSeleccionadoParaModal->ci ? 'CI '.$adultoSeleccionadoParaModal->ci : 'Documento no registrado' }}</p>
 </div>
 </div>
 <button wire:click="cerrarExpediente" type="button" class="rm-btn-icon rm-btn-icon-sm shrink-0" aria-label="Cerrar expediente">
 <i class="ph-bold ph-x text-lg"></i>
 </button>
 </div>

 <div class="rm-drawer-body relative z-10 flex-1 overflow-y-auto p-4 sm:p-6">
 @if($seccionActiva === 'valoracion')
 @livewire('valoraciones.salud-valoracion-panel', ['adulto' => $adultoSeleccionadoParaModal], key('val-'.$adultoSeleccionadoParaModal->cod_residente))
 @elseif($seccionActiva === 'signos')
 @livewire('clinica.salud-signos-panel', ['adulto' => $adultoSeleccionadoParaModal], key('signos-'.$adultoSeleccionadoParaModal->cod_residente))
 @elseif($seccionActiva === 'evaluaciones')
 @livewire('valoraciones.salud-evaluaciones-geriatricas-panel', ['adulto' => $adultoSeleccionadoParaModal], key('eval-'.$adultoSeleccionadoParaModal->cod_residente))
 @elseif($seccionActiva === 'administracion')
 @livewire('medicacion.salud-administracion-medicacion-panel', ['adulto' => $adultoSeleccionadoParaModal], key('adminmed-'.$adultoSeleccionadoParaModal->cod_residente))
 @endif
 </div>
 </aside>
 </div>
 </div>
 @endif
</div>
