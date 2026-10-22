<div class="rm-pilot-alertas rm-emergency-workspace rm-page-layout" data-alert-workspace="{{ $perfil['foco'] }}" x-data="rmAlertasInteraccion()" @alertas-caso-seleccionado.window="$refs.contextoCaso.focus({ preventScroll: true }); $refs.contextoCaso.scrollIntoView({ block: 'nearest', behavior: 'instant' });" @rm-alertas-request="estado($event.detail)" @rm-alertas-error="errorSolicitud = $event.detail.message" :aria-busy="solicitudes > 0" @click.capture="proteger($event)" @keydown.escape.window.capture="proteger($event)" @unless($preview || $modalCrear || $modalAtender || $modalCerrar || $modalDetalle || $modalEspecialidad || $drawerGrafico || $drawerUbicacion) wire:poll.visible.60s @endunless>
    @use('App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta')
    <x-ui.page-header :title="$perfil['titulo']" :subtitle="$perfil['descripcion']" overline="Alertas · espacio de trabajo" :icon="$perfil['icono']" class="rm-alert-workspace-heading">
        @if(auth()->user()->hasAnyRole(['SUPERADMINISTRADOR','ADMINISTRADOR']))<a href="{{ route('admin.alertas-clinicas.por-rol') }}" class="rm-btn rm-btn-secondary"><i class="ph-bold ph-users-three" aria-hidden="true"></i>Vistas por rol</a>@endif
        @if($preview && in_array($perfil['foco'],['turno','coordinacion','auditoria'],true) && auth()->user()->can('alertas.gestionar'))<x-ui.action-button variant="secondary" wire:click="abrirCrear" loading="abrirCrear" icono="ph-eye">Consultar formulario de aviso</x-ui.action-button>@endif
        @if(!$preview && auth()->user()->can('alertas.gestionar') && ($puedeIntervenir || $puedeCoordinar))
            <x-ui.action-button wire:click="abrirCrear" loading="abrirCrear" icono="ph-plus">Registrar alerta</x-ui.action-button>
            <x-ui.action-button variant="secondary" wire:click="detectarAlertas" loading="detectarAlertas" icono="ph-magnifying-glass">Detectar pendientes</x-ui.action-button>
        @endif

    </x-ui.page-header>
    @if(session()->has('mensaje'))<div role="status" aria-live="polite"><x-ui.callout variant="success" title="Registro confirmado">{{ session('mensaje') }}</x-ui.callout></div>@endif
    @unless($modalCrear || $modalAtender || $modalCerrar || $modalDetalle || $modalEspecialidad)
        <x-validation-errors />
    @endunless
    @if($preview)
        <x-ui.callout variant="info" title="Previsualización de solo lectura">
            Se muestra la organización del rol seleccionado con el ámbito autorizado de tu cuenta actual. Esta vista no acredita una asignación de turno ni concede permisos profesionales.
        </x-ui.callout>
    @endif
    <div wire:offline class="rm-callout" role="alert">Se perdió la conexión. Conserva tu captura y vuelve a intentar al recuperar el acceso.</div>
    <div wire:loading.delay wire:target="guardarAlerta,guardarAtencion,guardarCierre,guardarAccion,guardarRegistroEspecialidad,asignarResponsable,detectarAlertas" role="status" aria-live="polite" class="rm-alert-request-status"><i class="ph-bold ph-hourglass-medium" aria-hidden="true"></i><span x-text="guardados ? 'Procesando la operación. Conserva esta ventana hasta recibir el resultado.' : 'Consultando las alertas… puedes seguir revisando la información visible.'"></span></div>
    <div x-show="errorSolicitud" x-cloak role="alert" class="rm-callout rm-alert-request-error"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i><p x-text="errorSolicitud"></p><button type="button" class="rm-btn rm-btn-secondary" @click="errorSolicitud = ''">Mantener captura</button></div>
    <section class="rm-alert-specialties rm-alert-trace" aria-label="Alertas por vínculo profesional">
        <h2>{{ $filtroEspecialidad === '' ? 'Todas las especialidades de tu ámbito' : ($especialidadesDisponibles[$filtroEspecialidad] ?? 'Sin vínculo profesional verificable') }}</h2>
        <p>Elige la especialidad relacionada con el responsable o con el profesional del registro de origen. Una valoración del residente, por sí sola, no vincula una alerta a esa especialidad.</p>
        <x-ui.alerta-filtro-chips label="Especialidad vinculada" campo="filtroEspecialidad" :actual="$filtroEspecialidad" :opciones="[''=>'Todas las autorizadas'] + $especialidadesDisponibles + ['SIN_VINCULO'=>'Sin vínculo verificable']" />
        <p class="rm-help">Los vínculos utilizan los roles profesionales actuales de las cuentas relacionadas. No equivalen a un diagnóstico ni a una derivación. Las prioridades activas de tu ámbito siguen visibles debajo.</p>
    </section>
    @if($estadosHistoricos)
        <x-ui.callout variant="warning" title="Hay alertas de registros anteriores">
            <p>{{ array_sum($estadosHistoricos) }} alerta(s) conservan estados de registros anteriores. Se conservan sin reclasificar. «Urgentes» incluye solo prioridades altas o críticas del ciclo actual; «Por atender» incluye avisos actuales sin atención en curso.</p>
            @foreach($estadosHistoricos as $estado=>$cantidad)<button type="button" class="rm-btn rm-btn-secondary" wire:click="setFiltroRapido('filtroEstado','{{ $estado }}')" aria-pressed="{{ $filtroEstado === $estado ? 'true' : 'false' }}">{{ $filtroEstado === $estado ? 'Mostrando' : 'Consultar' }} {{ $cantidad }} · {{ $estado }}</button>@endforeach
        </x-ui.callout>
    @endif

    @include('livewire.alertas.resumen-trabajo')
    @unless($perfil['foco'] === 'coordinacion')
    <section class="rm-alert-kpis" aria-label="Resumen de la especialidad seleccionada, antes de los filtros de lista">
        @foreach([['Urgentes','urgentes',$conteos['criticas']+$conteos['altas'],'Críticas y altas activas'],['Por atender','por_atender',$conteos['por_atender'],'Sin atención en curso'],['En atención','en_atencion',$conteos['en_atencion'],'Actuación registrada'],['Cerradas','cerradas',$conteos['cerradas'],'Historial conservado'],['Registros anteriores','anteriores',$conteos['anteriores'],'Estado original conservado'],['Todas','todas',$conteos['total'],'Alertas de tu ámbito'],['Sin responsable','sin_responsable',$conteos['sin_responsable'],'Activas sin asignación'],['Con límite superado','fuera_plazo',$conteos['vencidas'],'Fecha límite guardada']] as [$etiqueta,$clave,$cantidad,$ayuda])
            @continue(!in_array($clave, $perfil['indicadores'], true))
            <button type="button" class="rm-alert-kpi {{ $clave === 'urgentes' ? 'is-priority' : '' }}" wire:click="filtrarResumen('{{ $clave }}')" aria-pressed="{{ match($clave) { 'urgentes' => $filtroNivel === 'PRIORITARIAS' && $filtroEstado === '', 'por_atender' => $filtroEstado === 'POR_ATENDER', 'en_atencion' => $filtroEstado === 'EN_ATENCION', 'cerradas' => $filtroEstado === 'CERRADA', 'anteriores' => $filtroEstado === 'ANTERIORES', 'sin_responsable' => $filtroResponsabilidad === 'SIN_RESPONSABLE' && $filtroEstado === 'ACTIVAS', 'fuera_plazo' => $filtroEstado === 'CON_LIMITE_SUPERADO', 'todas' => $filtroEstado === '' && $filtroNivel === '', default => false } ? 'true' : 'false' }}" wire:loading.attr="disabled" aria-label="{{ $etiqueta }}: {{ $cantidad }}. {{ $ayuda }}"><strong>{{ $cantidad }}</strong><span class="rm-alert-kpi__copy"><span>{{ $etiqueta }}</span><small>{{ $ayuda }}</small></span></button>
        @endforeach
    </section>
    @endunless
    @if($prioritarias->isNotEmpty())
        <nav class="rm-alert-urgent-strip" aria-label="Prioridades activas que permanecen pendientes">
            <strong><i class="ph-bold ph-warning-octagon" aria-hidden="true"></i> Prioridades activas de todo tu ámbito</strong>
            @foreach($prioritarias as $prioritaria)<button type="button" class="rm-btn rm-btn-secondary" wire:click="verDetalle('{{ $prioritaria->getKey() }}')">{{ LenguajeAlerta::prioridad($prioritaria->prioridad) }} · Residente: {{ $prioritaria->residente?->nombre_completo }} · Ver detalle</button>@endforeach
            <button type="button" class="rm-btn rm-btn-secondary" wire:click="verPrioritarias">Ver todas las prioridades activas</button>
        </nav>
    @endif
    <div class="rm-alert-workspace-grid">
    <div class="rm-alert-workspace-grid__main">
    <x-ui.filter-bar>
        <div class="rm-alert-filter-top"><h2>Encuentra y revisa una alerta</h2><button type="button" class="rm-btn rm-btn-ghost" wire:click="limpiarFiltros">Restablecer filtros</button></div>
        <x-ui.field label="Buscar alertas" for="alertas-buscar"><input id="alertas-buscar" type="search" wire:model.live.debounce.300ms="search" class="rm-input" placeholder="Nombre, habitación, cama, motivo o tipo"></x-ui.field>
        <p class="rm-help" role="status" aria-live="polite"><span wire:loading.remove wire:target="search">La lista se filtra mientras escribes. No necesitas actualizar la página.</span><span wire:loading.delay wire:target="search">Buscando coincidencias…</span></p>
        <x-ui.alerta-filtro-chips label="Estado" campo="filtroEstado" :actual="$filtroEstado" :opciones="['ACTIVAS'=>'Activas','POR_ATENDER'=>'Por atender','EN_ATENCION'=>'En atención','ATENDIDA'=>'Atendidas · por cerrar','CERRADA'=>'Cerradas','ANULADA'=>'Anuladas','ANTERIORES'=>'Registros anteriores',''=>'Todas']" />
        <x-ui.alerta-filtro-chips label="Prioridad" campo="filtroNivel" :actual="$filtroNivel" :opciones="[''=>'Todas las prioridades','PRIORITARIAS'=>'Críticas y altas activas','CRITICO'=>'Críticas','ALTO'=>'Altas','MEDIO'=>'Medias','BAJO'=>'Bajas']" />
        <div class="rm-alert-more-filters" x-data="{ abierto: @js((bool)($filtroOrigen || $filtroResponsabilidad || $fechaDesde || $fechaHasta)) }">
            <button type="button" class="rm-btn rm-btn-secondary rm-alert-disclosure" @click="abierto = !abierto" :aria-expanded="abierto" aria-controls="alertas-filtros-extra"><i class="ph-bold ph-faders-horizontal" aria-hidden="true"></i><span>Origen, responsable y fechas</span><i class="ph-bold" :class="abierto ? 'ph-minus' : 'ph-plus'" aria-hidden="true"></i></button>
            <div id="alertas-filtros-extra" class="rm-alert-filter-panel" x-show="abierto" x-cloak>
            <x-ui.alerta-filtro-chips label="Origen registrado" campo="filtroOrigen" :actual="$filtroOrigen" :opciones="[''=>'Todos los orígenes'] + collect($origenesDisponibles)->mapWithKeys(fn($o)=>[$o=>str_replace('_',' ',$o)])->all()" />
            <x-ui.alerta-filtro-chips label="Responsabilidad" campo="filtroResponsabilidad" :actual="$filtroResponsabilidad" :opciones="[''=>'Cualquier responsable','SIN_RESPONSABLE'=>'Sin responsable'] + (auth()->user()->personal ? ['PROPIAS'=>'A mi cargo'] : [])" />
            <div class="rm-alert-filters">
                <x-ui.field label="Registradas desde" for="alertas-desde" error="fechaDesde"><x-ui.calendario label="Registradas desde" id="alertas-desde" wire:model.live="fechaDesde" :max="$fechaHasta" aria-invalid="{{ $errors->has('fechaDesde') ? 'true' : 'false' }}" aria-describedby="alertas-desde-error" /></x-ui.field>
                <x-ui.field label="Registradas hasta" for="alertas-hasta" error="fechaHasta"><x-ui.calendario label="Registradas hasta" id="alertas-hasta" wire:model.live="fechaHasta" :min="$fechaDesde" aria-invalid="{{ $errors->has('fechaHasta') ? 'true' : 'false' }}" aria-describedby="alertas-hasta-error" /></x-ui.field>
            </div>
            </div>
        </div>
    </x-ui.filter-bar>
    <div class="rm-alert-list-heading" id="alertas-bandeja"><div><h2>{{ $perfil['bandeja'] }}</h2><p>{{ $alertas->total() }} de {{ $conteos['total'] }} alerta(s) de tu ámbito · {{ $filtroEstado === 'ACTIVAS' ? 'Pendientes actuales' : match($filtroEstado) { 'POR_ATENDER'=>'Por atender', 'ANTERIORES'=>'Registros anteriores', 'CON_LIMITE_SUPERADO'=>'Activas con límite superado', ''=>'Todos los estados', default=>LenguajeAlerta::estado($filtroEstado) } }}</p>@if(str_starts_with($filtroResponsabilidad,'PERSONAL:'))<button type="button" class="rm-btn rm-btn-ghost" wire:click="setFiltroRapido('filtroResponsabilidad','')">Quitar filtro de responsable</button>@endif</div>@if($alertas->total() > 0)<p class="rm-help">{{ $sinResponsableConsulta }} sin responsable registrado en esta consulta</p>@endif</div>
    <section class="rm-alert-case-list" aria-label="Alertas de la consulta">
        @forelse($alertas as $alerta)
            <article data-priority="{{ $alerta->prioridad }}" class="rm-alert-case {{ $traza && $traza['codigo'] === $alerta->getKey() ? 'is-selected' : '' }} {{ in_array($alerta->prioridad, ['CRITICO','CRITICA']) ? 'is-critical' : '' }}" wire:key="alerta-{{ $alerta->getKey() }}">
                <header class="rm-alert-case__header"><div class="rm-alert-case__signal"><i class="ph-bold ph-bell-ringing" aria-hidden="true"></i><span>Alerta</span><span class="rm-badge rm-alert-severity {{ in_array($alerta->prioridad, ['CRITICO','CRITICA']) ? 'is-critical' : (in_array($alerta->prioridad,['ALTO','ALTA']) ? 'is-high' : '') }}">{{ LenguajeAlerta::prioridad($alerta->prioridad) }}</span><span class="rm-badge">{{ LenguajeAlerta::estado($alerta->estado) }}</span></div></header>
                <p class="rm-alert-case__status">{{ LenguajeAlerta::situacion($alerta) }}</p>
                <div class="rm-alert-case__specialties">
                    @forelse($vinculosAlertas[$alerta->getKey()] ?? [] as $vinculo)<p><strong>{{ $vinculo['nombre'] }}</strong><span>{{ implode(' · ', $vinculo['motivos']) }}</span></p>
                    @empty<p>Sin vínculo profesional verificable con tus permisos de consulta.</p>@endforelse
                </div>
                <div class="rm-alert-case__body">
                    <div class="rm-alert-case__reason"><p class="rm-alert-case__resident-label">Nombre del residente</p><h3>{{ $alerta->residente?->nombre_completo }}</h3><p class="rm-alert-case__resident-label">Tipo de alerta</p><h4>{{ LenguajeAlerta::tipo($alerta->tipo) }}</h4><p class="rm-alert-case__resident-label">Motivo registrado</p><p class="rm-alert-case__description">{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alerta->descripcion) }}</p></div>
                    <dl class="rm-alert-case__facts">
                        <div><dt><i class="ph-bold ph-map-pin" aria-hidden="true"></i>Ubicación actual</dt><dd>{{ $alerta->residente?->cama?->habitacion?->codigo ?: 'Habitación no registrada' }} · {{ $alerta->residente?->cama?->codigo ?: 'Cama no registrada' }}</dd></div>
                        <div><dt><i class="ph-bold ph-user-check" aria-hidden="true"></i>Responsable del seguimiento</dt><dd>{{ \App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta::responsable($alerta->responsable) }}</dd></div>
                        <div><dt><i class="ph-bold ph-clock" aria-hidden="true"></i>Registrada</dt><dd><time datetime="{{ $alerta->fecha_hora?->toIso8601String() }}">{{ $alerta->fecha_hora?->format('d/m/Y H:i') ?: 'Fecha no registrada' }}</time></dd></div>
                        <div><dt><i class="ph-bold ph-git-branch" aria-hidden="true"></i>Origen</dt><dd>{{ str_replace('_',' ', $alerta->modulo ?: 'Sin origen registrado') }}</dd></div>
                        @if($alerta->fecha_hora_limite)<div><dt>Límite registrado</dt><dd>{{ $alerta->fecha_hora_limite->format('d/m/Y H:i') }}</dd></div>@endif
                    </dl>
                </div>
                <footer class="rm-alert-case__actions">
                    <span class="rm-alert-case__provenance"><i class="ph-bold {{ $alerta->generacion === 'MANUAL' ? 'ph-note-pencil' : 'ph-bell-ringing' }}" aria-hidden="true"></i>{{ match($alerta->generacion) { 'MANUAL' => 'Registro manual', 'AUTOMATICA', 'AUTOMATICO' => 'Detección registrada', default => 'Generación sin registrar' } }}</span>
                    <div class="rm-alert-case__primary-actions">
                        <x-ui.action-button variant="secondary" wire:click="verDetalle('{{ $alerta->getKey() }}')" loading="verDetalle('{{ $alerta->getKey() }}')" icono="ph-arrow-up-right">Ver detalle</x-ui.action-button>
                        <x-ui.action-button :variant="$definicionEspecialidad ? 'primary' : 'ghost'" wire:click="seleccionarTraza('{{ $alerta->getKey() }}')" loading="seleccionarTraza('{{ $alerta->getKey() }}')" aria-pressed="{{ $traza && $traza['codigo'] === $alerta->getKey() ? 'true' : 'false' }}" :icono="$perfil['icono']">{{ $perfil['seleccionar'] }}</x-ui.action-button>
                        @if($puedeIntervenir && in_array($alerta->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true))
                            @if($alerta->estado === 'EN_ATENCION')<x-ui.action-button wire:click="verDetalle('{{ $alerta->getKey() }}')" loading="verDetalle('{{ $alerta->getKey() }}')" icono="ph-note-pencil">Añadir seguimiento</x-ui.action-button>
                            @else<x-ui.action-button wire:click="atenderAlerta('{{ $alerta->getKey() }}')" loading="atenderAlerta('{{ $alerta->getKey() }}')" icono="ph-first-aid">Registrar intervención</x-ui.action-button>@endif
                        @elseif($puedeCoordinar && in_array($alerta->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true))
                            <x-ui.action-button wire:click="verDetalle('{{ $alerta->getKey() }}')" loading="verDetalle('{{ $alerta->getKey() }}')" icono="ph-note-pencil">Registrar coordinación</x-ui.action-button>
                        @endif
                        @if($definicionEspecialidad)<x-ui.action-button variant="secondary" wire:click="abrirRegistroEspecialidad('{{ $alerta->getKey() }}')" loading="abrirRegistroEspecialidad('{{ $alerta->getKey() }}')" icono="ph-notebook">{{ $preview || !auth()->user()->can($definicionEspecialidad['permiso']) ? 'Ver campos: ' : 'Registrar: ' }}{{ mb_strtolower($definicionEspecialidad['titulo']) }}</x-ui.action-button>@endif
                        @can('signos_vitales.ver')<x-ui.action-button variant="ghost" wire:click="verGraficos('{{ $alerta->cod_residente }}')" loading="verGraficos('{{ $alerta->cod_residente }}')" icono="ph-chart-line">Mediciones</x-ui.action-button>@endcan
                    </div>
                </footer>
            </article>
        @empty
            <x-ui.empty-state icono="ph-bell-slash" :titulo="$conteos['total'] > 0 ? 'Ninguna alerta coincide con estos filtros' : ($filtroEspecialidad ? 'No hay alertas con este vínculo profesional' : 'Aún no hay alertas registradas en tu ámbito')" :texto="$conteos['total'] > 0 ? 'Ajusta el estado, la prioridad o las fechas para revisar las alertas de esta especialidad.' : ($filtroEspecialidad ? 'No consta un responsable ni una fuente autorizada que vincule alertas a esta selección. Puedes consultar Todas las autorizadas. No se crean avisos para llenar esta vista.' : 'Las alertas aparecerán aquí cuando se registre una situación que requiera seguimiento.')" />
            @if($filtroEspecialidad)<button type="button" class="rm-btn rm-btn-secondary" wire:click="setFiltroRapido('filtroEspecialidad','')">Consultar todas las especialidades autorizadas</button>@endif
            @if($conteos['total'] > 0)<button type="button" class="rm-btn rm-btn-secondary" wire:click="filtrarResumen('todas')">Ver todas las alertas</button>@endif
        @endforelse
    </section>
    {{ $alertas->links() }}
    </div>
    <aside class="rm-alert-workspace-grid__aside" x-ref="contextoCaso" tabindex="-1" aria-label="Contexto de trabajo del caso seleccionado">
        @include('livewire.alertas.puesto-profesional')
        <x-ui.alerta-trazabilidad :traza="$traza" :titulo="$perfil['seleccionar']" :icono="$perfil['icono']" />
        @if($traza && in_array($perfil['foco'],['auditoria','supervision'],true))
        <section class="rm-alert-trace"><h3>Movimiento del registro</h3><p>Eventos guardados de la alerta seleccionada.</p>
            @forelse(array_slice($traza['movimientos'],0,3) as $movimiento)<article class="rm-alert-recent-event"><strong>{{ $movimiento['tipo'] }}</strong><p>{{ $movimiento['texto'] }}</p></article>@empty<p>No hay eventos guardados para esta alerta.</p>@endforelse
        </section>
        @endif
        <section class="rm-alert-trace"><h3>Análisis de la consulta</h3><p>Distribuciones reales con los mismos filtros, antes de paginar.</p><button type="button" class="rm-btn rm-btn-secondary" wire:click="$toggle('mostrarAnalisis')" aria-expanded="{{ $mostrarAnalisis ? 'true' : 'false' }}" aria-controls="alertas-analisis">{{ $mostrarAnalisis ? 'Ocultar análisis y gráficos' : 'Ver análisis y gráficos' }}</button></section>
        <a href="#alertas-bandeja" class="rm-btn rm-btn-ghost">Volver a la bandeja de casos</a>
    </aside>
    </div>
    <div id="alertas-analisis">@if($mostrarAnalisis)
    @if($alertas->total() > 0)
        <section class="rm-alert-analytics" aria-labelledby="alertas-graficas-titulo">
            <header class="rm-alert-analytics__heading"><span><i class="ph-bold ph-chart-donut" aria-hidden="true"></i><strong id="alertas-graficas-titulo">Gráficas de tus alertas</strong></span><p>{{ $alertas->total() }} alertas en esta consulta · mismos filtros que la lista, antes de paginar.</p></header>
            <div class="rm-alert-context-grid">
                <x-ui.dashboard-data-panel :panel="['title'=>'Prioridad registrada','subtitle'=>'Cuántas alertas hay en cada prioridad','type'=>'donut','items'=>collect($chartData['niveles'])->map(fn($n,$label)=>['label'=>str_replace('_',' ', $label ?: 'Sin prioridad registrada'),'value'=>$n,'tone'=>match($label) {'CRITICO','CRITICA'=>'danger','ALTO','ALTA','MEDIO','MEDIA'=>'warning','BAJO','BAJA'=>'info',default=>'neutral'}])->values()->all(),'icon'=>'ph-warning-octagon']" />
                <x-ui.dashboard-data-panel :panel="['title'=>'Estado registrado','subtitle'=>'Situación guardada de las alertas consultadas','type'=>'bars','items'=>collect($chartData['estados'])->map(fn($n,$label)=>['label'=>str_replace('_',' ', $label ?: 'Sin estado registrado'),'value'=>$n])->values()->all(),'icon'=>'ph-list-checks']" />
                <x-ui.dashboard-data-panel :panel="['title'=>'Origen de las alertas','subtitle'=>'Proceso vinculado al registro, cuando consta','type'=>'bars','items'=>collect($chartData['origenes'])->map(fn($n,$label)=>['label'=>str_replace('_',' ', $label ?: 'Sin origen registrado'),'value'=>$n])->values()->all(),'icon'=>'ph-chart-bar']" />
            </div>
        </section>
    @endif
    @endif</div>
    @if($perfil['foco'] === 'auditoria')
        <section class="rm-alert-trace"><h3>Vigilancia automática</h3><span class="rm-badge">Pendiente de habilitación</span><p class="rm-help">La ejecución sin sesión requiere una autoría institucional aprobada. La detección manual autorizada sigue disponible. No se confirma vigilancia automática activa.</p></section>
    @endif
    @php($alertaActiva = $detalle)
    @include('livewire.alertas.modales.detalle')
    @include('livewire.alertas.modales.crear')
    @include('livewire.alertas.modales.especialidad')
    @include('livewire.alertas.modales.atender')
    @include('livewire.alertas.modales.cerrar')
    @include('livewire.alertas.modales.resultado-cierre')
    @include('livewire.alertas.modales.drawer-graficos')
    @include('livewire.alertas.modales.drawer-ubicacion')
</div>
