<div class="space-y-6 rm-valoraciones-area" x-data="{ errorSolicitud: '' }" @rm-valoraciones-error="errorSolicitud = $event.detail.message" @rm-valoraciones-inicio="errorSolicitud = ''">
 <p x-show="errorSolicitud" x-cloak x-text="errorSolicitud" role="alert" class="rm-error"></p>

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
  @if($instrumentosActivos->isNotEmpty() && $puedeCapturar)
  <button wire:click="nuevaEvaluacion()"
   class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:scale-95">
  <i class="ph-bold ph-plus-circle text-sm"></i> Nueva Evaluación
  </button>
  @endif
 </x-slot:actions>
 </x-ui.collection-header>

 <nav class="rm-valoraciones-areas" aria-label="Áreas de valoración">
 @foreach(['cognitiva' => ['ARE_COG', 'Cognitiva', 'ph-brain'], 'afectiva' => ['ARE_AFE', 'Afectiva', 'ph-heart'], 'funcionamiento' => ['ARE_FUN', 'Funcionamiento', 'ph-person-simple-walk'], 'entorno' => ['ARE_SOC', 'Entorno y red social', 'ph-users-four']] as $rutaArea => [$codigoArea, $tituloArea, $iconoArea])
 <a href="{{ route('admin.psicologia.evaluacion.'.$rutaArea) }}" @if($codArea === $codigoArea) aria-current="page" @endif><i class="ph-bold {{ $iconoArea }}" aria-hidden="true"></i>{{ $tituloArea }}</a>
 @endforeach
 </nav>

 {{-- Stats del área --}}
 <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
 <x-ui.metric-card icon="ph-list-checks" :value="$totalEvaluaciones" label="Evaluaciones" variant="neutral" />
 <x-ui.metric-card icon="ph-user-check" :value="$pacientesCubiertos" label="Residentes cubiertos" variant="mint" />
 <x-ui.metric-card icon="ph-warning-circle" :value="$alertasCriticas" label="Alertas críticas" :variant="$alertasCriticas > 0 ? 'coral' : 'neutral'" />
 </div>

 @include('valoraciones::livewire.valoraciones.explorador-instrumentos')

 @if($codArea === 'ARE_COG' && auth()->user()?->hasRole('PSICOLOGO/A') && auth()->user()?->can('valoraciones_petersen.crear'))
 <x-ui.section-card title="Valoración cognitiva y funcional" subtitle="Seleccione al residente para documentar su valoración profesional y los registros que la sustentan" icon="ph-brain">
  <p class="text-sm text-rm-secondary">Aquí se documentan los cinco dominios cognitivos y los requisitos originales de Petersen. C02 se obtiene de los dominios; no se registra manualmente.</p>
  <div class="mt-4 max-w-lg">
   <label for="buscar-residente-valoracion" class="block text-sm font-semibold">Buscar residente por nombre</label>
   <input id="buscar-residente-valoracion" type="search" wire:model.live.debounce.300ms="busquedaValoracion" maxlength="80" autocomplete="off" placeholder="Nombre o apellido" class="rm-input w-full">
  </div>
  @if($residentesValoracion->isEmpty())
   <p class="mt-4 text-sm text-rm-secondary" role="status">No se encontraron residentes admitidos disponibles. Revise el nombre buscado.</p>
  @else
   <ul class="mt-4 grid gap-3 md:grid-cols-2" aria-label="Residentes para valoración cognitiva y funcional">
    @foreach($residentesValoracion as $residenteValoracion)
     <li wire:key="valoracion-cognitiva-{{ $residenteValoracion->getKey() }}" class="flex min-w-0 flex-wrap items-center justify-between gap-3 rounded-xl border border-rm p-3">
      <div class="min-w-0"><p class="break-words font-semibold">{{ $residenteValoracion->nombre_completo }}</p><p class="text-sm text-rm-secondary">{{ $residenteValoracion->edad !== null ? $residenteValoracion->edad.' años' : 'Edad no registrada' }}</p></div>
      <a href="{{ route('admin.sistema-experto.petersen-original', $residenteValoracion) }}" class="rm-btn rm-btn-primary">Abrir valoración<span class="sr-only"> cognitiva y funcional de {{ $residenteValoracion->nombre_completo }}</span></a>
     </li>
    @endforeach
   </ul>
   <p class="mt-3 text-sm text-rm-secondary">Se muestran hasta ocho residentes. Busque por nombre para encontrar otro.</p>
  @endif
 </x-ui.section-card>
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
                <x-ui.selector id="evaluaciones-alerta" label="Nivel de alerta" wire:model.live="filtroAlerta">
                    <option value="">Todos los niveles</option>
                    <option value="NORMAL">Normal</option>
                    <option value="PREVENTIVO">Preventivo</option>
                    <option value="CRITICO">Crítico</option>
                </x-ui.selector>
            </div>

            {{-- Filtro Instrumento --}}
            <div class="lg:col-span-3">
                <label for="evaluaciones-instrumento" class="rm-collection-filter-label">Instrumento</label>
                <x-ui.selector id="evaluaciones-instrumento" label="Instrumento" wire:model.live="filtroInstrumento">
                    <option value="">Todos los instrumentos</option>
                    @foreach($instrumentos as $inst)
                        <option value="{{ $inst->cod_instrumento }}">{{ $inst->siglas }} — {{ $inst->nombre }}</option>
                    @endforeach
                </x-ui.selector>
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

 <h2 class="rm-explorador__titulo-historial">Historial de aplicaciones</h2>
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
   <th class="px-5 py-3">Profesional y fuente original</th>
   <th class="px-5 py-3">Fecha</th>
   @if($instrumentosActivos->isNotEmpty() && $puedeCapturar)<th class="px-5 py-3 text-center">Acción</th>@endif
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
    {{ $eval->puntaje_total !== null ? number_format($eval->puntaje_total, 2) : '—' }}
    @if($eval->puntaje_maximo !== null)
    <span class="text-[10px] font-semibold text-apoyo">/ {{ number_format($eval->puntaje_maximo, 2) }}</span>
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
    {{ $eval->evaluador?->nombres ?? '—' }} {{ $eval->evaluador?->apellido_paterno ?? '' }}<p>{{ $eval->cod_documento_clinico ? 'Documento original asociado' : 'Registro de aplicación' }}</p>@if($eval->localizador_fuente)<p>{{ $eval->localizador_fuente }}</p>@endif
   </td>
   <td class="px-5 py-3 text-xs font-medium text-apoyo">
    {{ $eval->fecha_hora?->format('d/m/Y H:i') ?? '—' }}
   </td>
   @if($instrumentosActivos->isNotEmpty() && $puedeCapturar)
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
  @if($instrumentosActivos->isNotEmpty() && !$busqueda && !$filtroAlerta && !$filtroInstrumento && $puedeCapturar)
  <button wire:click="nuevaEvaluacion()"
   class="mt-5 inline-flex items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-md transition hover:-translate-y-0.5 active:scale-95">
  <i class="ph-bold ph-plus-circle text-sm"></i> Nueva Evaluación
  </button>
  @endif
 </div>
 @endif
 </div>

 <div class="rm-explorador__nota" role="note">
 <p>{{ match($codArea) { 'ARE_COG' => 'Las observaciones describen componentes; no equivalen a mediciones estandarizadas. Una interpretación puede abstenerse por falta de fuentes o condiciones.', 'ARE_FUN' => 'Barthel registra actividades básicas. La independencia IADL y C03 requieren su circuito original y sus responsables autorizados; aquí se consultan según permiso.', 'ARE_AFE' => 'La valoración psicológica conserva entrevista, estado afectivo, conducta, conclusión y seguimiento. Un vacío instrumental no significa falta de atención.', 'ARE_SOC' => 'La entrevista contextual y la red de apoyo requieren fuentes identificables, relación del informante y confidencialidad. No se crean tests sociales.', default => 'Consulta las fuentes y competencias autorizadas del área.' } }}</p>
 </div>
 @if(in_array($codArea, ['ARE_AFE', 'ARE_SOC', 'ARE_FUN']))
 <x-ui.section-card title="Continuar en la ficha del residente" subtitle="Consulta el contexto y utiliza el registro profesional existente" icon="ph-user-focus">
 <x-ui.field for="continuidad-buscar" label="Buscar residente"><input id="continuidad-buscar" class="rm-input" type="search" maxlength="80" wire:model.live.debounce.300ms="busquedaValoracion"></x-ui.field>
 <ul class="rm-explorador__continuidad">@foreach($residentesContinuidad as $residenteContinuidad)<li><span>{{ $residenteContinuidad->nombre_completo }}</span><a class="rm-btn rm-btn-secondary" href="{{ route('admin.psicologia.residente.ficha', ['residente' => $residenteContinuidad, 'tab' => match($codArea) { 'ARE_AFE' => 'afectiva', 'ARE_SOC' => 'entorno', default => 'funcionamiento' }]) }}">Abrir ficha<span class="sr-only"> de {{ $residenteContinuidad->nombre_completo }}</span></a></li>@endforeach</ul>
 </x-ui.section-card>
 @if($residentesContinuidad->isEmpty())
 <p class="rm-explorador__nota" role="status">No hay residentes disponibles para esta consulta. Revisa el nombre o los permisos de acceso.</p>
 @endif
 @endif
 @include('valoraciones::livewire.valoraciones.guia-instrumento')

 {{-- Modal de nueva evaluación --}}
 @if($instrumentosActivos->isNotEmpty() && $puedeCapturar)
 @livewire('valoraciones.evaluacion-geriatrica-area-modal')
 @endif

</div>
