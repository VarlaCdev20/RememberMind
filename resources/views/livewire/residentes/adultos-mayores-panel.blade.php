<div class="relative mx-auto max-w-7xl space-y-5">
 @inject('visibilidadNavegacion', 'App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion')

 {{-- MODAL DE FORMULARIO DE REGISTRO / EDICIÓN --}}
 @can('residentes.gestionar')
 @livewire('residentes.adulto-mayor-form-modal')
 @endcan

 <x-ui.collection-header title="Residentes" subtitle="Consulta y seguimiento institucional de residentes formalmente admitidos." icon="ph-users-three" eyebrow="Gestión operativa">
 <x-slot:actions>
 <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-panel border border-borde-suave px-4 py-2.5 text-xs font-bold text-titulo transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-arrow-left text-sm"></i> Panel de Inicio
 </a>

 @if($visibilidadNavegacion->puedeVerRuta('admin.admisiones.preadmision'))
 <a wire:navigate href="{{ route('admin.admisiones.preadmision') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold text-inverso shadow-sm transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-plus-circle text-sm"></i> Nueva preadmisión
 </a>
 @endif

 @if($visibilidadNavegacion->puedeVerRuta('admin.adultos-mayores.reporte-general'))
 <a href="{{ route('admin.adultos-mayores.reporte-general') }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold text-inverso shadow-sm transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-file-pdf text-sm"></i> Censo en PDF
 </a>
 @endif
 </x-slot:actions>
 </x-ui.collection-header>

 {{-- 6 INDICADORES SUPERIORES CON DATOS REALES --}}
 <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
 <x-ui.metric-card icon="ph-users-four" :value="$totales['total'] ?? 0" label="Total registrados" variant="neutral" />
 <x-ui.metric-card icon="ph-user-circle-gear" :value="$totales['activos'] ?? 0" label="Activos" variant="mint" />
 <x-ui.metric-card icon="ph-heartbeat" :value="$totales['seguimiento'] ?? 0" label="En seguimiento" variant="sky" />
 <x-ui.metric-card icon="ph-archive" :value="$totales['archivados'] ?? 0" label="Archivados" variant="neutral" />
 <x-ui.metric-card icon="ph-brain" :value="$totales['sin_evaluacion'] ?? 0" label="Sin eval. cognitiva" variant="neutral" />
 <x-ui.metric-card icon="ph-files" :value="$totales['docs_pendientes'] ?? 0" label="Docs pendientes" variant="neutral" />
 </section>

 <div x-data="{ tab: 'tarjetas' }" class="space-y-4">
 {{-- TABS NAVEGATIVOS DEL PANEL --}}
 <x-ui.collection-view-switch class="max-w-full overflow-x-auto" model="tab" label="Vistas de residentes" :options="[
  ['value' => 'tarjetas', 'label' => 'Grid', 'icon' => 'ph-squares-four'],
  ['value' => 'tabla', 'label' => 'Lista', 'icon' => 'ph-list-dashes'],
  ['value' => 'archivados', 'label' => 'Archivados', 'icon' => 'ph-archive'],
  ['value' => 'alertas', 'label' => 'Pendientes', 'icon' => 'ph-warning'],
 ]" />

 {{-- SECCIÓN DE FILTROS AVANZADOS --}}
 {{-- SECCIÓN DE FILTROS AVANZADOS UNIFICADA FORMATO ALERTAS --}}
 <x-ui.filter-bar class="mb-4" x-show="tab !== 'alertas'">
 <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
  {{-- Buscador Principal --}}
  <div class="lg:col-span-3 relative flex items-center">
  <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-muted)]">
   <i class="ph-bold ph-magnifying-glass text-base"></i>
  </span>
  <input type="text"
   wire:model.live.debounce.300ms="buscar"
   placeholder="Ficha, CI, Nombre, Teléfono..."
   class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-input-placeholder)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  @if($buscar !== '')
   <button type="button"
   wire:click="$set('buscar', '')"
   class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-action-primary)] cursor-pointer"
   title="Limpiar búsqueda">
   <i class="ph-bold ph-x-circle text-base"></i>
   </button>
  @endif
  </div>

  {{-- Estado --}}
  <div class="lg:col-span-2">
  <select wire:model.live="estado" class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
   <option value="">Todos los estados</option>
   @foreach($estadosAdulto ?? [] as $est)
   <option value="{{ $est->estado }}">{{ $est->estado }}</option>
   @endforeach
  </select>
  </div>

  {{-- Género --}}
  <div class="lg:col-span-1">
  <select wire:model.live="genero" class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-2 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
   <option value="">Género</option>
   <option value="MASCULINO">Masc.</option>
   <option value="FEMENINO">Fem.</option>
  </select>
  </div>

  {{-- Rango de Edad --}}
  <div class="lg:col-span-2">
  <select wire:model.live="rango_edad" class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
   <option value="">Cualquier edad</option>
   <option value="60-70">60 a 70 años</option>
   <option value="70-80">70 a 80 años</option>
   <option value="80+">Mayores a 80 años</option>
  </select>
  </div>

  {{-- Permanencia --}}
  <div class="lg:col-span-2">
  <select wire:model.live="permanencia" class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
   <option value="">Permanencia (Todas)</option>
   <option value="PERMANENTE">Permanente</option>
   <option value="TEMPORAL">Temporal</option>
   <option value="EVENTUAL">Eventual</option>
  </select>
  </div>

  {{-- Ciudad / Muni --}}
  <div class="lg:col-span-2">
  <input type="text" wire:model.live.debounce.300ms="ciudad_municipio" placeholder="Ciudad/Muni..." class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  </div>
 </div>

 {{-- Fila de chips de filtros activos (desplazable con colorcitos) --}}
 @php
  $hasFiltrosActivos = !empty($buscar) || !empty($estado) || !empty($genero) || !empty($rango_edad) || !empty($permanencia) || !empty($ciudad_municipio) || !empty($fecha_desde);
 @endphp
 @if($hasFiltrosActivos)
  <div class="rm-filter-bar__active">
  <div class="rm-filter-scroll">
   <span class="rm-filter-bar__active-label">
   <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
   </span>
   @if(!empty($buscar))
   <span class="rm-filter-chip rm-filter-chip--search">
    <i class="ph-bold ph-magnifying-glass text-xs"></i>
    <span>Búsqueda: "{{ Str::limit($buscar, 16) }}"</span>
    <button type="button" wire:click="$set('buscar', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
   @if(!empty($estado))
   <span class="rm-filter-chip rm-filter-chip--warning">
    <span class="w-1.5 h-1.5 rounded-full bg-[var(--rm-status-high)]"></span>
    <span>Estado: {{ $estado }}</span>
    <button type="button" wire:click="$set('estado', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
   @if(!empty($genero))
   <span class="rm-filter-chip rm-filter-chip--info">
    <i class="ph-bold ph-gender-intersex text-xs"></i>
    <span>{{ $genero === 'MASCULINO' ? 'Masc.' : 'Fem.' }}</span>
    <button type="button" wire:click="$set('genero', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
   @if(!empty($rango_edad))
   <span class="rm-filter-chip rm-filter-chip--success">
    <i class="ph-bold ph-calendar text-xs"></i>
    <span>Edad: {{ $rango_edad }}</span>
    <button type="button" wire:click="$set('rango_edad', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
   @if(!empty($permanencia))
   <span class="rm-filter-chip rm-filter-chip--clinical">
    <i class="ph-bold ph-clock text-xs"></i>
    <span>Perm.: {{ $permanencia }}</span>
    <button type="button" wire:click="$set('permanencia', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
   @if(!empty($ciudad_municipio))
   <span class="rm-filter-chip rm-filter-chip--search">
    <i class="ph-bold ph-map-pin text-xs"></i>
    <span>Muni: {{ $ciudad_municipio }}</span>
    <button type="button" wire:click="$set('ciudad_municipio', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
  </div>
  <div class="flex items-center gap-2 shrink-0">
   <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[var(--rm-surface-alt)] text-[var(--rm-text-secondary)] border border-[var(--rm-border-soft)]">
   {{ $adultos->total() ?? count($adultos) }} coincidentes
   </span>
   <button type="button"
   wire:click="$set('buscar', ''); $set('estado', ''); $set('genero', ''); $set('permanencia', ''); $set('ciudad_municipio', ''); $set('rango_edad', ''); $set('fecha_desde', ''); $set('fecha_hasta', '');"
   class="rm-filter-bar__clear-btn">
   <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
   <span>Limpiar filtros</span>
   </button>
  </div>
  </div>
 @endif
 </x-ui.filter-bar>

 {{-- VISTA TARJETAS (CARDS RESPONSIVAS PREMIUM) --}}
 <section x-show="tab === 'tarjetas'" class="rm-collection-results rm-collection-results--padded" aria-label="Tarjetas de residentes">
 <div class="rm-collection-grid">
 @php $hayTarjetasActivas = false; @endphp
 @foreach($adultos as $adulto)
 @php
 $estado = strtoupper($adulto->estado_adulto);
 $esArchivado = $estado === 'ARCHIVADO' || $estado === 'INACTIVO';
 // CÁLCULOS DINÁMICOS DE RELACIONES EAGER-LOADED
 $famPrincipal = $adulto->familiares->first(fn($f) => (bool) $f->pivot?->responsable_principal);
 if (!$famPrincipal) {
 $famPrincipal = $adulto->familiares->first();
 }

 $ultAtencion = collect($adulto->atenciones)->sortByDesc('fecha')->first();
 $ultEval = collect($adulto->evaluacionesGeriatricas)->sortByDesc('fecha_eval')->first();
 @endphp
 @if(!$esArchivado)
 @php $hayTarjetasActivas = true; @endphp
 @include('livewire.residentes.partials.resident-list-card')
 @endif
 @endforeach
 @if(!$hayTarjetasActivas)
<div class="col-span-full">
    <x-ui.empty-state
        icon="ph-users-three"
        title="Sin residentes encontrados"
        description="No se encontraron residentes que coincidan con los filtros aplicados."
        action-method="limpiarFiltros"
        action-text="Restablecer filtros" />
</div>
@endif
</div>
</section>

 {{-- TABLA GENERAL (HÍBRIDA DESKTOP / RESPONSIVA) --}}
 <section x-show="tab === 'tabla'" class="rm-collection-results" aria-label="Tabla de residentes" style="display: none;">
 <div class="overflow-x-auto">
 <table class="rm-data-table rm-data-table--actions rm-table w-full text-left text-sm text-titulo">
 <thead class="bg-fondo-panel text-[9px] font-bold uppercase tracking-widest text-apoyo border-b border-borde-suave">
 <tr>
 <th class="px-5 py-4">Nombre Completo</th>
 <th class="px-5 py-4">Carnet Identidad</th>
 <th class="px-5 py-4">Edad</th>
 <th class="px-5 py-4">F. Ingreso</th>
 <th class="px-5 py-4">Estado</th>
 <th class="px-5 py-4">Familiar Resp.</th>
 <th class="px-5 py-4 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[var(--rm-border)]/25">
 @foreach($adultos as $adulto)
 @php
 $famPrincipal = $adulto->familiares->first(fn($f) => (bool) $f->pivot?->responsable_principal);
 if (!$famPrincipal) {
 $famPrincipal = $adulto->familiares->first();
 }
 @endphp
 <tr class="hover:bg-fondo-panel transition">
 <td class="px-5 py-3.5 font-bold text-xs">{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</td>
 <td class="px-5 py-3.5 text-xs">{{ $adulto->ci }} {{ $adulto->complemento_ci }}</td>
 <td class="px-5 py-3.5 text-xs font-bold">{{ $adulto->edad }} años</td>
 <td class="px-5 py-3.5 text-xs">{{ optional($adulto->fecha_ing)->format('d/m/Y') }}</td>
 <td class="px-5 py-3.5">
 <x-ui.status-badge :estado="$adulto->estado_adulto" />
 </td>
 <td class="px-5 py-3.5 text-xs">
 @if($famPrincipal)
 <span class="font-bold">{{ $famPrincipal->usuario->name ?? $famPrincipal->usuario->nombres ?? 'Familiar' }}</span>
 @else
 <span class="text-xs text-apoyo italic">Ninguno</span>
 @endif
 </td>
 <td class="px-5 py-3.5">
 <div class="flex justify-end gap-1.5">
 <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_residente) }}" class="rounded-xl bg-boton-principal p-2 text-inverso hover:bg-fondo-panel transition" title="Ver Ficha Integral">
 <i class="ph-bold ph-eye text-sm"></i>
 </a>
 @can('residentes.gestionar')
 @if(strtoupper($adulto->estado_adulto) !== 'ARCHIVADO' && strtoupper($adulto->estado_adulto) !== 'INACTIVO')
 <button type="button" wire:click="editarAdultoMayor('{{ $adulto->cod_residente }}')" class="rounded-xl bg-boton-acento p-2 text-inverso hover:bg-fondo-panel transition" title="Editar">
 <i class="ph-bold ph-pencil-simple text-sm"></i>
 </button>
 <form method="POST" action="{{ route('admin.adultos-mayores.archivar', $adulto->cod_residente) }}" onsubmit="confirmarAccion(event, '¿Archivar expediente de {{ $adulto->nombres }}?', 'El expediente pasará a la sección de archivados/inactivos.')">
 @csrf @method('PATCH')
 <button type="submit" class="rounded-xl bg-fondo-panel text-parrafo border border-borde p-2 hover:bg-fondo-panel hover:text-inverso transition" title="Archivar expediente"><i class="ph-bold ph-archive text-sm"></i></button>
 </form>
 @else
 <button disabled class="rounded-xl bg-fondo-panel p-2 text-apoyo cursor-not-allowed border border-borde-suave">
 <i class="ph-bold ph-pencil-simple text-sm"></i>
 </button>
 <form method="POST" action="{{ route('admin.adultos-mayores.restaurar', $adulto->cod_residente) }}" onsubmit="confirmarAccion(event, '¿Restaurar expediente de {{ $adulto->nombres }}?', 'El expediente volverá a ser catalogado como ACTIVO.')">
 @csrf @method('PATCH')
 <button type="submit" class="rounded-xl bg-estado-exitoBg p-2 text-inverso hover:bg-fondo-panel transition" title="Restaurar expediente"><i class="ph-bold ph-arrow-counter-clockwise text-sm"></i></button>
 </form>
 @endif
 @endcan
 </div>
 </td>
 </tr>
 @endforeach
 @if(count($adultos) === 0)
 <tr>
 <td colspan="8" class="px-5 py-8">
  <x-ui.empty-state
   compact
   icono="ph-users-three"
   titulo="Sin residentes encontrados"
   texto="No se encontraron residentes que coincidan con los filtros aplicados."
   actionMethod="limpiarFiltros"
   actionText="Restablecer filtros"
  />
 </td>
 </tr>
 @endif
 </tbody>
 </table>
 </div>
 </section>

 {{-- EXPEDIENTES ARCHIVADOS (INACTIVOS / HISTÓRICOS) --}}
 <section x-show="tab === 'archivados'" class="rm-collection-results rm-collection-results--padded" aria-label="Expedientes archivados" style="display: none;">
 <div class="rm-collection-grid">
 @php $hayArchivados = false; @endphp
 @foreach($adultos as $adulto)
 @if(strtoupper($adulto->estado_adulto) === 'ARCHIVADO' || strtoupper($adulto->estado_adulto) === 'INACTIVO')
 @php $hayArchivados = true; @endphp
 <x-ui.resident-card :resident="$adulto" variant="inherit" :show-location="false" :primary-href="route('admin.adultos-mayores.show', $adulto->cod_residente)" primary-label="Ver ficha" context-label="Archivado en" :context-value="$adulto->archivado_en ? \Carbon\Carbon::parse($adulto->archivado_en)->format('d/m/Y') : 'Fecha no registrada'">
     <x-slot:status><x-ui.status-badge :estado="$adulto->estado_adulto" /></x-slot:status>
     <x-slot:details>
         <p><i class="ph-bold ph-identification-card" aria-hidden="true"></i><span>{{ $adulto->ci ? 'CI '.$adulto->ci : 'Documento no registrado' }}</span></p>
         <p><i class="ph-bold ph-archive" aria-hidden="true"></i><span>{{ $adulto->motivo_archivado ?: 'Archivado administrativamente.' }}</span></p>
     </x-slot:details>
     @can('residentes.gestionar')
         <x-slot:menu>
             <div class="rm-resident-compact-card__menu-heading">Restaurar o cambiar estado</div>
             @foreach($estadosAdulto as $est)
                 @if(strtoupper($est->estado) !== strtoupper($adulto->estado_adulto))
                     <form method="POST" action="{{ route('admin.adultos-mayores.estado', $adulto->cod_residente) }}" onsubmit="confirmarAccion(event, '¿Restaurar y cambiar estado a {{ $est->estado }}?', 'El expediente pasará nuevamente al censo activo.')">
                         @csrf @method('PATCH')
                         <input type="hidden" name="cod_est_adul" value="{{ $est->cod_est_adul }}">
                         <button type="submit"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i></span>{{ $est->estado }}</button>
                     </form>
                 @endif
             @endforeach
         </x-slot:menu>
     @endcan
 </x-ui.resident-card>
 @endif
 @endforeach
 @if(!$hayArchivados)
 <div class="col-span-full">
  <x-ui.empty-state
   compact
   icono="ph-archive"
   titulo="Sin expedientes archivados"
   texto="No existen residentes archivados o inactivos en este censo."
  />
 </div>
 @endif
 </div>
 </section>

 {{-- ALERTAS Y PENDIENTES BÁSICOS --}}
 <section x-show="tab === 'alertas'" class="rm-collection-results rm-collection-results--padded" aria-label="Pendientes de expediente" style="display: none;">
 <div class="rm-collection-grid">
 @php $hayAlertas = false; @endphp
 @foreach($adultos as $adulto)
 @php
 $alertas = [];
 if(!$adulto->foto) $alertas[] ="Falta fotografía digital de perfil";
 if($adulto->fam_total == 0) $alertas[] ="Falta registrar red de apoyo familiar";
 if(!$adulto->contacto_emergencia_nombre) $alertas[] ="Falta registrar contacto de emergencia";
 if($adulto->obs_total == 0) $alertas[] ="Sin observaciones diarias";

 $tieneEval = collect($adulto->evaluacionesGeriatricas)->count() > 0;
 if(!$tieneEval) $alertas[] ="Falta realizar evaluación cognitiva inicial";
 @endphp
 @if(count($alertas) > 0 && strtoupper($adulto->estado_adulto) !== 'ARCHIVADO')
 @php $hayAlertas = true; @endphp
 <x-ui.resident-card :resident="$adulto" variant="inherit" :primary-href="route('admin.adultos-mayores.show', $adulto->cod_residente)" primary-label="Completar expediente" context-label="Pendientes del expediente" :context-value="count($alertas).' por revisar'">
     <x-slot:details>
         <ul class="rm-resident-card__pending-list">
             @foreach($alertas as $alerta)
                 <li><i class="ph-bold ph-warning-circle" aria-hidden="true"></i>{{ $alerta }}</li>
             @endforeach
         </ul>
     </x-slot:details>
 </x-ui.resident-card>
 @endif
 @endforeach
 @if(!$hayAlertas)
 <div class="col-span-full">
  <x-ui.empty-state
   icono="ph-check-circle"
   color="text-[var(--rm-success)]"
   titulo="Expedientes completos"
   texto="No se detectaron expedientes con alertas o campos prioritarios vacíos. Todos los expedientes se encuentran al día."
  />
 </div>
 @endif
 </div>
 </section>

 {{-- PAGINACIÓN CON ESTILOS CONSISTENTES --}}
 <div class="mt-6 flex justify-center">
 {{ $adultos->links() }}
 </div>
 </div>
</div>
