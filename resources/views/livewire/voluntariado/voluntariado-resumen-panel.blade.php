<div class="min-h-screen bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8">
 <div class="mx-auto max-w-7xl space-y-6">
 <section class="overflow-hidden rounded-[1.65rem] border border-borde-suave bg-fondo-panel shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-end lg:justify-between">
 <div class="max-w-3xl">
 <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">
 <i class="ph-bold ph-hand-heart text-sm"></i>
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
 </span>
 <h1 class="mt-3 text-3xl font-black tracking-tight text-titulo sm:text-4xl">
 Voluntariado
 </h1>
 <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
 Gestión de voluntarios, disponibilidad, asignaciones, asistencia y reportes institucionales.
 </p>
 </div>

 <div class="flex items-center gap-3 rounded-2xl border border-borde-suave bg-fondo-panel px-4 py-3">
 <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-boton-principal text-inverso shadow-sm">
 <i class="ph-bold ph-users-three text-xl"></i>
 </span>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-apoyo">Equipo activo</p>
 <p class="text-2xl font-black leading-none text-titulo">{{ number_format($stats['voluntarios_activos']) }}</p>
 </div>
 </div>
 </div>
 </section>

 <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
 @php
 $tonoClases = [
 'azul' => ['icono' => 'bg-fondo-panel text-titulo', 'valor' => 'text-titulo', 'linea' => 'bg-boton-principal'],
 'verde' => ['icono' => 'bg-estado-exitoBg text-estado-exito', 'valor' => 'text-estado-exito', 'linea' => 'bg-estado-exitoBg'],
 'terracota' => ['icono' => 'bg-estado-peligroBg text-boton-acento', 'valor' => 'text-boton-acento', 'linea' => 'bg-boton-acento'],
 'dorado' => ['icono' => 'bg-estado-advertenciaBg text-estado-advertencia', 'valor' => 'text-estado-advertencia', 'linea' => 'bg-estado-advertenciaBg'],
 'neutro' => ['icono' => 'bg-fondo-panel text-meta', 'valor' => 'text-meta', 'linea' => 'bg-fondo-panel'],
 ];
 @endphp

 @foreach($metricas as $metrica)
 @php($tono = $tonoClases[$metrica['tono']] ?? $tonoClases['azul'])
 <article class="relative min-h-[128px] overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel p-4 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:border-borde-focus hover:shadow-[0_16px_34px_rgba(47,62,92,0.11)]">
 <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
 <div class="flex items-start justify-between gap-3">
 <p class="max-w-[11rem] text-[10px] font-bold uppercase leading-snug tracking-[0.15em] text-apoyo">
 {{ $metrica['label'] }}
 </p>
 <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
 <i class="ph-bold {{ $metrica['icono'] }} text-xl"></i>
 </span>
 </div>
 <p class="mt-5 text-3xl font-black leading-none {{ $tono['valor'] }}">
 {{ number_format($metrica['valor']) }}
 </p>
 </article>
 @endforeach
 </section>

 @if($sinDatos)
 <section class="rounded-[1.5rem] border border-dashed border-borde-suave bg-fondo-panel p-8 text-center shadow-inner">
 <i class="ph-bold ph-hand-heart text-4xl text-apoyo"></i>
 <h2 class="mt-3 text-base font-extrabold text-titulo">Resumen sin registros operativos</h2>
 <p class="mx-auto mt-1 max-w-xl text-xs font-bold leading-relaxed text-apoyo">
 Cuando existan voluntarios, disponibilidades, asignaciones o asistencias, este panel consolidará los indicadores principales.
 </p>
 </section>
 @endif

 <section class="rounded-[1.5rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
 <div class="mb-5 flex items-center justify-between gap-3">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-boton-acento">Flujo operativo</span>
 <h2 class="mt-1 text-lg font-extrabold text-titulo">Secuencia institucional de voluntariado</h2>
 </div>
 <i class="ph-bold ph-flow-arrow text-2xl text-boton-acento"></i>
 </div>

 <div class="grid gap-3 md:grid-cols-5">
 @foreach($flujoOperativo as $paso)
 <div class="relative rounded-2xl border border-borde-suave bg-fondo-panel px-4 py-4">
 <div class="flex items-center gap-3">
 <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-panel text-titulo">
 <i class="ph-bold {{ $paso['icono'] }} text-lg"></i>
 </span>
 <p class="text-xs font-bold leading-snug text-titulo">{{ $paso['label'] }}</p>
 </div>

 @if(! $loop->last)
 <span class="absolute -right-2 top-1/2 z-10 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full border border-borde-suave bg-fondo-app text-boton-acento md:flex">
 <i class="ph-bold ph-caret-right text-xs"></i>
 </span>
 @endif
 </div>
 @endforeach
 </div>
 </section>

 <section class="space-y-4">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-boton-acento">Submódulos</span>
 <h2 class="mt-1 text-lg font-extrabold text-titulo">Acceso organizado</h2>
 </div>

 <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
 @foreach($submodulos ?? [] as $item)
 <a href="{{ $item['url'] }}"
 class="group min-h-[160px] rounded-2xl border p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.11)] {{ $item['activo'] ? 'border-borde-focus bg-estado-peligroBg' : 'border-borde-suave bg-fondo-panel hover:border-borde-focus' }}"
 title="{{ $item['label'] }}">
 <div class="flex items-start justify-between gap-3">
 <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $item['activo'] ? 'bg-boton-acento text-inverso' : 'bg-fondo-panel text-titulo group-hover:bg-estado-peligroBg group-hover:text-boton-acento' }}">
 <i class="ph-bold {{ $item['icono'] }} text-xl"></i>
 </span>
 <span class="rounded-full bg-fondo-panel px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-apoyo">
 {{ $item['dato'] }}
 </span>
 </div>
 <h3 class="mt-4 text-sm font-bold text-titulo">{{ $item['label'] }}</h3>
 <p class="mt-2 text-xs font-bold leading-relaxed text-apoyo">{{ $item['descripcion'] }}</p>
 </a>
 @endforeach
 </div>
 </section>

 <section class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
 <div class="rounded-[1.5rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
 <div class="mb-4 flex items-center justify-between gap-3">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-boton-acento">Agenda</span>
 <h2 class="mt-1 text-lg font-extrabold text-titulo">Próximas asignaciones</h2>
 </div>
 <i class="ph-bold ph-calendar-check text-2xl text-estado-exito"></i>
 </div>

 <div class="space-y-3">
 @forelse($proximasAsignaciones as $asignacion)
 <div class="flex flex-col gap-3 rounded-2xl border border-borde-suave bg-fondo-panel px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
 <div class="min-w-0">
 <div class="flex flex-wrap items-center gap-2">
 <span class="rounded-full bg-fondo-panel px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-titulo">
 {{ $asignacion['relativa'] }} · {{ $asignacion['fecha'] }}
 </span>
 <span class="rounded-full bg-estado-exitoBg px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-estado-exito">
 {{ $asignacion['estado'] }}
 </span>
 </div>
 <p class="mt-2 truncate text-sm font-bold text-titulo">{{ $asignacion['voluntario'] }}</p>
 <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">
 {{ $asignacion['adulto'] }} · {{ $asignacion['area'] }}
 </p>
 </div>
 <span class="shrink-0 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-apoyo">
 {{ $asignacion['fecha'] }}
 </span>
 </div>
 @empty
 <div class="rounded-2xl border border-dashed border-borde-suave bg-fondo-panel p-8 text-center">
 <i class="ph-bold ph-calendar-blank text-4xl text-apoyo"></i>
 <h3 class="mt-3 text-sm font-bold text-titulo">Sin próximas asignaciones</h3>
 <p class="mt-1 text-xs font-bold text-apoyo">No hay asignaciones vigentes o programadas para mostrar.</p>
 </div>
 @endforelse
 </div>
 </div>

 <div class="rounded-[1.5rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm backdrop-blur-xl">
 <div class="mb-4 flex items-center justify-between gap-3">
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-boton-acento">Seguimiento</span>
 <h2 class="mt-1 text-lg font-extrabold text-titulo">Alertas operativas</h2>
 </div>
 <i class="ph-bold ph-warning-circle text-2xl text-boton-acento"></i>
 </div>

 <div class="space-y-3">
 @forelse($alertasOperativas as $alerta)
 <div class="rounded-2xl border border-borde-suave bg-fondo-panel p-4">
 <div class="flex gap-3">
 <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $alerta['tono'] === 'terracota' ? 'bg-estado-peligroBg text-boton-acento' : ($alerta['tono'] === 'dorado' ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-fondo-panel text-titulo') }}">
 <i class="ph-bold {{ $alerta['icono'] }} text-lg"></i>
 </span>
 <div class="min-w-0">
 <h3 class="text-sm font-bold text-titulo">{{ $alerta['titulo'] }}</h3>
 <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">{{ $alerta['detalle'] }}</p>
 </div>
 </div>
 </div>
 @empty
 <div class="rounded-2xl border border-estado-exitoBorde bg-estado-exitoBg p-8 text-center">
 <i class="ph-bold ph-check-circle text-4xl text-estado-exito"></i>
 <h3 class="mt-3 text-sm font-bold text-titulo">Sin alertas operativas</h3>
 <p class="mt-1 text-xs font-bold text-apoyo">Los indicadores del resumen no requieren seguimiento inmediato.</p>
 </div>
 @endforelse
 </div>
 </div>
 </section>
 </div>
</div>
