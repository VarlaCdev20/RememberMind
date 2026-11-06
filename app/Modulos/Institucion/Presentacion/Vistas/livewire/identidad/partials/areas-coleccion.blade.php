 {{-- ENCABEZADO PREMIUM --}}
 <x-ui.collection-header class="no-print" :title="$modoEquipo ? 'Áreas y equipo' : 'Áreas institucionales'" :subtitle="$modoEquipo ? 'Organización institucional y equipo registrado por jornada.' : 'Estructura y organigrama funcional del centro.'" icon="ph-buildings" eyebrow="Gestión institucional">
 <x-slot:actions>
 @unless($modoEquipo)
 @can('areas.reportes')
 <button type="button"
 wire:click="abrirReportes"
 class="inline-flex items-center justify-center gap-2 rm-btn-secondary">
 <i class="ph-bold ph-chart-line-up text-base"></i>
 Reportes
 </button>
 @endcan
 @endunless

 @can('areas.crear')
 <button type="button"
 wire:click="crearArea"
 class="inline-flex items-center justify-center gap-2 rm-btn-primary">
 <i class="ph-bold ph-plus-circle text-base"></i>
 Nueva área
 </button>
 @endcan
 </x-slot:actions>
 </x-ui.collection-header>

 {{-- MÉTRICAS E INDICADORES ORGANIZACIONALES --}}
 <section class="grid grid-cols-2 {{ $modoEquipo ? 'sm:grid-cols-3' : 'md:grid-cols-6' }} gap-4 no-print">
 <x-ui.metric-card icon="ph-layout" :value="$totalAreas" label="Total áreas" variant="neutral" />
 <x-ui.metric-card icon="ph-check-circle" :value="$areasActivas" label="Activas" variant="mint" />
 <x-ui.metric-card :class="$modoEquipo ? 'col-span-2 sm:col-span-1' : ''" icon="ph-eye-slash" :value="$areasInactivas" label="Inactivas" variant="neutral" />
 @unless($modoEquipo)
 <x-ui.metric-card icon="ph-users-three" :value="$usuariosVinculados" label="Personal" variant="sky" />
 <x-ui.metric-card icon="ph-warning-circle" :value="$areasSinResponsable" label="Sin responsable" :variant="$areasSinResponsable > 0 ? 'coral' : 'neutral'" />
 <x-ui.metric-card icon="ph-user-minus" :value="$areasSinUsuarios" label="Sin personal" variant="neutral" />
 @endunless
 </section>

 {{-- FILTROS DE BÚSQUEDA Y VISTA FORMATO ALERTAS --}}
 <x-ui.filter-bar class="mb-4 no-print">
  <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
   {{-- Búsqueda textual --}}
   <div class="lg:col-span-9">
    <label for="areas-buscar" class="rm-collection-filter-label">Buscar área</label>
    <div class="rm-filter-search relative flex items-center">
    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
     <i class="ph-bold ph-magnifying-glass text-base"></i>
    </span>
    <input id="areas-buscar" type="text"
     wire:model.live.debounce.300ms="search"
     placeholder="Buscar por nombre o código..."
     class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
    @if($search !== '')
     <button type="button"
      wire:click="$set('search', '')"
      class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
      title="Limpiar búsqueda">
      <i class="ph-bold ph-x-circle text-base"></i>
     </button>
    @endif
    </div>
   </div>

   {{-- Estado --}}
   <div class="lg:col-span-3">
    <label for="areas-estado" class="rm-collection-filter-label">Estado</label>
    <select id="areas-estado" wire:model.live="filtroEstado"
     class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
     <option value="">Todos los estados</option>
     <option value="ACTIVO">Áreas activas</option>
     <option value="INACTIVO">Áreas inactivas</option>
    </select>
   </div>
  </div>

  {{-- Fila de chips de filtros activos --}}
  @php
   $hasFiltrosArea = !empty($search) || !empty($filtroEstado);
  @endphp
  @if($hasFiltrosArea)
   <div class="rm-filter-bar__active">
    <div class="flex flex-wrap items-center gap-1.5">
     <span class="rm-filter-bar__active-label">
      <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
     </span>
     @if(!empty($search))
      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
       <span>Búsqueda: "{{ Str::limit($search, 16) }}"</span>
       <button type="button" wire:click="$set('search', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
      </span>
     @endif
     @if(!empty($filtroEstado))
      <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-warning-soft)] border border-[var(--rm-warning)] text-[11px] font-bold text-[var(--rm-warning)]">
       <span>Estado: {{ $filtroEstado }}</span>
       <button type="button" wire:click="$set('filtroEstado', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
      </span>
     @endif
    </div>

    <div class="flex items-center gap-2.5">
     <button type="button"
      wire:click="$set('search', ''); $set('filtroEstado', '');"
      class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-primary-soft)] hover:bg-[var(--rm-primary)] hover:text-white text-[var(--rm-primary)] border border-[var(--rm-primary)]/30 py-1 px-2.5 text-xs font-bold transition cursor-pointer">
      <i class="ph-bold ph-arrow-counter-clockwise"></i>
      <span>Limpiar filtros</span>
     </button>
    </div>
   </div>
  @endif
 </x-ui.filter-bar>

 {{-- GRID PRINCIPAL DE CARDS --}}
 <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 no-print">
 @forelse($areas as $area)
 @php
 $esInactiva = in_array($area->estado, ['INACTIVA', 'INACTIVO'], true);
 @endphp
 <div data-area-card="{{ $area->getKey() }}" wire:key="area-card-{{ $area->getKey() }}" class="group relative rounded-3xl transition-all duration-300 hover:-translate-y-1 rm-card overflow-hidden border-2 border-transparent hover:border-borde-suave flex flex-col {{ $esInactiva ? 'opacity-70 grayscale bg-[var(--surface-soft)]' : '' }}">

 {{-- Cabecera institucional --}}
 <div class="relative h-36 w-full shrink-0 overflow-hidden bg-[var(--surface-soft)]">
 <div class="h-full w-full flex items-center justify-center bg-[var(--rm-primary-soft)]">
 <i class="ph-bold ph-buildings text-[var(--rm-primary)] text-5xl opacity-40" aria-hidden="true"></i>
 </div>

 {{-- Estado --}}
 <div class="absolute top-4 left-4 flex flex-wrap gap-2">
 @if($esInactiva)
 <span class="rounded-full bg-red-600 px-2.5 py-0.5 text-[8px] font-black text-inverso uppercase tracking-widest shadow-sm">
 Inactiva
 </span>
 @else
 <span class="rounded-full bg-estado-exitoBg px-2.5 py-0.5 text-[8px] font-black text-inverso uppercase tracking-widest shadow-sm">
 Activa
 </span>
 @endif
 </div>

 {{-- Nombre del área encima de la imagen --}}
 <div class="absolute bottom-4 left-4 right-4">
 <h3 class="text-base font-extrabold text-[var(--rm-text-primary)] leading-snug break-words">
 {{ $area->nombre }}
 </h3>
 </div>
 </div>

 {{-- CONTENIDO CARD --}}
 <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
 {{-- DESCRIPCIÓN --}}
 <p class="text-xs font-semibold text-meta line-clamp-3 leading-relaxed">
 {{ $area->descripcion }}
 </p>

 @if($modoEquipo)
 <div class="rounded-2xl bg-fondo-panel p-3 border border-borde-suave text-sm text-parrafo">
 <i class="ph-bold ph-users-three" aria-hidden="true"></i> Equipo consultable por fecha y jornada
 </div>
 @else
 {{-- RESPONSABLE --}}
 <div class="rounded-2xl bg-fondo-panel p-3 flex items-center justify-between border border-borde-suave">
 <div class="flex items-center gap-2">
 <div class="flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold text-inverso shadow-sm bg-[var(--rm-primary)]">
 {{ $area->responsable ? substr($area->responsable->nombres, 0, 1) . substr($area->responsable->ap_paterno, 0, 1) : '?' }}
 </div>
 <div>
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Responsable</p>
 <p class="text-[10px] font-bold text-titulo truncate max-w-[140px]">
 {{ $area->responsable ? $area->responsable->name : 'Sin Responsable' }}
 </p>
 </div>
 </div>

 {{-- Contador Personal --}}
 <div class="text-right">
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Personal</p>
 <span class="inline-flex items-center gap-1 text-[11px] font-bold text-boton-acento">
 <i class="ph-bold ph-users-three"></i>
 {{ $area->usuarios_count }}
 </span>
 </div>
 </div>

 @endif
 {{-- ACCIONES DE CARD --}}
 <div class="pt-4 border-t border-borde-suave flex flex-wrap gap-2 items-center justify-between">
 <div class="flex items-center gap-2">
 <button type="button"
 wire:click="verArea('{{ $area->cod_area }}')"
 class="inline-flex min-h-[44px] items-center gap-1.5 rounded-full bg-fondo-panel px-3 text-[10px] font-bold text-titulo hover:bg-boton-principal hover:text-inverso transition duration-200"
 title="Ver Ficha Completa">
 <i class="ph-bold ph-eye"></i>
 {{ $modoEquipo ? 'Ver área y equipo' : 'Ver ficha' }}
 </button>

 @unless($modoEquipo)
 @can('areas.reportes')
 <button type="button"
 wire:click="abrirReporteArea('{{ $area->cod_area }}')"
 class="inline-flex min-h-[44px] items-center gap-1.5 rounded-full bg-estado-exitoBg px-3 text-[10px] font-bold text-estado-exito hover:bg-estado-exitoBg hover:text-inverso transition duration-200"
 title="Reporte del Área">
 <i class="ph-bold ph-file-chart"></i>
 Reporte
 </button>
 @endcan
 @endunless
 </div>

 <div class="flex items-center gap-1.5">
 @can('areas.editar')
 <button type="button"
 wire:click="editarArea('{{ $area->cod_area }}')"
 class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-estado-peligroBg text-boton-acento hover:bg-boton-acento hover:text-inverso transition duration-200"
 aria-label="Editar área {{ $area->nombre }}" title="Editar Área">
 <i class="ph-bold ph-pencil-simple"></i>
 </button>
 @endcan

 @can('areas.cambiar_estado')
 <button type="button"
 wire:click="toggleEstado('{{ $area->cod_area }}')"
 wire:confirm="¿Está seguro de cambiar el estado de este área institucional?"
 class="inline-flex h-11 w-11 items-center justify-center rounded-full transition duration-200 {{ $esInactiva ? 'bg-estado-exitoBg text-estado-exito hover:bg-estado-exitoBg hover:text-inverso' : 'bg-red-50 text-red-600 hover:bg-red-600 hover:text-inverso' }}"
 title="{{ $esInactiva ? 'Activar Área' : 'Desactivar Área' }}">
 <i class="ph-bold {{ $esInactiva ? 'ph-power' : 'ph-x-circle' }}"></i>
 </button>
 @endcan
 </div>
 </div>
 </div>
 </div>
 @empty
 <div class="col-span-full rounded-3xl bg-fondo-card p-12 text-center shadow-[0_12px_24px_rgba(0,0,0,0.02)]">
 <div class="flex justify-center text-5xl text-meta mb-4">
 <i class="ph-bold ph-folder-open"></i>
 </div>
 <h3 class="text-base font-extrabold text-titulo">No se encontraron áreas institucionales</h3>
 <p class="text-xs font-semibold text-parrafo mt-1">Intenta ajustando los filtros de búsqueda o registra una nueva área.</p>
 </div>
 @endforelse
 </section>
