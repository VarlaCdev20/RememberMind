<div class="rm-pilot-alertas rm-emergency-workspace rm-page-layout" x-data="rmAlertasInteraccion()" @rm-alertas-request="estado($event.detail)" @rm-alertas-error="errorSolicitud = $event.detail.message" :aria-busy="solicitudes > 0" @click.capture="proteger($event)" @keydown.escape.window.capture="proteger($event)" @unless($preview || $modalCrear || $modalAtender || $modalCerrar || $modalDetalle || $drawerGrafico || $drawerUbicacion) wire:poll.visible.60s @endunless>
    <x-ui.page-header :title="$perfil['titulo']" :subtitle="$perfil['descripcion']" overline="Alertas registradas · continuidad y seguimiento" icon="ph-bell-ringing">
        @if(!$preview && auth()->user()->can('alertas.gestionar') && ($puedeIntervenir || $puedeCoordinar))
            <x-ui.action-button wire:click="abrirCrear" loading="abrirCrear" icono="ph-plus">Registrar alerta</x-ui.action-button>
            <x-ui.action-button variant="secondary" wire:click="detectarAlertas" loading="detectarAlertas" icono="ph-magnifying-glass">Detectar pendientes</x-ui.action-button>
        @endif
        <x-ui.action-button variant="ghost" wire:click="$refresh" loading="$refresh" icono="ph-arrows-clockwise" aria-label="Actualizar bandeja">Actualizar</x-ui.action-button>
    </x-ui.page-header>
    @if(session()->has('mensaje'))<div role="status" aria-live="polite"><x-ui.callout variant="success" title="Registro confirmado">{{ session('mensaje') }}</x-ui.callout></div>@endif
    @unless($modalCrear || $modalAtender || $modalCerrar || $modalDetalle)
        <x-validation-errors />
    @endunless
    @if($preview)
        <x-ui.callout variant="info" title="Previsualización de solo lectura">
            Se muestra la organización del rol seleccionado con el ámbito autorizado de tu cuenta actual. Esta vista no acredita una asignación de turno ni concede permisos profesionales.
        </x-ui.callout>
    @endif
    <div wire:offline class="rm-callout" role="alert">Se perdió la conexión. Conserva tu captura y vuelve a intentar al recuperar el acceso.</div>
    <div wire:loading.delay role="status" aria-live="polite" class="rm-alert-request-status"><i class="ph-bold ph-hourglass-medium" aria-hidden="true"></i><span x-text="guardados ? 'Procesando la operación. Conserva esta ventana hasta recibir el resultado.' : 'Consultando las alertas… puedes seguir revisando la información visible.'"></span></div>
    <div x-show="errorSolicitud" x-cloak role="alert" class="rm-callout rm-alert-request-error"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i><p x-text="errorSolicitud"></p><button type="button" class="rm-btn rm-btn-secondary" @click="errorSolicitud = ''">Mantener captura</button></div>
    @if($estadosHistoricos)
        <x-ui.callout variant="warning" title="Registros con estado histórico">
            <p>{{ array_sum($estadosHistoricos) }} alerta(s) conservan estados de registros anteriores. Puedes consultarlas con su información original; se muestran separadas de los pendientes actuales.</p>
            @foreach($estadosHistoricos as $estado=>$cantidad)<button type="button" class="rm-btn rm-btn-secondary" wire:click="setFiltroRapido('filtroEstado','{{ $estado }}')" aria-pressed="{{ $filtroEstado === $estado ? 'true' : 'false' }}">{{ $filtroEstado === $estado ? 'Mostrando' : 'Consultar' }} {{ $cantidad }} · {{ $estado }}</button>@endforeach
        </x-ui.callout>
    @endif

    <section class="rm-alert-kpis" aria-label="Resumen del ámbito autorizado, antes de los filtros de lista">
        @foreach([['Alertas registradas','total',''],['Pendientes activos','pendientes','ACTIVAS'],['En atención','en_atencion','EN_ATENCION'],['Atendidas · por cerrar','atendidas','ATENDIDA'],['Cerradas','cerradas','CERRADA']] as [$etiqueta,$clave,$estado])
            <button type="button" class="rm-alert-kpi" wire:click="setFiltroRapido('filtroEstado','{{ $estado }}')"><strong>{{ $conteos[$clave] }}</strong><span class="rm-alert-kpi__copy"><span>{{ $etiqueta }}</span><small>Ver alertas</small></span></button>
        @endforeach
        <button type="button" class="rm-alert-kpi is-priority" wire:click="verPrioritarias"><strong>{{ $conteos['criticas'] + $conteos['altas'] }}</strong><span class="rm-alert-kpi__copy"><span>Críticas y altas activas</span><small>Prioridad registrada</small></span></button>
    </section>

    @if($prioritarias->isNotEmpty())
        <section class="rm-alert-priority" aria-labelledby="alertas-prioritarias">
            <header><i class="ph-bold ph-warning-octagon" aria-hidden="true"></i><div><h2 id="alertas-prioritarias">Revisar primero</h2><p>Prioridades registradas de tu ámbito. Abre una alerta para revisar el caso y continuar.</p></div></header>
            @foreach($prioritarias as $a)
                <button type="button" wire:key="prioridad-{{ $a->getKey() }}" wire:click="verDetalle('{{ $a->getKey() }}')"><strong>{{ $a->prioridad }} · {{ $a->residente?->nombre_completo }}</strong><span>{{ $a->tipo }} · {{ str_replace('_',' ',$a->estado) }}</span><span>Ver alerta <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></span></button>
            @endforeach
        </section>
    @endif

    <x-ui.filter-bar>
        <nav class="rm-alert-shortcuts" aria-label="Consultas rápidas">
            <button type="button" class="rm-btn rm-btn-secondary" wire:click="setFiltroRapido('filtroEstado','ACTIVAS')" aria-pressed="{{ $filtroEstado === 'ACTIVAS' ? 'true' : 'false' }}"><i class="ph-bold ph-tray" aria-hidden="true"></i>Pendientes</button>
            @if(auth()->user()->personal)<button type="button" class="rm-btn rm-btn-secondary" wire:click="setFiltroRapido('filtroResponsabilidad','PROPIAS')" aria-pressed="{{ $filtroResponsabilidad === 'PROPIAS' ? 'true' : 'false' }}"><i class="ph-bold ph-user-check" aria-hidden="true"></i>A mi cargo</button>@endif
            <button type="button" class="rm-btn rm-btn-secondary" wire:click="setFiltroRapido('filtroResponsabilidad','SIN_RESPONSABLE')" aria-pressed="{{ $filtroResponsabilidad === 'SIN_RESPONSABLE' ? 'true' : 'false' }}"><i class="ph-bold ph-user-minus" aria-hidden="true"></i>Sin responsable</button>
            <button type="button" class="rm-btn rm-btn-secondary" wire:click="limpiarFiltros">Ver todas las alertas</button>
        </nav>
        <div class="rm-alert-filters">
            <x-ui.field label="Buscar" for="alertas-buscar"><input id="alertas-buscar" type="search" wire:model.live.debounce.400ms="search" class="rm-input" placeholder="Residente, código, tipo o descripción"></x-ui.field>
            <x-ui.field label="Estado" for="alertas-estado"><x-ui.selector id="alertas-estado" label="Estado" wire:model.live="filtroEstado" teleport="body"><option value="">Todos los estados</option><option value="ACTIVAS">Pendientes activos</option>@foreach(array_unique([...\App\Models\Alerta::ESTADOS_ACTIVOS,'ATENDIDA','CERRADA','ANULADA',...array_keys($estadosHistoricos)]) as $estado)<option value="{{ $estado }}">{{ str_replace('_',' ', $estado) }}</option>@endforeach</x-ui.selector></x-ui.field>
            <x-ui.field label="Prioridad" for="alertas-prioridad"><x-ui.selector id="alertas-prioridad" label="Prioridad" wire:model.live="filtroNivel" teleport="body"><option value="">Todas</option><option value="PRIORITARIAS">Críticas y altas activas</option>@foreach(['CRITICO','ALTO','MEDIO','BAJO'] as $nivel)<option value="{{ $nivel }}">{{ $nivel }}</option>@endforeach</x-ui.selector></x-ui.field>
        </div>
        <details id="alertas-filtros-extra" wire:ignore.self class="rm-alert-more-filters" @if($filtroOrigen || $filtroResponsabilidad || $fechaDesde || $fechaHasta) open @endif>
            <summary><span class="rm-alert-filter-heading"><i class="ph-bold ph-sliders-horizontal" aria-hidden="true"></i><span><strong>Más filtros</strong><small>Origen, responsable y fechas</small></span></span><i class="ph-bold ph-caret-down rm-alert-filter-caret" aria-hidden="true"></i></summary>
            <div class="rm-alert-filters">
            <x-ui.field label="Origen registrado" for="alertas-origen"><x-ui.selector id="alertas-origen" label="Origen registrado" wire:model.live="filtroOrigen" teleport="body"><option value="">Todos</option>@foreach($origenesDisponibles as $origen)<option value="{{ $origen }}">{{ str_replace('_',' ', $origen ?: 'Sin origen registrado') }}</option>@endforeach</x-ui.selector></x-ui.field>
            <x-ui.field label="Responsabilidad" for="alertas-responsable"><x-ui.selector id="alertas-responsable" label="Responsabilidad" wire:model.live="filtroResponsabilidad" teleport="body"><option value="">Cualquier responsable</option>@if(auth()->user()->personal)<option value="PROPIAS">A mi cargo</option>@endif<option value="SIN_RESPONSABLE">Sin responsable</option></x-ui.selector></x-ui.field>
            <x-ui.field label="Registradas desde" for="alertas-desde" error="fechaDesde"><x-ui.calendario id="alertas-desde" label="Registradas desde" wire:model.live="fechaDesde" :max="$fechaHasta" :aria-invalid="$errors->has('fechaDesde') ? 'true' : 'false'" aria-describedby="alertas-desde-error" /></x-ui.field>
            <x-ui.field label="Registradas hasta" for="alertas-hasta" error="fechaHasta"><x-ui.calendario id="alertas-hasta" label="Registradas hasta" wire:model.live="fechaHasta" :min="$fechaDesde" :aria-invalid="$errors->has('fechaHasta') ? 'true' : 'false'" aria-describedby="alertas-hasta-error" /></x-ui.field>
            </div>
        </details>
    </x-ui.filter-bar>
    <div class="rm-alert-list-heading"><div><h2>Alertas de la consulta</h2><p>{{ $alertas->total() }} de {{ $conteos['total'] }} alerta(s) de tu ámbito · {{ $filtroEstado === 'ACTIVAS' ? 'Pendientes actuales' : ($filtroEstado !== '' ? 'Estado: '.str_replace('_', ' ', $filtroEstado) : 'Todos los estados') }}</p></div>@if($alertas->total() > 0)<p class="rm-help">{{ $sinResponsableConsulta }} sin responsable registrado en esta consulta</p>@endif</div>
    @if(in_array($perfil['foco'], ['supervision','auditoria']) && $alertas->total() > 0)
        <section class="rm-alert-context-grid" aria-label="Resumen de las alertas de esta consulta">
            <x-ui.dashboard-data-panel :panel="['title'=>'Origen de las alertas','subtitle'=>'Mismos filtros que la lista · '.$alertas->total().' registros','type'=>'bars','items'=>collect($chartData['origenes'])->map(fn($n,$label)=>['label'=>str_replace('_',' ', $label ?: 'Sin origen registrado'),'value'=>$n])->values()->all(),'icon'=>'ph-chart-bar']" />
            <x-ui.dashboard-data-panel :panel="['title'=>'Estado registrado','subtitle'=>'Se conserva el estado guardado de cada alerta','type'=>'bars','items'=>collect($chartData['estados'])->map(fn($n,$label)=>['label'=>str_replace('_',' ', $label ?: 'Sin estado registrado'),'value'=>$n])->values()->all(),'icon'=>'ph-list-checks']" />
        </section>
    @endif
    <section class="rm-alert-case-list" aria-label="Alertas de la consulta">
        @forelse($alertas as $alerta)
            <article class="rm-alert-case {{ in_array($alerta->prioridad, ['CRITICO','CRITICA']) ? 'is-critical' : '' }}" wire:key="alerta-{{ $alerta->getKey() }}">
                <header class="rm-alert-case__header"><div><span class="rm-badge rm-alert-severity {{ in_array($alerta->prioridad, ['CRITICO','CRITICA']) ? 'is-critical' : (in_array($alerta->prioridad,['ALTO','ALTA']) ? 'is-high' : '') }}">{{ $alerta->prioridad }}</span><span class="rm-badge">{{ str_replace('_',' ',$alerta->estado) }}</span></div><small aria-label="Código de la alerta">{{ $alerta->getKey() }}</small></header>
                <div class="rm-alert-case__body">
                    <div><h3>{{ $alerta->residente?->nombre_completo }}</h3><p><strong>{{ str_replace('_', ' ', $alerta->tipo) }}</strong></p><p>{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos(\Illuminate\Support\Str::limit($alerta->descripcion,200)) }}</p></div>
                    <dl class="rm-alert-case__facts">
                        <div><dt>Ubicación actual</dt><dd>{{ $alerta->residente?->cama?->habitacion?->codigo ?: 'Habitación no registrada' }} · {{ $alerta->residente?->cama?->codigo ?: 'Cama no registrada' }}</dd></div>
                        <div><dt>Responsable</dt><dd>{{ $alerta->responsable ? trim($alerta->responsable->nombres.' '.$alerta->responsable->apellido_paterno) : 'Sin responsable asignado' }}</dd></div>
                        <div><dt>Registrada</dt><dd><time datetime="{{ $alerta->fecha_hora?->toIso8601String() }}">{{ $alerta->fecha_hora?->format('d/m/Y H:i') ?: 'Fecha no registrada' }}</time></dd></div>
                        <div><dt>Origen</dt><dd>{{ str_replace('_',' ', $alerta->modulo ?: 'Sin origen registrado') }}</dd></div>
                        @if($alerta->fecha_hora_limite)<div><dt>Límite registrado</dt><dd>{{ $alerta->fecha_hora_limite->format('d/m/Y H:i') }}</dd></div>@endif
                    </dl>
                </div>
                <footer class="rm-alert-case__actions">
                    <span class="rm-alert-case__provenance"><i class="ph-bold {{ $alerta->generacion === 'MANUAL' ? 'ph-note-pencil' : 'ph-bell-ringing' }}" aria-hidden="true"></i>{{ match($alerta->generacion) { 'MANUAL' => 'Registro manual', 'AUTOMATICA', 'AUTOMATICO' => 'Detección registrada', default => 'Generación sin registrar' } }}</span>
                    <x-ui.action-button variant="secondary" wire:click="verDetalle('{{ $alerta->getKey() }}')" loading="verDetalle('{{ $alerta->getKey() }}')" icono="ph-arrow-up-right">Ver alerta</x-ui.action-button>
                </footer>
            </article>
        @empty
            <x-ui.empty-state icono="ph-bell-slash" :titulo="$conteos['total'] > 0 ? 'Ninguna alerta coincide con estos filtros' : 'Aún no hay alertas registradas en tu ámbito'" :texto="$conteos['total'] > 0 ? 'Hay '.$conteos['total'].' alertas registradas. Usa Ver todas las alertas para consultarlas o ajusta el estado, la prioridad y las fechas.' : 'Las alertas aparecerán aquí cuando se registre una situación que requiera seguimiento.'" />
        @endforelse
    </section>
    {{ $alertas->links() }}
    @if($perfil['foco'] === 'auditoria')
        <details class="rm-alert-more-filters"><summary>Estado de la vigilancia automática <span>Pendiente de habilitación</span></summary><p class="rm-help">La ejecución sin sesión requiere una autoría institucional aprobada. La detección manual autorizada sigue disponible. No se confirma vigilancia automática activa.</p></details>
    @endif
    @php($alertaActiva = $detalle)
    @include('livewire.alertas.modales.detalle')
    @include('livewire.alertas.modales.crear')
    @include('livewire.alertas.modales.atender')
    @include('livewire.alertas.modales.cerrar')
    @include('livewire.alertas.modales.resultado-cierre')
    @include('livewire.alertas.modales.drawer-graficos')
    @include('livewire.alertas.modales.drawer-ubicacion')
</div>
