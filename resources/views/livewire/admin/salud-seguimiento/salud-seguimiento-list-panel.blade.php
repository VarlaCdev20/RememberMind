<div class="relative mx-auto max-w-7xl space-y-5 overflow-hidden rounded-[2rem] border border-borde/70 bg-fondo-panel p-3 text-parrafo shadow-[0_22px_70px_rgba(47,62,92,0.16)] backdrop-blur-xl sm:p-5 lg:p-6">
 <div class="pointer-events-none absolute inset-0 dash-noise opacity-[0.04]"></div>
 <div class="pointer-events-none absolute inset-x-0 top-0 h-48 bg-gradient-to-b from-[#F8F3ED]/70 to-transparent"></div>
 <div class="pointer-events-none absolute -right-24 top-16 h-72 w-72 rounded-full bg-estado-peligroBg blur-3xl"></div>
 <div class="pointer-events-none absolute -left-24 bottom-20 h-72 w-72 rounded-full bg-estado-exitoBg blur-3xl"></div>

 <div class="relative z-10 space-y-5">
 @if($seccionActiva === 'resumen')
 {{-- ENCABEZADO PRINCIPAL --}}
 <section class="overflow-hidden rounded-[1.75rem] border border-borde/80 bg-gradient-to-br from-[#E6DDD3]/95 via-[#F3ECE4]/92 to-[#D5C7B9]/85 shadow-[0_18px_46px_rgba(47,62,92,0.13)]">
 <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="p-5 sm:p-6">
 <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
 <div class="max-w-3xl">
 <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-boton-acento">
 <i class="ph-bold ph-heartbeat text-sm"></i>
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS - Area clinico asistencial
 </span>
 <h1 class="mt-3 text-2xl font-black tracking-tight text-parrafo sm:text-3xl">
 Salud y Seguimiento
 </h1>
 <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-parrafo/72">
 Panel institucional para revisar fichas medicas, signos vitales, valoraciones funcionales,
 medicacion, administraciones y alertas preventivas de los adultos mayores.
 </p>
 <div class="mt-4 flex flex-wrap gap-2">
 @can('salud.ficha.crear')
 <button wire:click="cambiarSeccion('ficha')" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_10px_24px_rgba(226,125,96,0.25)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-plus-circle text-sm"></i>
 Nuevo registro de salud
 </button>
 @endcan
 <button wire:click="cambiarSeccion('reportes')" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde/80 bg-fondo-panel px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-chart-bar text-sm"></i>
 Reportes
 </button>
 </div>
 </div>

 <div class="grid w-full grid-cols-2 gap-3 sm:grid-cols-3 xl:max-w-xl">
 @php
 $metricasHeader = [
 ['label' => 'Seguimientos activos', 'valor' => $stats['seguimientos_activos'] ?? 0, 'icono' => 'ph-users-three', 'color' => 'text-parrafo', 'bg' => 'bg-fondo-panel'],
 ['label' => 'Alertas pendientes', 'valor' => $stats['alertas_pendientes'] ?? 0, 'icono' => 'ph-warning-circle', 'color' => 'text-boton-acento', 'bg' => 'bg-estado-peligroBg'],
 ['label' => 'Medicaciones activas', 'valor' => $stats['medicaciones_activas'] ?? 0, 'icono' => 'ph-pill', 'color' => 'text-estado-exito', 'bg' => 'bg-estado-exitoBg'],
 ['label' => 'Controles recientes', 'valor' => $stats['signos_recientes'] ?? 0, 'icono' => 'ph-activity', 'color' => 'text-parrafo', 'bg' => 'bg-fondo-panel'],
 ['label' => 'Valoraciones registradas', 'valor' => $stats['valoraciones'] ?? 0, 'icono' => 'ph-person-simple-walk', 'color' => 'text-parrafo', 'bg' => 'bg-fondo-panel'],
 ['label' => 'Controles de hoy', 'valor' => $stats['controles_hoy'] ?? 0, 'icono' => 'ph-calendar-check', 'color' => 'text-boton-acento', 'bg' => 'bg-fondo-panel'],
 ];
 @endphp
 @foreach($metricasHeader as $metrica)
 <div class="relative overflow-hidden rounded-2xl border border-borde/55 {{ $metrica['bg'] }} p-3.5 shadow-sm backdrop-blur-md">
 <i class="ph-bold {{ $metrica['icono'] }} absolute right-3 top-3 text-2xl text-parrafo/10"></i>
 <p class="pr-7 text-[10px] font-bold uppercase leading-tight tracking-[0.12em] text-apoyo">{{ $metrica['label'] }}</p>
 <p class="mt-2 text-2xl font-black leading-none {{ $metrica['color'] }}">{{ $metrica['valor'] }}</p>
 </div>
 @endforeach
 </div>
 </div>
 </div>
 </section>

 {{-- TABS --}}
 <nav class="overflow-x-auto rounded-[1.45rem] border border-borde/70 bg-fondo-panel p-2 shadow-sm backdrop-blur-xl scrollbar-hidden">
 <div class="flex min-w-max items-center gap-2">
 @php
 $tabsRaw = [
 'resumen' => ['label' => 'Resumen clinico', 'icon' => 'ph-squares-four', 'permission' => 'salud.ver'],
 'ficha' => ['label' => 'Ficha medica', 'icon' => 'ph-file-text', 'permission' => 'salud.ficha.ver', 'fallback_permission' => 'ficha_medica.crear'],
 'signos' => ['label' => 'Signos vitales', 'icon' => 'ph-activity', 'permission' => 'salud.signos.ver', 'fallback_permission' => 'signos_vitales.ver'],
 'medicacion' => ['label' => 'Medicacion', 'icon' => 'ph-pill', 'permission' => 'salud.medicacion.ver', 'fallback_permission' => 'medicacion.ver'],
 'administracion' => ['label' => 'Administracion', 'icon' => 'ph-prescription', 'permission' => 'salud.medicacion.ver', 'fallback_permission' => 'administracion_medicacion.registrar'],
 'valoracion' => ['label' => 'Valoracion funcional', 'icon' => 'ph-person-simple-walk', 'permission' => 'salud.ver', 'fallback_permission' => 'valoracion_funcional.crear'],
 'evaluaciones' => ['label' => 'Evaluaciones cognitivas', 'icon' => 'ph-brain', 'permission' => 'evaluaciones.ver'],
 'nutricion' => ['label' => 'Nutricion', 'icon' => 'ph-apple-pod', 'permission' => 'nutricion.ver'],
 'alertas' => ['label' => 'Alertas clinicas', 'icon' => 'ph-warning-circle', 'permission' => 'alertas.ver', 'fallback_permission' => 'salud.alertas.ver'],
 'reportes' => ['label' => 'Reportes clinicos', 'icon' => 'ph-chart-bar', 'permission' => 'reportes.ver', 'fallback_permission' => 'salud.reportes.ver'],
 ];
 
 $tabs = array_filter($tabsRaw, function($tab) {
     if (auth()->user()->hasRole('SUPERADMINISTRADOR')) return true;
     
     $hasPerm = auth()->user()->can($tab['permission']);
     if (isset($tab['fallback_permission']) && !$hasPerm) {
         $hasPerm = auth()->user()->can($tab['fallback_permission']);
     }
     
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
 ['label' => 'Cobertura de fichas', 'valor' => $porcentajeFichas, 'icono' => 'ph-file-text', 'color' => '#E27D60'],
 ['label' => 'Medicacion activa', 'valor' => $porcentajeMedicacion, 'icono' => 'ph-pill', 'color' => '#63775B'],
 ['label' => 'Controles 7 dias', 'valor' => $porcentajeControles, 'icono' => 'ph-activity', 'color' => '#2F3E5C'],
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
 ['key' => 'medicacion', 'label' => 'Medicacion', 'icon' => 'ph-pill'],
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
 @livewire('admin.salud-seguimiento.salud-alertas-panel', key('alertas'))
 </section>
 @elseif($seccionActiva === 'reportes')
 <section class="animate-in fade-in duration-200">
 @livewire('admin.salud-seguimiento.salud-reportes-panel', key('reportes'))
 </section>
 @elseif($seccionActiva === 'medicacion')
 <section class="animate-in fade-in duration-200">
 @livewire('admin.salud-seguimiento.salud-medicacion-panel', key('medicacion'))
 </section>
 @elseif($seccionActiva === 'signos')
 <section class="animate-in fade-in duration-200">
 @livewire('admin.salud-seguimiento.salud-signos-panel', key('signos'))
 </section>
 @elseif($seccionActiva === 'ficha')
 <section class="animate-in fade-in duration-200">
 @livewire('admin.salud-seguimiento.salud-ficha-panel', key('ficha-general'))
 </section>
 @elseif($seccionActiva === 'evaluaciones')
 <section class="animate-in fade-in duration-200">
 @livewire('admin.salud-seguimiento.salud-evaluaciones-geriatricas-panel', key('evaluaciones'))
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
 <section class="space-y-5 animate-in fade-in duration-200">
 <div class="rounded-[1.6rem] border border-borde/65 bg-fondo-panel p-4 shadow-sm backdrop-blur-xl sm:p-5">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
 <div class="max-w-2xl">
 <span class="text-xs font-bold uppercase tracking-[0.15em] text-boton-acento">{{ $contexto['titulo'] }}</span>
 <h2 class="mt-1 text-xl font-extrabold text-parrafo">Seleccionar expediente</h2>
 <p class="mt-1 text-xs font-bold leading-relaxed text-parrafo/62">{{ $contexto['descripcion'] }}</p>
 </div>
 <div class="grid w-full gap-3 sm:grid-cols-[1fr_auto] lg:max-w-xl">
 <label class="relative block">
 <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-parrafo/55">Buscar adulto mayor</span>
 <i class="ph-bold ph-magnifying-glass absolute bottom-3 left-3.5 text-meta"></i>
 <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nombre, apellido o codigo..." class="w-full rounded-xl border border-borde/70 bg-fondo-panel py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition placeholder:text-meta focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
 </label>
 <div class="flex items-end">
 <button type="button" wire:click="$refresh" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-borde/70 bg-fondo-panel px-4 text-xs font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-arrows-clockwise"></i>
 Actualizar
 </button>
 </div>
 </div>
 </div>
 </div>

 <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
 @forelse($adultos as $adulto)
 @php
 $estadoTexto = strtoupper($adulto->estado->estado ?? 'ACTIVO');
 $ficha = $adulto->fichasMedicas->sortByDesc('updated_at')->first();
 $signo = $adulto->signosVitales->sortByDesc('fecha')->first();
 $valoracion = $adulto->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
 $medicacionesActivas = $adulto->medicaciones->where('estado', 'ACTIVO')->count();
 $edad = $adulto->fecha_nac ? \Carbon\Carbon::parse($adulto->fecha_nac)->age . ' anos' : 'Sin edad';
 $estadoClase = in_array($estadoTexto, ['ACTIVO', 'ACTIVA']) ? 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde' : 'bg-fondo-panel text-apoyo border-borde-suave';
 @endphp

 <article class="group relative overflow-hidden rounded-[1.55rem] border border-borde bg-fondo-panel shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:border-borde-focus hover:shadow-[0_18px_38px_rgba(47,62,92,0.14)]">
 <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="relative bg-gradient-to-b from-[#D5C7B9]/72 to-[#E6DDD3]/30 px-5 pb-5 pt-4 text-center">
 <div class="mb-3 flex items-center justify-between gap-2">
 <span class="rounded-full border px-2.5 py-1 text-xs font-bold uppercase tracking-wide {{ $estadoClase }}">{{ $estadoTexto }}</span>
 <span class="rounded-full border border-borde/45 bg-fondo-panel px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-parrafo/55">{{ $adulto->cod_am }}</span>
 </div>

 <div class="mx-auto h-20 w-20 overflow-hidden rounded-2xl border-[4px] border-borde bg-boton-principal shadow-md transition group-hover:scale-105">
 @if($adulto->foto)
 <img src="{{ Storage::url($adulto->foto) }}" alt="{{ $adulto->nombres }}" class="h-full w-full object-cover">
 @else
 <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#2F3E5C] to-[#5B5F97] text-xl font-extrabold text-inverso">
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
 <p class="mt-2 text-xs font-bold text-parrafo/62">{{ $medicacionesActivas > 0 ? 'Listo para revisar prescripciones y administraciones.' : 'Sin medicacion activa registrada.' }}</p>
 </div>
 @endif
 </div>

 <div class="border-t border-borde/35 bg-fondo-panel p-4">
 <button wire:click="abrirExpediente('{{ $adulto->cod_am }}')" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:bg-fondo-panel active:scale-95">
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
 <div class="fixed inset-0 z-[100] flex justify-end">
 <div class="absolute inset-0 bg-fondo-panel backdrop-blur-md" wire:click="cerrarExpediente"></div>

 <aside class="salud-slide-panel relative flex h-full w-full max-w-5xl flex-col overflow-hidden border-l border-borde/70 bg-fondo-app shadow-[-22px_0_60px_rgba(47,62,92,0.28)] sm:rounded-l-[2rem]">
 <div class="pointer-events-none absolute inset-0 dash-noise opacity-[0.035]"></div>
 <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-estado-peligroBg blur-3xl"></div>
 <div class="relative z-10 flex items-center justify-between border-b border-borde bg-fondo-panel px-5 py-4 backdrop-blur-xl sm:px-7">
 <div class="flex min-w-0 items-center gap-4">
 <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl border-[3px] border-borde bg-boton-principal shadow-sm">
 @if($adultoSeleccionadoParaModal->foto)
 <img src="{{ Storage::url($adultoSeleccionadoParaModal->foto) }}" alt="{{ $adultoSeleccionadoParaModal->nombres }}" class="h-full w-full object-cover">
 @else
 <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#2F3E5C] to-[#5B5F97] text-sm font-bold text-inverso">
 {{ substr($adultoSeleccionadoParaModal->nombres, 0, 1) }}{{ substr($adultoSeleccionadoParaModal->ap_paterno, 0, 1) }}
 </div>
 @endif
 </div>
 <div class="min-w-0">
 <span class="inline-flex items-center gap-1.5 rounded-full bg-estado-peligroBg px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-boton-acento">
 <i class="ph-bold {{ $contexto['icono'] }}"></i>
 {{ $contexto['titulo'] }}
 </span>
 <h2 class="mt-1 truncate text-lg font-extrabold text-parrafo">
 {{ $adultoSeleccionadoParaModal->nombres }} {{ $adultoSeleccionadoParaModal->ap_paterno }} {{ $adultoSeleccionadoParaModal->ap_materno }}
 </h2>
 <p class="text-[10px] font-bold uppercase tracking-wider text-parrafo/45">{{ $adultoSeleccionadoParaModal->cod_am }}</p>
 </div>
 </div>
 <button wire:click="cerrarExpediente" type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-borde bg-fondo-panel text-parrafo shadow-sm transition hover:bg-boton-acento hover:text-inverso active:scale-95">
 <i class="ph-bold ph-x text-lg"></i>
 </button>
 </div>

 <div class="relative z-10 flex-1 overflow-y-auto bg-fondo-panel p-4 sm:p-6">
  @if($seccionActiva === 'valoracion')
  @livewire('admin.salud-seguimiento.salud-valoracion-panel', ['adulto' => $adultoSeleccionadoParaModal], key('val-'.$adultoSeleccionadoParaModal->cod_am))
  @elseif($seccionActiva === 'signos')
  @livewire('admin.salud-seguimiento.salud-signos-panel', ['adulto' => $adultoSeleccionadoParaModal], key('signos-'.$adultoSeleccionadoParaModal->cod_am))
  @elseif($seccionActiva === 'evaluaciones')
  @livewire('admin.salud-seguimiento.salud-evaluaciones-geriatricas-panel', ['adulto' => $adultoSeleccionadoParaModal], key('eval-'.$adultoSeleccionadoParaModal->cod_am))
  @elseif($seccionActiva === 'administracion')
  @livewire('admin.salud-seguimiento.salud-administracion-medicacion-panel', ['adulto' => $adultoSeleccionadoParaModal], key('adminmed-'.$adultoSeleccionadoParaModal->cod_am))
  @endif
 </div>
 </aside>
 </div>
 @endif
</div>
