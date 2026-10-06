<main class="rm-pre-main rm-pre-studio">
 <x-ui.page-header title="Preadmisiones" subtitle="Cada solicitud, un nuevo comienzo. Acompaña su revisión y prepara el ingreso formal." overline="Administración / Admisión" icon="ph-file-text" :date="now()->locale('es')">
  @can('admisiones.crear')<a wire:navigate href="{{ route('admin.admisiones.preadmision') }}" class="rm-btn rm-btn-primary"><i class="ph-bold ph-plus-circle" aria-hidden="true"></i> Nueva preadmisión</a>@endcan
 </x-ui.page-header>
 @if($residenteAdmitidoCodigo)<div class="rm-pre-notice" role="status">Admisión realizada correctamente. <a wire:navigate href="{{ route('admin.administracion.residentes.show', $residenteAdmitidoCodigo) }}">Ver residente</a></div>@endif
 @php
  $rutaListado = $soloRechazadas ? 'admin.admisiones.preadmisiones.rechazadas' : 'admin.admisiones.preadmisiones';
  $filtrosUrl = array_filter(['search' => $search, 'estado' => $estado, 'prioridad' => $prioridad, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'vista' => $vista]);
  $estadosVista = [
   '' => ['Todas', 'ph-files', $metricas['total']],
   'PENDIENTE' => ['Pendientes', 'ph-hourglass', $metricas['pendientes']],
   'APROBADA' => ['Aprobadas', 'ph-check-circle', $metricas['aprobadas']],
   'ADMITIDA' => ['Admitidas', 'ph-users-three', $metricas['admitidas']],
   'RECHAZADA' => ['Rechazadas', 'ph-x-circle', $metricas['rechazadas']],
  ];
  $segmentos = ['PENDIENTE' => 'pendiente', 'APROBADA' => 'aprobada', 'ADMITIDA' => 'admitida', 'RECHAZADA' => 'rechazada'];
  $totalGrafico = array_sum(array_map(fn ($estadoGrafico) => $estadosVista[$estadoGrafico][2], array_keys($segmentos)));
  $hasFiltrosActivos = $search !== '' || $estado !== '' || $prioridad !== '' || $fecha_inicio !== '' || $fecha_fin !== '' || $orden !== 'recientes';
 @endphp
 <details class="rm-pre-overview" open>
  <summary><span><i class="ph-bold ph-chart-donut" aria-hidden="true"></i> Panorama de solicitudes <small>Datos generales · interactúa para filtrar</small></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary>
  <div class="rm-pre-insights">
   <section class="rm-pre-insight" aria-label="Distribución por estado">
    <header><i class="ph-bold ph-chart-pie-slice" aria-hidden="true"></i><div><h2>¿En qué etapa están?</h2><p>Estados registrados y admisiones formalizadas</p></div></header>
    <div class="rm-pre-distribution">
     <div class="rm-pre-donut">
      <svg viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="45" class="rm-pre-donut__track" fill="none" stroke-width="10"/>
       @php $avance = 0; @endphp
       @foreach($segmentos as $codigo => $tono)
        @php $porcentaje = $totalGrafico ? $estadosVista[$codigo][2] / $totalGrafico * 100 : 0; @endphp
        <circle cx="60" cy="60" r="45" pathLength="100" fill="none" stroke-width="10" class="rm-pre-donut__segment is-{{ $tono }}" stroke-dasharray="{{ $porcentaje }} {{ 100 - $porcentaje }}" stroke-dashoffset="{{ -$avance }}" transform="rotate(-90 60 60)" />
        @php $avance += $porcentaje; @endphp
       @endforeach
      </svg><div><strong>{{ $totalGrafico }}</strong><span>solicitudes</span></div>
     </div>
     <div class="rm-pre-legend">
      @foreach($segmentos as $codigo => $tono)
       <button type="button" class="is-{{ $tono }}" wire:click="$set('estado', '{{ $codigo }}')" @disabled($soloRechazadas && $codigo !== 'RECHAZADA') aria-pressed="{{ $estado === $codigo ? 'true' : 'false' }}"><i class="ph-bold {{ $estadosVista[$codigo][1] }}" aria-hidden="true"></i><span>{{ $estadosVista[$codigo][0] }}</span><strong>{{ $estadosVista[$codigo][2] }}</strong></button>
      @endforeach
     </div>
    </div>
   </section>
   <section class="rm-pre-insight" aria-label="Solicitudes registradas en los últimos seis meses">
    <header><i class="ph-bold ph-chart-bar" aria-hidden="true"></i><div><h2>Ritmo de nuevas solicitudes</h2><p>Últimos seis meses · selecciona un mes</p></div></header>
    <div class="rm-pre-months">
     @foreach($tendencia as $mes)
      <button type="button" wire:click="filtrarMes('{{ $mes['mes'] }}')" aria-label="{{ $mes['descripcion'] }}: {{ $mes['cantidad'] }} solicitudes. Filtrar este mes." title="{{ $mes['descripcion'] }} · {{ $mes['cantidad'] }} solicitudes" class="{{ str_starts_with($fecha_inicio, $mes['mes']) ? 'is-selected' : '' }}"><strong>{{ $mes['cantidad'] }}</strong><span class="rm-pre-months__track"><span style="--bar-size: {{ $mes['cantidad'] ? max(5, $mes['cantidad'] / max(1, $tendencia->max('cantidad')) * 100) : 0 }}%"></span></span><small>{{ $mes['etiqueta'] }}</small></button>
     @endforeach
    </div>
   </section>
  </div>
 </details>
 <section class="rm-pre-toolbar" aria-label="Filtrar preadmisiones">
  <div class="rm-pre-toolbar__top">
   <label class="rm-pre-searchbox" for="buscar-preadmisiones"><i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i><input id="buscar-preadmisiones" type="search" wire:model.live.debounce.350ms="search" placeholder="Nombre, CI, familiar o caso…" aria-label="Buscar preadmisiones">@if($search !== '')<button type="button" wire:click="$set('search', '')" aria-label="Limpiar búsqueda"><i class="ph-bold ph-x" aria-hidden="true"></i></button>@endif</label>
   <div class="rm-pre-view-switch" role="group" aria-label="Vista de solicitudes">
    @foreach(['lista' => ['Lista', 'ph-list-bullets'], 'tarjetas' => ['Tarjetas', 'ph-squares-four'], 'tabla' => ['Tabla', 'ph-table']] as $tipo => [$etiqueta, $icono])
     <button type="button" wire:click="$set('vista', '{{ $tipo }}')" aria-label="Vista {{ mb_strtolower($etiqueta) }}" aria-pressed="{{ $vista === $tipo ? 'true' : 'false' }}" title="{{ $etiqueta }}"><i class="ph-bold {{ $icono }}" aria-hidden="true"></i><span>{{ $etiqueta }}</span></button>
    @endforeach
   </div>
  </div>
  <div class="rm-pre-toolbar__filters">
   <div><label for="selector-estado">Estado</label><x-ui.selector label="Estado" aria-label="Filtrar por estado de preadmisión" wire:model.live="estado" :disabled="$soloRechazadas">@foreach($estadosVista as $valor => [$etiqueta, $icono, $cantidad])@if(!$soloRechazadas || $valor === 'RECHAZADA')<option value="{{ $valor }}">{{ $etiqueta }} ({{ $cantidad }})</option>@endif @endforeach</x-ui.selector></div>
   <div><label for="selector-prioridad">Prioridad</label><x-ui.selector label="Prioridad" wire:model.live="prioridad"><option value="">Todas las prioridades</option>@foreach($prioridadesDisponibles as $valor => $cantidad)<option value="{{ $valor }}">{{ $valor === 'SIN_REGISTRAR' ? 'Sin registrar' : ucfirst(mb_strtolower($valor)) }} ({{ $cantidad }})</option>@endforeach</x-ui.selector></div>
   <div><label for="calendario-fecha_inicio">Desde</label><x-ui.calendario label="Fecha desde" wire:model.live="fecha_inicio" :max="$fecha_fin" /></div>
   <div><label for="calendario-fecha_fin">Hasta</label><x-ui.calendario label="Fecha hasta" wire:model.live="fecha_fin" :min="$fecha_inicio" /></div>
   <div><label for="selector-orden">Orden</label><x-ui.selector label="Ordenar preadmisiones" wire:model.live="orden"><option value="recientes">Más recientes</option><option value="antiguas">Más antiguas</option></x-ui.selector></div>
  </div>
  @if($hasFiltrosActivos)
   <div class="rm-pre-active" aria-label="Filtros activos"><span><i class="ph-bold ph-funnel" aria-hidden="true"></i> Filtros</span>
    @if($search !== '')<button type="button" wire:click="$set('search', '')" aria-label="Quitar búsqueda">{{ Str::limit($search, 24) }} <i class="ph-bold ph-x" aria-hidden="true"></i></button>@endif
    @if($estado !== '')<button type="button" wire:click="$set('estado', '')" @disabled($soloRechazadas) aria-label="Quitar filtro de estado">{{ $estadosVista[$estado][0] ?? $estado }} <i class="ph-bold ph-x" aria-hidden="true"></i></button>@endif
    @if($prioridad !== '')<button type="button" wire:click="$set('prioridad', '')" aria-label="Quitar filtro de prioridad">{{ $prioridad === 'SIN_REGISTRAR' ? 'Sin prioridad registrada' : ucfirst(mb_strtolower($prioridad)) }} <i class="ph-bold ph-x" aria-hidden="true"></i></button>@endif
    @if($fecha_inicio !== '' || $fecha_fin !== '')<button type="button" wire:click="limpiarFechas" aria-label="Quitar filtro de fechas"><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i>{{ $fecha_inicio ?: 'Inicio' }} / {{ $fecha_fin ?: 'Hoy' }} <i class="ph-bold ph-x" aria-hidden="true"></i></button>@endif
    @if($orden !== 'recientes')<button type="button" wire:click="$set('orden', 'recientes')" aria-label="Restablecer orden">Más antiguas <i class="ph-bold ph-x" aria-hidden="true"></i></button>@endif
    <button type="button" wire:click="limpiarFiltros" class="rm-pre-active__clear"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i> Limpiar filtros</button>
   </div>
  @endif
 </section>
 <div class="rm-pre-results" role="status"><div><span class="rm-pre-results__icon"><i class="ph-bold ph-stack" aria-hidden="true"></i></span><div><h2>{{ $preadmisiones->total() }} {{ $preadmisiones->total() === 1 ? 'solicitud encontrada' : 'solicitudes encontradas' }}</h2><p>{{ $hasFiltrosActivos ? 'Resultados según tus filtros' : 'Todas las solicitudes registradas' }}</p></div></div><span wire:loading wire:target="search,estado,prioridad,fecha_inicio,fecha_fin,orden,previousPage,nextPage,gotoPage,porPagina"><i class="ph-bold ph-spinner animate-spin" aria-hidden="true"></i> Actualizando…</span></div>
 <section class="rm-pre-collection rm-pre-collection--{{ $vista }}" aria-label="Solicitudes de preadmisión" wire:loading.class="is-updating" wire:target="search,estado,prioridad,fecha_inicio,fecha_fin,orden,porPagina">
  @if($vista === 'tabla' && $preadmisiones->isNotEmpty())
   <div class="rm-pre-table-scroll"><table class="rm-pre-table"><caption class="sr-only">Solicitudes registradas</caption><thead><tr><th scope="col">Postulante</th><th scope="col">Responsable</th><th scope="col">Solicitud</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead><tbody>
    @foreach($preadmisiones as $p)<tr wire:key="tabla-{{ $p->cod_preadmision }}"><td><button type="button" wire:click="verDetalle('{{ $p->cod_preadmision }}')">{{ $p->nombre_completo }}</button><small>CI {{ $p->numero_documento ?: 'Sin registrar' }}</small></td><td>{{ $p->familiar_completo ?: 'Sin registrar' }}</td><td>{{ $p->fecha_solicitud?->format('d/m/Y') ?: 'Sin fecha' }}<small>{{ $p->prioridad ?: 'Sin prioridad registrada' }}</small></td><td><x-ui.status-badge :estado="$p->admision ? 'ADMITIDA' : $p->estado" /></td><td><div class="rm-pre-quick"><button type="button" wire:click="verDetalle('{{ $p->cod_preadmision }}')" aria-label="Ver expediente de {{ $p->nombre_completo }}" title="Expediente"><i class="ph-bold ph-identification-card" aria-hidden="true"></i></button><button type="button" wire:click="verDocumentos('{{ $p->cod_preadmision }}')" aria-label="Ver documentos de {{ $p->nombre_completo }}" title="Documentos"><i class="ph-bold ph-folder-open" aria-hidden="true"></i><span>{{ $p->documentos_count }}</span></button><button type="button" wire:click="verHistorial('{{ $p->cod_preadmision }}')" aria-label="Ver historial de {{ $p->nombre_completo }}" title="Historial"><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i></button></div></td></tr>@endforeach
   </tbody></table></div>
  @else
   @forelse($preadmisiones as $preadmision)
    @php
     $residenteCodigo = $preadmision->admision?->cod_residente;
     $iniciales = collect(explode(' ', trim($preadmision->nombre_completo)))->filter()->take(2)->map(fn ($palabra) => mb_substr($palabra, 0, 1))->implode('');
    @endphp
    <article class="rm-pre-row rm-pre-record {{ $solicitudDetalle?->getKey() === $preadmision->getKey() ? 'is-selected' : '' }}" wire:key="pre-{{ $preadmision->cod_preadmision }}">
     <div class="rm-pre-row__person"><span class="rm-pre-avatar rm-pre-avatar--initials" aria-hidden="true">{{ $iniciales ?: '?' }}</span><div class="rm-pre-row__identity"><button type="button" id="preadmision-{{ $preadmision->cod_preadmision }}" wire:click="verDetalle('{{ $preadmision->cod_preadmision }}')" class="rm-pre-person-name">{{ $preadmision->nombre_completo }}</button><p>CI {{ $preadmision->ci ?: 'Sin registrar' }} {{ $preadmision->expedicion_ci }} · {{ $preadmision->fecha_nacimiento?->age ?? '—' }} años</p><div class="rm-pre-chips"><span>{{ $preadmision->tipo_ingreso ? str_replace('_', ' ', $preadmision->tipo_ingreso) : 'Tipo sin registrar' }}</span><span>Prioridad {{ mb_strtolower($preadmision->prioridad ?: 'sin registrar') }}</span></div></div></div>
     <div class="rm-pre-row__responsible"><span class="rm-pre-row__caption"><i class="ph-bold ph-users" aria-hidden="true"></i> Red de apoyo</span><strong>{{ $preadmision->familiar_completo ?: 'Responsable sin registrar' }}</strong><small><i class="ph-bold ph-phone" aria-hidden="true"></i> {{ $preadmision->familiar_celular ?: 'Sin teléfono' }}</small></div>
     <div class="rm-pre-row__motive"><span class="rm-pre-row__caption"><i class="ph-bold ph-note" aria-hidden="true"></i> Motivo de solicitud</span><p>{{ $preadmision->descripcion_caso ?: str_replace('_', ' ', $preadmision->motivo_ingreso ?: 'Sin motivo registrado') }}</p></div>
     <div class="rm-pre-row__meta"><x-ui.status-badge :estado="$residenteCodigo ? 'ADMITIDA' : $preadmision->estado" /><small class="rm-pre-row__date"><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i>{{ $preadmision->fecha_solicitud?->locale('es')->translatedFormat('d M Y') ?: 'Sin fecha' }}</small><div class="rm-pre-quick"><button type="button" wire:click="verDetalle('{{ $preadmision->cod_preadmision }}')" aria-label="Ver expediente de {{ $preadmision->nombre_completo }}" title="Expediente"><i class="ph-bold ph-identification-card" aria-hidden="true"></i></button><button type="button" wire:click="verDocumentos('{{ $preadmision->cod_preadmision }}')" aria-label="Ver documentos de {{ $preadmision->nombre_completo }}" title="Documentos"><i class="ph-bold ph-folder-open" aria-hidden="true"></i><span>{{ $preadmision->documentos_count }}</span></button><button type="button" wire:click="verHistorial('{{ $preadmision->cod_preadmision }}')" aria-label="Ver historial de {{ $preadmision->nombre_completo }}" title="Historial"><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i></button></div>
      @if($residenteCodigo)<a wire:navigate href="{{ route(auth()->user()?->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']) ? 'admin.administracion.residentes.show' : 'admin.adultos-mayores.show', $residenteCodigo) }}" class="rm-pre-primary">Ver residente <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>@else<button type="button" wire:click="verDetalle('{{ $preadmision->cod_preadmision }}')" class="rm-pre-primary">Abrir expediente <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button>@endif
     </div>
    </article>
   @empty
    <div class="rm-pre-empty"><i class="ph-bold ph-folder-open" aria-hidden="true"></i><h2>{{ $metricas['total'] ? 'No encontramos solicitudes con esos criterios' : 'No hay preadmisiones registradas' }}</h2><p>{{ $metricas['total'] ? 'Cambia el estado o elimina un filtro para continuar.' : 'Registra la primera solicitud para iniciar el proceso.' }}</p>@if($metricas['total'])<button type="button" wire:click="limpiarFiltros" class="rm-btn-secondary"><i class="ph-bold ph-arrow-counter-clockwise" aria-hidden="true"></i> Limpiar filtros</button>@endif</div>
   @endforelse
  @endif
 </section>
 @if($preadmisiones->total() > 0)
  <footer class="rm-pre-pagination rm-pre-pagination--studio"><div><strong>{{ $preadmisiones->firstItem() }}–{{ $preadmisiones->lastItem() }}</strong><span>de {{ $preadmisiones->total() }} solicitudes</span></div><div class="rm-pre-page-size"><label for="selector-porPagina">Por página</label><x-ui.selector label="Solicitudes por página" wire:model.live="porPagina"><option value="10">10</option><option value="20">20</option><option value="50">50</option></x-ui.selector></div><nav aria-label="Páginas de solicitudes"><button type="button" wire:click="previousPage" @disabled($preadmisiones->onFirstPage()) aria-label="Página anterior"><i class="ph-bold ph-caret-left" aria-hidden="true"></i></button>@foreach($preadmisiones->getUrlRange(max(1, $preadmisiones->currentPage() - 2), min($preadmisiones->lastPage(), $preadmisiones->currentPage() + 2)) as $pagina => $url)<button type="button" wire:click="gotoPage({{ $pagina }})" @if($pagina === $preadmisiones->currentPage()) aria-current="page" @endif aria-label="Página {{ $pagina }}">{{ $pagina }}</button>@endforeach<button type="button" wire:click="nextPage" @disabled(!$preadmisiones->hasMorePages()) aria-label="Página siguiente"><i class="ph-bold ph-caret-right" aria-hidden="true"></i></button></nav></footer>
 @endif
</main>
