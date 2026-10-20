<div class="space-y-6">

 {{-- Breadcrumb + Encabezado --}}
 <nav class="flex text-[10px] font-bold uppercase tracking-widest text-meta" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1">
   <li>
   <a href="{{ route('admin.psicologia.dashboard') }}" class="hover:text-parrafo">
    Psicología
   </a>
   </li>
   <li class="flex items-center">
   <i class="ph-bold ph-caret-right mx-1"></i>
   <span class="text-apoyo">{{ $nombreArea }}</span>
   </li>
  </ol>
 </nav>
 <x-ui.collection-header :title="$nombreArea" :subtitle="$descripcionArea" :icon="$areaConfig['icono']" eyebrow="Psicología">
 <x-slot:actions>
  <a href="{{ route('admin.psicologia.dashboard') }}"
  class="inline-flex items-center gap-2 rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-panel">
  <i class="ph-bold ph-arrow-left"></i> Volver
  </a>
  @if($instrumentosActivos->isNotEmpty() && auth()->user()?->can('aplicaciones_instrumento.crear'))
  <button wire:click="nuevaEvaluacion()"
   class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:scale-95">
  <i class="ph-bold ph-plus-circle text-sm"></i> Nueva Evaluación
  </button>
  @endif
 </x-slot:actions>
 </x-ui.collection-header>

 @if($codArea === 'ARE_COG' && auth()->user()?->hasRole('PSICOLOGO/A'))
 <x-ui.section-card title="Valoración del sistema experto" subtitle="Los registros observacionales se muestran aquí; la valoración original de Petersen se registra por residente" icon="ph-brain">
  <a href="{{ route('admin.sistema-experto.residentes') }}" class="rm-btn rm-btn-primary">Elegir residente para el sistema experto</a>
 </x-ui.section-card>
 @endif

 {{-- Stats del área --}}
 <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
 <x-ui.metric-card icon="ph-list-checks" :value="$totalEvaluaciones" label="Evaluaciones" variant="neutral" />
 <x-ui.metric-card icon="ph-user-check" :value="$pacientesCubiertos" label="Residentes cubiertos" variant="mint" />
 <x-ui.metric-card icon="ph-warning-circle" :value="$alertasCriticas" label="Alertas críticas" :variant="$alertasCriticas > 0 ? 'coral' : 'neutral'" />
 </div>

 {{-- Instrumentos del área --}}
 @if($instrumentos->count() > 0)
 <details class="rounded-2xl border border-borde bg-fondo-panel p-4" @if($instrumentos->count() <= 3) open @endif>
 <summary class="cursor-pointer text-sm font-semibold text-parrafo">Instrumentos del área · {{ $instrumentos->count() }} registros de catálogo</summary>
 <div class="mt-3 flex flex-wrap gap-2">
  @foreach($instrumentos as $inst)
  <span class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1 text-[11px] font-bold text-parrafo">
  <span class="{{ $areaConfig['color_txt'] }} font-black">{{ $inst->siglas }}</span>
  {{ $inst->nombre }}
  @if($inst->estado !== 'ACTIVO')<span class="text-apoyo">Histórico · no disponible para nueva aplicación</span>@endif
  @if($inst->puntaje_maximo)
  <span class="text-apoyo">/ {{ number_format($inst->puntaje_maximo, 0) }} pts</span>
  @endif
  </span>
  @endforeach
 </div>
 </details>
 @endif

     {{-- Filtros --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Búsqueda textual --}}
            <div class="lg:col-span-6">
                <label for="evaluaciones-buscar" class="rm-collection-filter-label">Buscar residente</label>
                <div class="relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input id="evaluaciones-buscar" wire:model.live.debounce.300ms="busqueda"
                    type="text"
                    placeholder="Buscar por nombre del residente o CI..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if(!empty($busqueda))
                    <button type="button"
                        wire:click="$set('busqueda', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
                </div>
            </div>

            {{-- Filtro Alerta --}}
            <div class="lg:col-span-3">
                <label for="evaluaciones-alerta" class="rm-collection-filter-label">Nivel de alerta</label>
                <select id="evaluaciones-alerta" wire:model.live="filtroAlerta"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los niveles</option>
                    <option value="NORMAL">Normal</option>
                    <option value="PREVENTIVO">Preventivo</option>
                    <option value="CRITICO">Crítico</option>
                </select>
            </div>

            {{-- Filtro Instrumento --}}
            <div class="lg:col-span-3">
                <label for="evaluaciones-instrumento" class="rm-collection-filter-label">Instrumento</label>
                <select id="evaluaciones-instrumento" wire:model.live="filtroInstrumento"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los instrumentos</option>
                    @foreach($instrumentos as $inst)
                        <option value="{{ $inst->cod_instrumento }}">{{ $inst->siglas }} — {{ $inst->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($busqueda) || !empty($filtroAlerta) || !empty($filtroInstrumento);
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($busqueda))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($busqueda, 16) }}"</span>
                            <button type="button" wire:click="$set('busqueda', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroAlerta))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Alerta: {{ $filtroAlerta }}</span>
                            <button type="button" wire:click="$set('filtroAlerta', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroInstrumento))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Instrumento seleccionado</span>
                            <button type="button" wire:click="$set('filtroInstrumento', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="$set('busqueda', ''); $set('filtroAlerta', ''); $set('filtroInstrumento', '')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

 @if($instrumentos->isEmpty())
 <p class="text-sm text-rm-secondary" role="status">El catálogo actual no tiene instrumentos asociados a esta área. Consulte los registros existentes desde la ficha del residente; aquí no se puede iniciar una aplicación de esta área.</p>
 @elseif($instrumentosActivos->isEmpty())
 <p class="text-sm text-rm-secondary" role="status">Las aplicaciones históricas de esta área siguen visibles. Sus instrumentos están inactivos para nuevas aplicaciones.</p>
 @endif

 {{-- Tabla de evaluaciones --}}
 <div class="rm-table-container rounded-3xl border border-borde bg-fondo-card shadow-sm overflow-hidden">
 @if($evaluaciones->count() > 0)
 <div class="overflow-x-auto">
  <table class="rm-data-table rm-data-table--actions rm-table w-full text-left text-sm whitespace-nowrap">
  <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
   <tr>
   <th class="px-5 py-3">Residente</th>
   <th class="px-5 py-3">Instrumento</th>
   <th class="px-5 py-3">Puntaje</th>
   <th class="px-5 py-3">Resultado / Categoría</th>
   <th class="px-5 py-3">Clasificación registrada</th>
   <th class="px-5 py-3">Evaluador</th>
   <th class="px-5 py-3">Fecha</th>
   @if($instrumentosActivos->isNotEmpty() && auth()->user()?->can('aplicaciones_instrumento.crear'))<th class="px-5 py-3 text-center">Acción</th>@endif
   </tr>
  </thead>
  <tbody class="divide-y divide-borde/50">
   @foreach($evaluaciones as $eval)
   @php
   $alertaClass = match($eval->clasificacion) {
    'CRITICO' => 'bg-estado-peligro text-white',
    'PREVENTIVO' => 'bg-estado-advertencia text-white',
    'NORMAL' => 'bg-estado-exitoBg text-estado-exito',
    default => 'bg-fondo-panel text-parrafo',
   };
   @endphp
   <tr class="hover:bg-fondo-panel/50 transition-colors">
   <td class="px-5 py-3">
    <div class="flex items-center gap-2.5">
    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $areaConfig['color_bg'] }} {{ $areaConfig['color_txt'] }} text-sm font-black">
     {{ mb_substr($eval->adulto?->nombres ?? '?', 0, 1) }}{{ mb_substr($eval->adulto?->apellido_paterno ?? '', 0, 1) }}
    </div>
    <div>
     <div class="font-bold text-titulo">
     {{ $eval->adulto?->nombres ?? '—' }} {{ $eval->adulto?->apellido_paterno ?? '' }}
     </div>
     <div class="text-[10px] text-apoyo">Documento: {{ $eval->adulto?->numero_documento ?? '—' }}</div>
    </div>
    </div>
   </td>
   <td class="px-5 py-3">
    <div class="font-bold text-titulo">{{ $eval->instrumento?->codigo ?? '—' }}</div>
    <div class="text-[10px] text-apoyo max-w-[160px] truncate">{{ $eval->instrumento?->nombre ?? '' }}</div>
   </td>
   <td class="px-5 py-3 font-black text-titulo">
    {{ $eval->puntaje_total !== null ? number_format($eval->puntaje_total, 0) : '—' }}
    @if($eval->instrumento?->puntaje_maximo)
    <span class="text-[10px] font-semibold text-apoyo">/ {{ number_format($eval->instrumento->puntaje_maximo, 0) }}</span>
    @endif
   </td>
   <td class="px-5 py-3 text-xs font-semibold text-parrafo max-w-[160px] truncate"
    title="{{ $eval->interpretacion ?? '' }}">
    {{ $eval->interpretacion ?? '—' }}
   </td>
   <td class="px-5 py-3">
    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $alertaClass }}">
    {{ $eval->clasificacion ?? 'Sin clasificación' }}
    </span>
   </td>
   <td class="px-5 py-3 text-xs text-apoyo">
    {{ $eval->evaluador?->nombres ?? '—' }} {{ $eval->evaluador?->apellido_paterno ?? '' }}
   </td>
   <td class="px-5 py-3 text-xs font-medium text-apoyo">
    {{ $eval->fecha_hora?->format('d/m/Y H:i') ?? '—' }}
   </td>
   @if($instrumentosActivos->isNotEmpty() && auth()->user()?->can('aplicaciones_instrumento.crear'))
   <td class="px-5 py-3 text-center">
    <button wire:click="nuevaEvaluacion('{{ $eval->adulto?->cod_residente }}')"
     title="Nueva evaluación para este residente"
     class="h-8 w-8 rounded-lg bg-fondo-panel text-parrafo hover:bg-boton-acento hover:text-white transition-colors flex items-center justify-center mx-auto">
    <i class="ph-bold ph-plus text-sm"></i>
    </button>
   </td>
   @endif
   </tr>
   @endforeach
  </tbody>
  </table>
 </div>
 <div class="border-t border-borde px-5 py-3">
  {{ $evaluaciones->links() }}
 </div>
 @else
 <div class="flex flex-col items-center justify-center py-16 text-center">
  <div class="flex h-16 w-16 items-center justify-center rounded-2xl {{ $areaConfig['color_bg'] }} {{ $areaConfig['color_txt'] }}">
  <i class="ph-bold {{ $areaConfig['icono'] }} text-3xl"></i>
  </div>
  <h3 class="mt-4 text-lg font-bold text-titulo">
  Sin evaluaciones registradas
  @if($busqueda || $filtroAlerta || $filtroInstrumento)
   con estos filtros
  @endif
  </h3>
  <p class="mt-1 text-sm text-apoyo max-w-sm">
  @if($busqueda || $filtroAlerta || $filtroInstrumento)
   Intenta modificar los filtros de búsqueda.
  @else
   @if($instrumentosActivos->isNotEmpty())Registre la primera aplicación vigente de <strong>{{ $nombreArea }}</strong> para un residente.
   @elseif($instrumentos->isNotEmpty())No hay aplicaciones de esta área en el período consultado. Sus instrumentos históricos no admiten nuevas aplicaciones.
   @else No hay instrumentos asociados a esta área en el catálogo actual.@endif
  @endif
  </p>
  @if($instrumentosActivos->isNotEmpty() && !$busqueda && !$filtroAlerta && !$filtroInstrumento && auth()->user()?->can('aplicaciones_instrumento.crear'))
  <button wire:click="nuevaEvaluacion()"
   class="mt-5 inline-flex items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-md transition hover:-translate-y-0.5 active:scale-95">
  <i class="ph-bold ph-plus-circle text-sm"></i> Nueva Evaluación
  </button>
  @endif
 </div>
 @endif
 </div>

 {{-- Observaciones clínicas por área --}}
 <div class="rounded-2xl border border-borde bg-fondo-panel p-5">
 <div class="flex items-start gap-3">
  <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $areaConfig['color_bg'] }} {{ $areaConfig['color_txt'] }}">
  <i class="ph-bold ph-info text-base"></i>
  </div>
  <div>
  <p class="text-xs font-black uppercase tracking-wider text-titulo mb-1">Sobre los registros de {{ $nombreArea }}</p>
  @php
   $guias = [
   'ARE_COG' => 'Las tareas observacionales del catálogo describen componentes cognitivos. No equivalen por sí mismas a una prueba estandarizada ni acreditan un patrón DCL.',
   'ARE_FUN' => 'El Índice de Barthel describe actividades básicas. La independencia instrumental para Petersen requiere una valoración IADL original y contextual.',
   ];
  @endphp
  <p class="text-xs font-semibold text-parrafo leading-relaxed">
   {{ $guias[$codArea] ?? 'No hay un instrumento asociado a esta área en el catálogo actual. Consulte las fuentes originales y el método del instrumento antes de registrar una aplicación.' }}
  </p>
  </div>
 </div>
 </div>

 {{-- Modal de nueva evaluación --}}
 @if($instrumentosActivos->isNotEmpty() && auth()->user()?->can('aplicaciones_instrumento.crear'))
 @livewire('valoraciones.evaluacion-geriatrica-area-modal')
 @endif

</div>
