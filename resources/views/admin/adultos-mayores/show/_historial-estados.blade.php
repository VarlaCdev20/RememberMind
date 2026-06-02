{{-- TAB HISTORIAL INSTITUCIONAL (RESUMEN) --}}
<section
 x-show="tab === 'historial'"
 style="display: none;"
 x-transition.opacity.duration.250ms
 class="space-y-6"
>
 <!-- HEADER BLOCK -->
 <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-borde pb-5">
 <div class="flex items-center gap-3">
 <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-panel text-parrafo">
 <i class="ph-fill ph-clock-counter-clockwise text-3xl"></i>
 </div>
 <div>
 <h2 class="text-2xl font-black tracking-tight text-titulo">Historial Institucional Resumido</h2>
 <p class="text-sm font-semibold text-apoyo">Últimos movimientos, trazabilidad y cambios de estado.</p>
 </div>
 </div>
 
 <div class="flex items-center gap-2">
 <a href="#" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-card border border-borde px-4 py-2.5 text-xs font-bold text-titulo shadow-sm transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-[0.98]">
 <i class="ph-bold ph-magnifying-glass text-lg"></i>
 Ver Trazabilidad Completa
 </a>
 </div>
 </div>

 <!-- METRICS GRID -->
 <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Estado Actual</p>
 <p class="mt-2 text-xl font-extrabold {{ $estadoTexto === 'ACTIVO' ? 'text-emerald-600' : 'text-parrafo' }}">
 {{ ucfirst(strtolower($estadoTexto)) }}
 </p>
 <p class="text-[10px] font-bold text-apoyo mt-1 uppercase tracking-widest">Desde: {{ $fechaIngresoFormateada }}</p>
 </div>

 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Movimientos Registrados</p>
 <p class="mt-2 text-3xl font-black text-parrafo">
 {{ collect(($bitacoraLista ?? collect())Lista)->count() }}
 </p>
 </div>

 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Última Modificación</p>
 <p class="mt-2 text-sm font-bold text-titulo">
 @if(collect(($bitacoraLista ?? collect())Lista)->count() > 0)
 @php $ultimoMov = collect(($bitacoraLista ?? collect())Lista)->first(); @endphp
 <span class="block text-xs text-apoyo">Por: {{ $ultimoMov['causer'] ?? 'Sistema' }}</span>
 <span class="block mt-1 text-xs font-normal text-apoyo">{{ $ultimoMov['fecha_exacta'] ?? '--' }}</span>
 @else
 --
 @endif
 </p>
 </div>
 </div>

 <!-- ÚLTIMOS MOVIMIENTOS TIMELINE -->
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo border-b border-borde pb-4 mb-4">Últimos Registros Relevantes</h3>
 
 @if(collect(($bitacoraLista ?? collect())Lista)->count() > 0)
 <div class="relative border-l-2 border-borde ml-3 space-y-6 pb-4">
 @foreach(collect(($bitacoraLista ?? collect())Lista)->take(5) as $log)
 <div class="relative pl-6">
 <!-- Nodo del timeline -->
 <div class="absolute -left-[9px] top-1 h-4 w-4 rounded-full border-2 border-white {{ $log['color_bg'] }} shadow-sm"></div>
 
 <div class="rounded-xl border border-borde bg-fondo-panel p-4 transition hover:bg-fondo-panel">
 <div class="flex items-center justify-between gap-4 mb-2">
 <h4 class="text-sm font-bold text-titulo"><i class="{{ $log['icono'] }} {{ $log['color_text'] }} mr-1"></i> {{ $log['etiqueta'] }}</h4>
 <span class="text-[10px] font-bold text-apoyo">{{ $log['fecha_relativa'] }}</span>
 </div>
 
 @if($log['modulo'])
 <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest bg-fondo-panel text-parrafo mb-2">
 {{ $log['modulo'] }}
 </span>
 @endif
 
 <p class="text-xs font-semibold text-apoyo">{{ $log['descripcion'] }}</p>
 
 <div class="mt-3 flex items-center gap-2 text-[10px] font-bold text-apoyo border-t border-borde pt-3">
 <i class="ph-bold ph-user"></i> Autor: <span class="text-apoyo">{{ $log['causer'] }}</span>
 </div>
 </div>
 </div>
 @endforeach
 </div>
 <div class="mt-4 text-center">
 <a href="#" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-card border border-borde px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo shadow-sm transition hover:bg-fondo-panel active:scale-[0.98]">
 Ver Bitácora Completa
 </a>
 </div>
 @else
 <div class="py-8 text-center">
 <i class="ph-fill ph-clock-counter-clockwise text-4xl text-meta mb-3 block"></i>
 <p class="text-sm font-bold text-apoyo">No se encontraron movimientos recientes en el historial institucional.</p>
 </div>
 @endif
 </div>
</section>