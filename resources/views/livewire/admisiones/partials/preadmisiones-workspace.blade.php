<div class="rm-pre-layout {{ $modalDetalle ? 'rm-pre-layout--open' : '' }}" x-data x-init="$nextTick(() => { const code = new URLSearchParams(location.search).get('focus'); if (code) document.getElementById('preadmision-' + code)?.focus() })">
 <main class="rm-pre-main">
  <header class="rm-pre-heading">
   <span class="rm-pre-heading__icon" aria-hidden="true"><i class="ph-bold ph-file-text"></i></span>
   <div class="rm-pre-heading__copy">
    <p>Administración / Admisión</p>
    <h1>Preadmisiones</h1>
    <span>Gestiona y revisa solicitudes previas al ingreso formal.</span>
   </div>
   @can('admisiones.crear')
    <a wire:navigate href="{{ route('admin.admisiones.preadmision') }}" class="rm-pre-primary rm-pre-heading__new"><i class="ph-bold ph-plus" aria-hidden="true"></i> Nueva preadmisión</a>
   @endcan
  </header>

  @if($residenteAdmitidoCodigo)
   <div class="rm-pre-notice" role="status">Admisión realizada correctamente. <a href="{{ route('admin.administracion.residentes.show', $residenteAdmitidoCodigo) }}">Ver residente <i class="ph-bold ph-arrow-right"></i></a></div>
  @endif

  @php
   $rutaListado = $soloRechazadas ? 'admin.admisiones.preadmisiones.rechazadas' : 'admin.admisiones.preadmisiones';
   $filtrosUrl = array_filter(['search' => $search, 'estado' => $estado, 'prioridad' => $prioridad, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]);
   $estadosVista = [
    '' => ['Todas', 'ph-files', $metricas['total']],
    'PENDIENTE' => ['Pendientes', 'ph-hourglass', $metricas['pendientes']],
    'APROBADA' => ['Aprobadas', 'ph-check-circle', $metricas['aprobadas']],
    'ADMITIDA' => ['Admitidas', 'ph-users-three', $metricas['admitidas']],
    'RECHAZADA' => ['Rechazadas', 'ph-x-circle', $metricas['rechazadas']],
   ];
  @endphp
  <x-ui.filter-bar class="mb-4" aria-label="Filtrar preadmisiones">
   <div class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 items-center">
    {{-- Buscador Principal formato alertas --}}
    <div class="relative flex items-center">
     <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
      <i class="ph-bold ph-magnifying-glass text-base"></i>
     </span>
     <input type="text"
      wire:model.live.debounce.350ms="search"
      placeholder="Buscar por nombre, CI, familiar o caso..."
      class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
     @if(!empty($search))
      <button type="button"
       wire:click="$set('search', '')"
       class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
       title="Limpiar búsqueda">
       <i class="ph-bold ph-x-circle text-base"></i>
      </button>
     @endif
    </div>

    {{-- Estado --}}
    <div>
     <select wire:model.live="estado" @disabled($soloRechazadas) aria-label="Filtrar por estado de preadmisión"
      class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
      @foreach($estadosVista as $valor => [$etiqueta, $icono, $cantidad])
       @if(!$soloRechazadas || $valor === 'RECHAZADA')
        <option value="{{ $valor }}">{{ $etiqueta }} ({{ $cantidad }})</option>
       @endif
      @endforeach
     </select>
    </div>

    {{-- Prioridad --}}
    <div>
     <select wire:model.live="prioridad"
      class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
      <option value="">Todas las prioridades</option>
      <option value="BAJA">Baja</option>
      <option value="MEDIA">Media</option>
      <option value="ALTA">Alta</option>
      <option value="CRITICA">Crítica</option>
     </select>
    </div>

    {{-- Desde --}}
    <div>
     <input type="date" wire:model.live="fecha_inicio" title="Fecha inicio"
      class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
    </div>

    {{-- Hasta --}}
    <div>
     <input type="date" wire:model.live="fecha_fin" title="Fecha fin"
      class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
    </div>
    <div>
     <select wire:model.live="orden" aria-label="Ordenar preadmisiones"
      class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer">
      <option value="recientes">Más recientes</option>
      <option value="antiguas">Más antiguas</option>
     </select>
    </div>
   </div>

   {{-- Fila de chips de filtros activos --}}
   @php
    $hasFiltrosActivos = !empty($search) || !empty($estado) || !empty($prioridad) || !empty($fecha_inicio) || !empty($fecha_fin) || $orden !== 'recientes';
   @endphp
   @if($hasFiltrosActivos)
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
      @if(!empty($estado))
       <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-warning-soft)] border border-[var(--rm-warning)] text-[11px] font-bold text-[var(--rm-warning)]">
        <span>Estado: {{ $estado }}</span>
        @unless($soloRechazadas)<button type="button" wire:click="$set('estado', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5" aria-label="Quitar filtro de estado"><i class="ph-bold ph-x text-xs"></i></button>@endunless
       </span>
      @endif
      @if(!empty($prioridad))
       <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-primary-soft)] border border-[var(--rm-primary)] text-[11px] font-bold text-[var(--rm-primary)]">
        <span>Prioridad: {{ $prioridad }}</span>
        <button type="button" wire:click="$set('prioridad', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
       </span>
      @endif
      @if(!empty($fecha_inicio) || !empty($fecha_fin))
       <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
        <span>Fechas: {{ $fecha_inicio ?: '...' }} a {{ $fecha_fin ?: '...' }}</span>
        <button type="button" wire:click="$set('fecha_inicio', null); $set('fecha_fin', null);" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
       </span>
      @endif
      @if($orden !== 'recientes')
       <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg border text-[11px] font-bold">
        <span>Orden: más antiguas</span>
        <button type="button" wire:click="$set('orden', 'recientes')" class="cursor-pointer ml-0.5" aria-label="Restablecer orden"><i class="ph-bold ph-x text-xs"></i></button>
       </span>
      @endif
     </div>

     <div class="flex items-center gap-2.5">
      <button type="button"
       wire:click="limpiarFiltros"
       class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-primary-soft)] hover:bg-[var(--rm-primary)] hover:text-white text-[var(--rm-primary)] border border-[var(--rm-primary)]/30 py-1 px-2.5 text-xs font-bold transition cursor-pointer">
       <i class="ph-bold ph-arrow-counter-clockwise"></i>
       <span>Limpiar filtros</span>
      </button>
     </div>
    </div>
   @endif
  </x-ui.filter-bar>

  <div class="rm-pre-list-heading"><strong>{{ $preadmisiones->total() }} {{ $preadmisiones->total() === 1 ? 'solicitud encontrada' : 'solicitudes encontradas' }}</strong></div>

  <section class="rm-pre-list" aria-label="Solicitudes de preadmisión">
   @forelse($preadmisiones as $preadmision)
    @php
     $edad = $preadmision->fecha_nacimiento?->age;
     $iniciales = collect(explode(' ', trim($preadmision->nombre_completo)))->filter()->take(2)->map(fn ($palabra) => mb_substr($palabra, 0, 1))->implode('');
     $residenteCodigo = $preadmision->admision?->cod_residente;
     $admitida = $preadmision->estado === 'ADMITIDA' || (bool) $residenteCodigo;
     $motivo = $preadmision->descripcion_caso ?: str_replace('_', ' ', $preadmision->motivo_ingreso ?: 'Sin motivo registrado');
     $urlExpediente = route($rutaListado, $filtrosUrl + ['solicitud' => $preadmision->cod_preadmision]);
     $urlDocumentos = route($rutaListado, $filtrosUrl + ['solicitud' => $preadmision->cod_preadmision, 'tab' => 'documentos']);
    @endphp
    <article class="rm-pre-row {{ $solicitudDetalle?->getKey() === $preadmision->getKey() ? 'is-selected' : '' }}" wire:key="pre-{{ $preadmision->cod_preadmision }}">
     <div class="rm-pre-row__person">
      @if($residenteCodigo && $preadmision->admision?->residente?->foto)
       <img class="rm-pre-avatar" src="{{ asset('storage/'.$preadmision->admision->residente->foto) }}" alt="Foto de {{ $preadmision->nombre_completo }}">
      @else
       <span class="rm-pre-avatar rm-pre-avatar--initials" aria-hidden="true">{{ $iniciales ?: '?' }}</span>
      @endif
      <div class="rm-pre-row__identity"><a id="preadmision-{{ $preadmision->cod_preadmision }}" href="{{ $urlExpediente }}" class="rm-pre-person-name">{{ $preadmision->nombre_completo }}</a><p>CI {{ $preadmision->ci ?: 'No registrado' }} {{ $preadmision->expedicion_ci }} {{ $edad ? '· '.$edad.' años' : '' }}</p><div class="rm-pre-chips"><span>{{ $preadmision->tipo_ingreso ? 'Ingreso '.mb_strtolower(str_replace('_', ' ', $preadmision->tipo_ingreso)) : 'Tipo sin registrar' }}</span><span class="rm-pre-priority rm-pre-priority--{{ mb_strtolower($preadmision->prioridad ?: 'sin') }}">Prioridad {{ mb_strtolower($preadmision->prioridad ?: 'sin registrar') }}</span></div></div>
     </div>
     <div class="rm-pre-row__responsible"><span class="rm-pre-row__caption"><i class="ph-bold ph-user" aria-hidden="true"></i> Responsable</span><strong>{{ $preadmision->familiar_completo ?: 'No registrado' }}</strong><small><i class="ph-bold ph-phone" aria-hidden="true"></i> {{ $preadmision->familiar_celular ?: 'Sin teléfono' }}</small></div>
     <div class="rm-pre-row__motive"><span class="rm-pre-row__caption"><i class="ph-bold ph-file-text" aria-hidden="true"></i> Motivo de la solicitud</span><p>{{ $motivo }}</p></div>
     <div class="rm-pre-row__meta">
      <div class="rm-pre-row__status"><x-ui.status-badge :estado="$admitida ? 'ADMITIDA' : $preadmision->estado" /></div>
      <div class="rm-pre-row__documents"><strong>{{ $preadmision->documentos_count }} {{ $preadmision->documentos_count === 1 ? 'documento' : 'documentos' }}</strong><small>{{ $preadmision->documentos_count ? 'Registrados en solicitud' : 'Iniciales pendientes' }}</small><a href="{{ $urlDocumentos }}" class="rm-pre-text-button">Ver documentos</a></div>
      <small class="rm-pre-row__date"><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i> {{ $preadmision->fecha_solicitud?->translatedFormat('d M Y') ?: 'Sin fecha' }}</small>
      <div class="rm-pre-row__actions">
       @if($residenteCodigo)
        <a wire:navigate href="{{ route(auth()->user()?->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']) ? 'admin.administracion.residentes.show' : 'admin.adultos-mayores.show', $residenteCodigo) }}" class="rm-pre-primary">Ver residente <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
       @else
        <a href="{{ $urlExpediente }}" class="rm-pre-primary">Ver expediente <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
       @endif
       <details class="rm-pre-more"><summary aria-label="Más acciones para {{ $preadmision->nombre_completo }}"><i class="ph-bold ph-dots-three-vertical" aria-hidden="true"></i></summary><div><a href="{{ $urlExpediente }}">Ver solicitud</a><a href="{{ $urlDocumentos }}">Documentos</a></div></details>
      </div>
     </div>
    </article>
   @empty
    <div class="rm-pre-empty"><i class="ph-bold ph-folder-open" aria-hidden="true"></i><h2>{{ $metricas['total'] ? ($estado && !$search && !$prioridad && !$fecha_inicio && !$fecha_fin ? 'No hay solicitudes '.mb_strtolower($estadosVista[$estado][0] ?? 'en este estado') : 'No encontramos solicitudes con esos criterios') : 'No hay preadmisiones registradas' }}</h2><p>{{ $metricas['total'] ? 'Puedes consultar otro estado o ajustar los filtros.' : 'Cuando se registre una solicitud aparecerá aquí.' }}</p>@if($metricas['total'])<a href="{{ route('admin.admisiones.preadmisiones') }}" class="rm-pre-empty__link">Ver todas las solicitudes <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@else @can('admisiones.crear')<a wire:navigate href="{{ route('admin.admisiones.preadmision') }}" class="rm-pre-primary">Nueva preadmisión</a>@endcan @endif</div>
   @endforelse
  </section>
  @if($preadmisiones->total() > 0)
   <footer class="rm-pre-pagination">
    <span>Mostrando {{ $preadmisiones->firstItem() }}–{{ $preadmisiones->lastItem() }} de {{ $preadmisiones->total() }} solicitudes</span>
    <nav aria-label="Páginas de solicitudes">
     <button type="button" wire:click="previousPage" @disabled($preadmisiones->onFirstPage()) aria-label="Página anterior"><i class="ph-bold ph-caret-left" aria-hidden="true"></i></button>
     <span aria-current="page">{{ $preadmisiones->currentPage() }}</span>
     <button type="button" wire:click="nextPage" @disabled(! $preadmisiones->hasMorePages()) aria-label="Página siguiente"><i class="ph-bold ph-caret-right" aria-hidden="true"></i></button>
    </nav>
   </footer>
  @endif
 </main>

 @if($modalDetalle && $solicitudDetalle)
  @php
   $sol = $solicitudDetalle;
   $solEdad = $sol->fecha_nacimiento?->age;
   $solIniciales = collect(explode(' ', trim($sol->nombre_completo)))->filter()->take(2)->map(fn ($palabra) => mb_substr($palabra, 0, 1))->implode('');
   $solAdmitida = $sol->estado === 'ADMITIDA' || (bool) $sol->admision?->cod_residente;
   $eventos = collect();
   if ($sol->admision?->fecha_hora_admision) $eventos->push(['Admisión formalizada', $sol->admision->fecha_hora_admision, 'Se registró el ingreso institucional.']);
   if ($sol->fecha_revision) $eventos->push([$sol->estado === 'RECHAZADA' ? 'Solicitud rechazada' : 'Solicitud revisada', $sol->fecha_revision, $sol->estado === 'RECHAZADA' ? str_replace('_', ' ', $sol->motivo_rechazo ?: 'Decisión registrada') : 'Se registró la revisión de la solicitud.']);
   if ($sol->fecha_solicitud) $eventos->push(['Solicitud registrada', $sol->fecha_solicitud, 'Se registró la preadmisión.']);
   $eventos = $eventos->sortByDesc(fn ($evento) => $evento[1]->timestamp)->values();
  @endphp
  <aside class="rm-pre-panel" aria-label="Expediente de preadmisión" tabindex="-1" x-init="$nextTick(() => $el.focus())" x-on:keydown.escape.window="$wire.cerrarDetalle()">
   <div class="rm-pre-panel__top"><h2>Expediente de preadmisión</h2><a href="{{ route($rutaListado, $filtrosUrl + ['focus' => $sol->cod_preadmision]) }}" aria-label="Cerrar expediente"><i class="ph-bold ph-x"></i></a></div>
   <div class="rm-pre-panel__person">
    @if($sol->admision?->residente?->foto)
     <img class="rm-pre-avatar" src="{{ asset('storage/'.$sol->admision->residente->foto) }}" alt="Foto de {{ $sol->nombre_completo }}">
    @else
     <span class="rm-pre-avatar rm-pre-avatar--initials" aria-hidden="true">{{ $solIniciales ?: '?' }}</span>
    @endif
    <div><h3>{{ $sol->nombre_completo }}</h3><p>CI {{ $sol->ci ?: 'No registrado' }} {{ $sol->expedicion_ci }} {{ $solEdad ? '· '.$solEdad.' años' : '' }}</p><div class="rm-pre-chips"><x-ui.status-badge :estado="$solAdmitida ? 'ADMITIDA' : $sol->estado" /><span class="rm-pre-priority rm-pre-priority--{{ mb_strtolower($sol->prioridad ?: 'sin') }}">{{ ucfirst(mb_strtolower($sol->prioridad ?: 'Sin prioridad')) }} · {{ ucfirst(mb_strtolower($sol->permanencia ?: $sol->tipo_ingreso ?: 'Sin tipo')) }}</span></div></div>
   </div>
   @if($panelModo === 'detalle')
    <nav class="rm-pre-panel__tabs" aria-label="Secciones del expediente">
     @foreach(['resumen' => 'Resumen', 'documentos' => 'Documentos', 'historial' => 'Historial'] as $tab => $etiqueta)
      <button type="button" wire:click="cambiarPanelTab('{{ $tab }}')" class="{{ $panelTab === $tab ? 'is-active' : '' }}" @if($panelTab === $tab) aria-current="true" @endif>{{ $etiqueta }}</button>
     @endforeach
    </nav>
   @else
    <div class="rm-pre-panel__back"><button type="button" wire:click="{{ $panelModo === 'rechazo' ? 'cerrarModalRechazo' : 'volverAlResumen' }}"><i class="ph-bold ph-arrow-left"></i> Volver</button><strong>{{ $panelModo === 'rechazo' ? 'Registrar rechazo' : 'Revisión de preadmisión' }}</strong></div>
   @endif
   <div class="rm-pre-panel__body">
    @if($panelModo === 'revision')
     <section class="rm-pre-info"><h4>Información esencial</h4><dl><div><dt>Solicitante</dt><dd>{{ $sol->nombre_completo }}</dd></div><div><dt>Documento</dt><dd>{{ $sol->ci ?: 'No registrado' }}</dd></div><div><dt>Responsable</dt><dd>{{ $sol->familiar_completo ?: 'No registrado' }}</dd></div><div><dt>Prioridad</dt><dd>{{ ucfirst(mb_strtolower($sol->prioridad ?: 'Sin registrar')) }}</dd></div></dl></section>
     <section class="rm-pre-info"><h4>Documentación</h4><p>{{ $sol->documentos->count() }} documentos registrados</p><button type="button" wire:click="cambiarPanelTab('documentos')" class="rm-pre-text-button">Ver documentación <i class="ph-bold ph-arrow-right"></i></button></section>
     <section class="rm-pre-info"><h4>Observaciones y motivo</h4><p>{{ $sol->descripcion_caso ?: str_replace('_', ' ', $sol->motivo_ingreso ?: 'Sin descripción registrada') }}</p></section>
    @elseif($panelModo === 'rechazo')
     <form id="rm-pre-rejection" wire:submit="rechazar" class="rm-pre-rejection"><p>La decisión quedará registrada en el expediente.</p><label>Motivo de rechazo<select wire:model="motivo_rechazo"><option value="">Seleccionar motivo</option><option value="DOCUMENTACION_INSUFICIENTE">Documentación insuficiente</option><option value="CRITERIO_MEDICO">Criterio médico</option><option value="CRITERIO_INSTITUCIONAL">Criterio institucional</option><option value="DATOS_INCONSISTENTES">Datos inconsistentes</option><option value="DESISTIMIENTO_FAMILIAR">Desistimiento familiar</option><option value="OTRO">Otro</option></select></label>@error('motivo_rechazo')<span class="rm-pre-error">{{ $message }}</span>@enderror<label>Observación profesional<textarea wire:model="observacion_rechazo" rows="5"></textarea></label>@error('observacion_rechazo')<span class="rm-pre-error">{{ $message }}</span>@enderror</form>
    @elseif($panelTab === 'resumen')
     <section class="rm-pre-info"><h4><i class="ph-bold ph-clipboard-text"></i> Información de la solicitud</h4><dl><div><dt>Fecha</dt><dd>{{ $sol->fecha_solicitud?->format('d/m/Y') ?: 'Sin fecha' }}</dd></div><div><dt>Tipo</dt><dd>{{ ucfirst(mb_strtolower(str_replace('_', ' ', $sol->tipo_ingreso ?: 'Sin registrar'))) }}</dd></div><div><dt>Prioridad</dt><dd>{{ ucfirst(mb_strtolower($sol->prioridad ?: 'Sin registrar')) }}</dd></div><div><dt>Estado</dt><dd>{{ $solAdmitida ? 'Admitida' : ucfirst(mb_strtolower($sol->estado)) }}</dd></div></dl></section>
     <section class="rm-pre-info"><h4><i class="ph-bold ph-user"></i> Responsable</h4><dl><div><dt>Nombre</dt><dd>{{ $sol->familiar_completo ?: 'No registrado' }}</dd></div><div><dt>Relación</dt><dd>No registrada en la solicitud</dd></div><div><dt>Teléfono</dt><dd>{{ $sol->familiar_celular ?: 'No registrado' }}</dd></div><div><dt>Email</dt><dd>{{ $sol->familiar_correo ?: 'No registrado' }}</dd></div></dl></section>
     <section class="rm-pre-info"><h4><i class="ph-bold ph-file-text"></i> Motivo</h4><p>{{ $sol->descripcion_caso ?: str_replace('_', ' ', $sol->motivo_ingreso ?: 'Sin motivo registrado') }}</p>@if($sol->estado === 'RECHAZADA' && $sol->motivo_rechazo)<p class="rm-pre-info__note">Decisión: {{ str_replace('_', ' ', $sol->motivo_rechazo) }}</p>@endif</section>
     <section class="rm-pre-info rm-pre-info--action"><div><h4><i class="ph-bold ph-folder"></i> Documentación</h4><strong>{{ $sol->documentos->count() }} {{ $sol->documentos->count() === 1 ? 'documento' : 'documentos' }}</strong><p>{{ $sol->documentos->count() ? 'Registrados en la solicitud' : 'Iniciales pendientes' }}</p></div><button type="button" wire:click="cambiarPanelTab('documentos')" class="rm-pre-text-button">Ver documentación <i class="ph-bold ph-arrow-right"></i></button></section>
    @elseif($panelTab === 'documentos')
     <h4 class="rm-pre-panel__section-title">Documentación de la solicitud</h4>
     @forelse($sol->documentos as $documento)
      <div class="rm-pre-document"><i class="ph-bold ph-file-text" aria-hidden="true"></i><div><strong>{{ $documento->nombre }}</strong><span>{{ str_replace('_', ' ', $documento->tipo_documento ?: 'Documento') }}</span></div><x-ui.status-badge :estado="$documento->estado" />@if($documento->ruta_archivo)<a href="{{ asset('storage/'.$documento->ruta_archivo) }}" target="_blank" rel="noopener" aria-label="Ver {{ $documento->nombre }}"><i class="ph-bold ph-arrow-square-out"></i></a>@endif</div>
     @empty
      <div class="rm-pre-info"><p>No hay documentos registrados para esta solicitud.</p></div>
     @endforelse
    @else
     <h4 class="rm-pre-panel__section-title">Historial de la solicitud</h4>@include('livewire.admisiones.partials.preadmisiones-timeline', ['eventos' => $eventos])
    @endif
   </div>
   <footer class="rm-pre-panel__footer">
    @if($panelModo === 'rechazo')<button type="submit" form="rm-pre-rejection" class="rm-pre-danger">Confirmar rechazo</button>
    @elseif($panelModo === 'revision')<button type="button" wire:click="abrirModalRechazo('{{ $sol->cod_preadmision }}')" class="rm-pre-danger-link">Rechazar</button><button type="button" wire:click="aprobar('{{ $sol->cod_preadmision }}')" wire:confirm="¿Confirma que revisó la solicitud y desea aprobarla? La ficha de residente todavía no será creada." class="rm-pre-primary">Aprobar preadmisión <i class="ph-bold ph-check"></i></button>
    @elseif($sol->admision?->cod_residente)<a wire:navigate href="{{ route(auth()->user()?->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']) ? 'admin.administracion.residentes.show' : 'admin.adultos-mayores.show', $sol->admision->cod_residente) }}" class="rm-pre-primary">Ver expediente completo <i class="ph-bold ph-arrow-square-out"></i></a>
    @elseif($sol->estado === 'PENDIENTE' && auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'ADMINISTRADOR', 'SUPERADMINISTRADOR']))<button type="button" wire:click="revisarSolicitud" class="rm-pre-primary">Revisar solicitud <i class="ph-bold ph-arrow-right"></i></button>
    @elseif($sol->estado === 'APROBADA' && auth()->user()?->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']))<button type="button" wire:click="abrirAdmision('{{ $sol->cod_preadmision }}')" class="rm-pre-primary">Formalizar admisión <i class="ph-bold ph-arrow-right"></i></button>
    @elseif($sol->estado === 'RECHAZADA')<button type="button" wire:click="cambiarPanelTab('resumen')" class="rm-pre-primary">Ver decisión <i class="ph-bold ph-arrow-right"></i></button>
    @endif
   </footer>
  </aside>
 @endif
</div>
