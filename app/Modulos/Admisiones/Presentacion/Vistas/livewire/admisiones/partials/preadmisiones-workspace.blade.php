<div class="rm-pre-layout {{ $modalDetalle ? 'rm-pre-layout--open' : '' }}" x-data x-init="$nextTick(() => { const code = new URLSearchParams(location.search).get('focus'); if (code) document.getElementById('preadmision-' + code)?.focus() })">
 @php
  $rutaListado = $soloRechazadas ? 'admin.admisiones.preadmisiones.rechazadas' : 'admin.admisiones.preadmisiones';
  $filtrosUrl = array_filter(['search' => $search, 'estado' => $estado, 'prioridad' => $prioridad, 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'vista' => $vista]);
 @endphp
 @include('livewire.admisiones.partials.preadmisiones-listado')
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
