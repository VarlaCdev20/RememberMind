<x-sistema-layout>
 @php
 $colorClasses = [
 'emerald' => ['card' => 'border-emerald-200/70 bg-emerald-50/70', 'icon' => 'bg-emerald-100 text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700'],
 'amber' => ['card' => 'border-amber-200/70 bg-amber-50/70', 'icon' => 'bg-amber-100 text-amber-700', 'badge' => 'bg-amber-100 text-amber-700'],
 'salmon' => ['card' => 'border-borde-focus bg-estado-peligroBg', 'icon' => 'bg-estado-peligroBg text-parrafo', 'badge' => 'bg-estado-peligroBg text-parrafo'],
 'blue' => ['card' => 'border-sky-200/70 bg-sky-50/70', 'icon' => 'bg-sky-100 text-sky-700', 'badge' => 'bg-sky-100 text-sky-700'],
 'green' => ['card' => 'border-estado-exitoBorde bg-estado-exitoBg', 'icon' => 'bg-estado-exitoBg text-parrafo', 'badge' => 'bg-estado-exitoBg text-parrafo'],
 'violet' => ['card' => 'border-violet-200/70 bg-violet-50/70', 'icon' => 'bg-violet-100 text-violet-700', 'badge' => 'bg-violet-100 text-violet-700'],
 'rose' => ['card' => 'border-rose-200/70 bg-rose-50/70', 'icon' => 'bg-rose-100 text-rose-700', 'badge' => 'bg-rose-100 text-rose-700'],
 'indigo' => ['card' => 'border-indigo-200/70 bg-indigo-50/70', 'icon' => 'bg-indigo-100 text-indigo-700', 'badge' => 'bg-indigo-100 text-indigo-700'],
 ];

 $priorityClasses = [
 'Alta' => 'bg-rose-100 text-rose-700 border-rose-200',
 'Media' => 'bg-amber-100 text-amber-700 border-amber-200',
 'Baja' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
 ];

 $nivelColor = [
 'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
 'amber' => 'bg-amber-100 text-amber-700 border-amber-200',
 'rose' => 'bg-rose-100 text-rose-700 border-rose-200',
 ][$estadoSocial['nivel']['color']] ?? 'bg-fondo-app text-titulo border-borde-suave';

 $hasRedChart = array_sum($chartData['red']['data']) > 0;
 $hasVisitasChart = ($chartData['visitas']['available'] ?? false) && array_sum($chartData['visitas']['data'] ?? []) > 0;
 $hasFichaChart = ($chartData['ficha']['available'] ?? false) && array_sum($chartData['ficha']['data'] ?? []) > 0;
 @endphp

 <section class="min-h-[calc(100vh-7rem)] bg-fondo-panel px-3 py-4 text-titulo sm:px-4 lg:px-5">
 <div class="mx-auto max-w-[1480px] space-y-4">
 <div class="overflow-hidden rounded-2xl border border-borde-suave bg-fondo-panel shadow-[0_16px_42px_rgba(47,62,92,0.12)] backdrop-blur-xl">
 <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
 <div class="flex min-w-0 items-start gap-4">
 <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-fondo-panel text-boton-acento shadow-sm">
 <i class="ph-bold ph-users-three text-2xl"></i>
 </span>
 <div class="min-w-0">
 <span class="text-xs font-bold uppercase text-boton-acento">Familia y Social</span>
 <h1 class="mt-1 text-2xl font-black text-titulo sm:text-3xl">Resumen familiar y social</h1>
 <p class="mt-1 max-w-3xl text-sm font-bold leading-relaxed text-apoyo">
 Panel de seguimiento de red de apoyo, visitas, ficha social y estado social de los adultos mayores.
 </p>
 </div>
 </div>

 @can('familiares.ver')
 <div class="flex flex-wrap gap-2">
 @if($rutasSubmodulos['red_apoyo'])
 <a href="{{ $rutasSubmodulos['red_apoyo'] }}" class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-xs font-bold text-titulo shadow-sm transition hover:-translate-y-0.5 hover:border-borde-focus">
 <i class="ph-bold ph-hand-heart text-base text-boton-acento"></i>
 Ver red de apoyo
 </a>
 @endif
 @if($rutasSubmodulos['visitas'])
 <a href="{{ $rutasSubmodulos['visitas'] }}" class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-xs font-bold text-titulo shadow-sm transition hover:-translate-y-0.5 hover:border-borde-focus">
 <i class="ph-bold ph-calendar-check text-base text-estado-exito"></i>
 Ver visitas
 </a>
 @endif
 @if($rutasSubmodulos['ficha_social'])
 <a href="{{ $rutasSubmodulos['ficha_social'] }}" class="inline-flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2 text-xs font-bold text-titulo shadow-sm transition hover:-translate-y-0.5 hover:border-borde-focus">
 <i class="ph-bold ph-clipboard-text text-base text-titulo"></i>
 Ver ficha social
 </a>
 @endif
 </div>
 @endcan
 </div>
 </div>

 <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
 @foreach($metricas as $metrica)
 @php($classes = $colorClasses[$metrica['color']] ?? $colorClasses['salmon'])
 <article class="min-h-[118px] rounded-2xl border {{ $classes['card'] }} p-4 shadow-sm backdrop-blur-sm">
 <div class="flex items-start justify-between gap-3">
 <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $classes['icon'] }}">
 <i class="ph-bold {{ $metrica['icono'] }} text-xl"></i>
 </span>
 <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $classes['badge'] }}">{{ $metrica['badge'] }}</span>
 </div>
 <div class="mt-3">
 <p class="text-2xl font-black leading-none text-titulo">{{ number_format($metrica['valor']) }}</p>
 <h2 class="mt-1 text-sm font-bold text-titulo">{{ $metrica['label'] }}</h2>
 <p class="mt-1 text-xs font-bold leading-snug text-apoyo">{{ $metrica['subtitulo'] }}</p>
 </div>
 </article>
 @endforeach
 </div>

 <div class="grid gap-4 lg:grid-cols-[0.95fr_1.05fr]">
 <section class="rm-chart-card rm-chart-glass">
 <div class="flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Estado social general</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Lectura rapida del acompanamiento familiar y social.</p>
 </div>
 <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $nivelColor }}">
 {{ $estadoSocial['nivel']['texto'] }}
 </span>
 </div>

 <div class="mt-4 space-y-3">
 @foreach([
 ['label' => 'Red de apoyo registrada', 'value' => $estadoSocial['red_apoyo'], 'color' => 'bg-estado-exitoBg'],
 ['label' => 'Adultos sin red de apoyo', 'value' => $estadoSocial['sin_red'], 'color' => 'bg-estado-advertenciaBg'],
 ['label' => 'Ficha social completada', 'value' => $estadoSocial['ficha_social'], 'color' => 'bg-boton-acento'],
 ['label' => 'Visitas recientes', 'value' => $estadoSocial['visitas_recientes'], 'color' => 'bg-boton-principal'],
 ] as $item)
 <div>
 <div class="mb-1 flex items-center justify-between gap-3 text-xs font-bold text-titulo">
 <span>{{ $item['label'] }}</span>
 <span>{{ $item['value'] }}%</span>
 </div>
 <div class="h-2 overflow-hidden rounded-full bg-fondo-panel">
 <div class="h-full rounded-full {{ $item['color'] }}" style="width: {{ $item['value'] }}%"></div>
 </div>
 </div>
 @endforeach
 </div>

 <div class="mt-4 rounded-xl border border-borde-suave bg-fondo-card/35 p-3">
 <div class="flex items-center justify-between gap-3">
 <span class="text-xs font-bold text-apoyo">Indice social consolidado</span>
 <span class="text-lg font-extrabold text-titulo">{{ $estadoSocial['nivel']['score'] }}%</span>
 </div>
 </div>
 </section>

 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3 flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Cobertura de red de apoyo</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Adultos mayores con y sin vinculo familiar activo.</p>
 </div>
 <i class="ph-bold ph-chart-donut text-2xl text-boton-acento"></i>
 </div>
 @if($hasRedChart)
 <div class="h-56">
 <canvas id="familiaRedChart" class="max-h-56"></canvas>
 </div>
 @else
 <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-borde-suave bg-fondo-panel text-center">
 <div>
 <i class="ph-bold ph-chart-pie-slice text-3xl text-apoyo"></i>
 <p class="mt-2 text-sm font-bold text-titulo">No hay datos suficientes para generar este grafico.</p>
 </div>
 </div>
 @endif
 </section>
 </div>

 <div class="grid gap-4 lg:grid-cols-2">
 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3 flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Visitas registradas</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Evolucion mensual de visitas familiares o sociales.</p>
 </div>
 <i class="ph-bold ph-chart-bar text-2xl text-estado-exito"></i>
 </div>
 @if($hasVisitasChart)
 <div class="h-56">
 <canvas id="familiaVisitasChart" class="max-h-56"></canvas>
 </div>
 @else
 <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-borde-suave bg-fondo-panel text-center">
 <div>
 <i class="ph-bold ph-calendar-x text-3xl text-apoyo"></i>
 <p class="mt-2 text-sm font-bold text-titulo">No existen datos suficientes para generar este grafico.</p>
 <p class="mt-1 text-xs font-bold text-apoyo">El panel queda preparado para el submodulo de visitas.</p>
 </div>
 </div>
 @endif
 </section>

 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3 flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Estado de ficha social</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Fichas completas, pendientes y sin registro.</p>
 </div>
 <i class="ph-bold ph-chart-pie text-2xl text-boton-acento"></i>
 </div>
 @if($hasFichaChart)
 <div class="h-56">
 <canvas id="familiaFichaChart" class="max-h-56"></canvas>
 </div>
 @else
 <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-borde-suave bg-fondo-panel text-center">
 <div>
 <i class="ph-bold ph-clipboard-text text-3xl text-apoyo"></i>
 <p class="mt-2 text-sm font-bold text-titulo">La ficha social aun no tiene registros disponibles.</p>
 <p class="mt-1 text-xs font-bold text-apoyo">No se creo ninguna migracion ni dato temporal.</p>
 </div>
 </div>
 @endif
 </section>
 </div>

 <div class="grid gap-4 xl:grid-cols-2">
 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3 flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Alertas sociales</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Casos que requieren atencion administrativa o social.</p>
 </div>
 <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">{{ $alertas->count() }} alertas</span>
 </div>

 <div class="space-y-2">
 @forelse($alertas as $alerta)
 <div class="rounded-xl border border-borde-suave bg-fondo-card/35 p-3">
 <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
 <div class="min-w-0">
 <p class="truncate text-sm font-bold text-titulo">{{ $alerta['adulto'] }}</p>
 <p class="mt-0.5 text-xs font-bold leading-snug text-apoyo">{{ $alerta['motivo'] }}</p>
 </div>
 <div class="flex shrink-0 items-center gap-2">
 <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $priorityClasses[$alerta['prioridad']] ?? $priorityClasses['Media'] }}">{{ $alerta['prioridad'] }}</span>
 <span class="text-[11px] font-bold text-apoyo">{{ $alerta['fecha'] }}</span>
 </div>
 </div>
 </div>
 @empty
 <div class="rounded-xl border border-dashed border-borde-suave bg-fondo-panel p-6 text-center">
 <i class="ph-bold ph-check-circle text-3xl text-emerald-600/45"></i>
 <p class="mt-2 text-sm font-bold text-titulo">No existen alertas sociales pendientes.</p>
 </div>
 @endforelse
 </div>
 </section>

 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3 flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Visitas recientes</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Ultimos registros de acompanamiento familiar o social.</p>
 </div>
 <i class="ph-bold ph-door-open text-2xl text-estado-exito"></i>
 </div>

 <div class="space-y-2">
 @forelse($visitasRecientes as $visita)
 <div class="rounded-xl border border-borde-suave bg-fondo-card/35 p-3">
 <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
 <div class="min-w-0">
 <p class="truncate text-sm font-bold text-titulo">{{ $visita['adulto'] }}</p>
 <p class="mt-0.5 text-xs font-bold text-apoyo">{{ $visita['visitante'] }} · {{ $visita['motivo'] }}</p>
 </div>
 <div class="shrink-0 text-left sm:text-right">
 <p class="text-xs font-bold text-titulo">{{ $visita['fecha'] }}</p>
 <p class="text-[11px] font-bold text-apoyo">{{ $visita['hora'] ?? 'Sin hora' }} · {{ $visita['estado'] }}</p>
 </div>
 </div>
 </div>
 @empty
 <div class="rounded-xl border border-dashed border-borde-suave bg-fondo-panel p-6 text-center">
 <i class="ph-bold ph-calendar-x text-3xl text-apoyo"></i>
 <p class="mt-2 text-sm font-bold text-titulo">No hay visitas registradas recientemente.</p>
 </div>
 @endforelse
 </div>
 </section>
 </div>

 <div class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3 flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Red de apoyo por completar</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Adultos mayores con vinculos, responsables o contactos pendientes.</p>
 </div>
 <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">{{ $redIncompleta->count() }} casos</span>
 </div>

 <div class="overflow-hidden rounded-xl border border-borde-suave">
 @forelse($redIncompleta as $item)
 <div class="flex flex-col gap-3 border-b border-borde-suave bg-fondo-card/30 p-3 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
 <div class="min-w-0">
 <p class="truncate text-sm font-bold text-titulo">{{ $item['adulto'] }}</p>
 <p class="mt-0.5 text-xs font-bold text-apoyo">{{ $item['faltante'] }}</p>
 </div>
 <div class="flex shrink-0 items-center gap-2">
 <span class="rounded-full bg-fondo-app px-2.5 py-1 text-[11px] font-bold text-apoyo">{{ $item['estado'] }}</span>
 @can('adultos.ver')
 @if($item['url'])
 <a href="{{ $item['url'] }}" class="rounded-lg bg-boton-principal px-3 py-1.5 text-xs font-bold text-inverso transition hover:bg-boton-acento">
 Revisar red
 </a>
 @endif
 @endcan
 </div>
 </div>
 @empty
 <div class="bg-fondo-panel p-6 text-center">
 <i class="ph-bold ph-check-circle text-3xl text-emerald-600/45"></i>
 <p class="mt-2 text-sm font-bold text-titulo">No se encontraron adultos mayores con red de apoyo incompleta.</p>
 </div>
 @endforelse
 </div>
 </section>

 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3 flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Ficha social</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Estado de fichas y ultimas actualizaciones sociales.</p>
 </div>
 <i class="ph-bold ph-clipboard-text text-2xl text-boton-acento"></i>
 </div>

 <div class="grid gap-2 sm:grid-cols-3">
 <div class="rounded-xl border border-borde-suave bg-fondo-card/35 p-3">
 <p class="text-lg font-extrabold text-titulo">{{ $fichaSocial['completas'] }}</p>
 <p class="text-[11px] font-bold text-apoyo">Completas</p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-card/35 p-3">
 <p class="text-lg font-extrabold text-titulo">{{ $fichaSocial['pendientes'] }}</p>
 <p class="text-[11px] font-bold text-apoyo">Pendientes</p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-card/35 p-3">
 <p class="text-lg font-extrabold text-titulo">{{ $fichaSocial['sin_registro'] }}</p>
 <p class="text-[11px] font-bold text-apoyo">Sin registro</p>
 </div>
 </div>

 <div class="mt-3 space-y-2">
 @forelse($fichaSocial['ultimas'] as $ficha)
 <div class="rounded-xl border border-borde-suave bg-fondo-card/35 p-3">
 <div class="flex items-start justify-between gap-3">
 <div class="min-w-0">
 <p class="truncate text-sm font-bold text-titulo">{{ $ficha['adulto'] }}</p>
 <p class="mt-0.5 line-clamp-2 text-xs font-bold text-apoyo">{{ $ficha['observacion'] }}</p>
 </div>
 <div class="shrink-0 text-right">
 <p class="text-xs font-bold text-titulo">{{ $ficha['estado'] }}</p>
 <p class="text-[11px] font-bold text-apoyo">{{ $ficha['fecha'] }}</p>
 </div>
 </div>
 </div>
 @empty
 <div class="rounded-xl border border-dashed border-borde-suave bg-fondo-panel p-5 text-center">
 <i class="ph-bold ph-folder-simple-dashed text-3xl text-apoyo"></i>
 <p class="mt-2 text-sm font-bold text-titulo">No existen fichas sociales registradas.</p>
 </div>
 @endforelse
 </div>
 </section>
 </div>

 <div class="grid gap-4 xl:grid-cols-[1fr_0.9fr]">
 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3 flex items-start justify-between gap-3">
 <div>
 <h2 class="text-base font-extrabold text-titulo">Reportes sociales</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Reportes disponibles o preparados para evidencia institucional.</p>
 </div>
 <i class="ph-bold ph-file-chart text-2xl text-titulo"></i>
 </div>

 <div class="grid gap-2 md:grid-cols-2">
 @foreach($reportesSociales as $reporte)
 <div class="rounded-xl border border-borde-suave bg-fondo-card/35 p-3">
 <div class="flex items-start gap-3">
 <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-app text-boton-acento">
 <i class="ph-bold {{ $reporte['icono'] }} text-lg"></i>
 </span>
 <div class="min-w-0 flex-1">
 <div class="flex items-start justify-between gap-2">
 <h3 class="text-sm font-bold text-titulo">{{ $reporte['titulo'] }}</h3>
 <span class="shrink-0 rounded-full bg-fondo-app px-2 py-0.5 text-[10px] font-bold text-apoyo">{{ $reporte['estado'] }}</span>
 </div>
 <p class="mt-1 text-xs font-bold leading-snug text-apoyo">{{ $reporte['descripcion'] }}</p>
 @can($reporte['permiso'])
 @if($reporte['url'])
 <a href="{{ $reporte['url'] }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-boton-acento transition hover:text-titulo">
 Ver reporte
 <i class="ph-bold ph-arrow-right"></i>
 </a>
 @endif
 @endcan
 </div>
 </div>
 </div>
 @endforeach
 </div>
 </section>

 <section class="rm-chart-card rm-chart-glass">
 <div class="mb-3">
 <h2 class="text-base font-extrabold text-titulo">Accesos a submodulos</h2>
 <p class="mt-1 text-xs font-bold text-apoyo">Continuidad operativa del modulo Familia y Social.</p>
 </div>

 <div class="space-y-2">
 @foreach([
 ['titulo' => 'Red de apoyo', 'descripcion' => 'Consulta y vinculacion de familiares/personas de apoyo.', 'url' => $rutasSubmodulos['red_apoyo'], 'icono' => 'ph-hand-heart'],
 ['titulo' => 'Visitas', 'descripcion' => 'Registro y seguimiento de visitas familiares/sociales.', 'url' => $rutasSubmodulos['visitas'], 'icono' => 'ph-calendar-check'],
 ['titulo' => 'Ficha social', 'descripcion' => 'Informacion social, familiar y de contexto del adulto mayor.', 'url' => $rutasSubmodulos['ficha_social'], 'icono' => 'ph-clipboard-text'],
 ] as $acceso)
 <a href="{{ $acceso['url'] ?? '#' }}" class="flex items-center gap-3 rounded-xl border border-borde-suave bg-fondo-card/35 p-3 transition hover:-translate-y-0.5 hover:border-borde-focus hover:bg-fondo-card/55">
 <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-fondo-app text-boton-acento">
 <i class="ph-bold {{ $acceso['icono'] }} text-xl"></i>
 </span>
 <span class="min-w-0 flex-1">
 <span class="block text-sm font-bold text-titulo">{{ $acceso['titulo'] }}</span>
 <span class="block text-xs font-bold leading-snug text-apoyo">{{ $acceso['descripcion'] }}</span>
 </span>
 <i class="ph-bold ph-caret-right shrink-0 text-apoyo"></i>
 </a>
 @endforeach
 </div>
 </section>
 </div>
 </div>
 </section>

 <script>
const rmDatosff6fbf562949 = @json($chartData);
{!! file_get_contents(resource_path('frontend/scripts/modules/pages-familia-social-resumen.js')) !!}
</script>
</x-sistema-layout>
