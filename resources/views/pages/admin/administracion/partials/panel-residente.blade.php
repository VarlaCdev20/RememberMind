@php
    $nombreResidente = trim(implode(' ', array_filter([
        $residente->nombres, $residente->apellido_paterno, $residente->apellido_materno,
    ])));
    $responsable = $datosPanel['responsable'];
    $nombreResponsable = $responsable ? trim(implode(' ', array_filter([
        $responsable->nombres, $responsable->apellido_paterno, $responsable->apellido_materno,
    ]))) : null;
    $habitacion = $datosPanel['habitacion'];
    $supervision = $datosPanel['supervision'];
    $panelParams = $parametrosListadoResidentes + ['residente' => $residente->cod_residente];
    $urlCerrarPanel = route('admin.administracion.residentes', $parametrosListadoResidentes);
@endphp

<a class="rm-admin-resident-panel__backdrop" href="{{ $urlCerrarPanel }}" aria-label="Cerrar panel del residente"></a>
<aside class="rm-admin-resident-panel" aria-label="Panel del residente {{ $nombreResidente }}" tabindex="-1"
    x-data x-init="$nextTick(() => $el.focus())" x-on:keydown.escape.window="window.location.assign(@js($urlCerrarPanel))">
    <div class="rm-admin-resident-panel__top">
        <h2>Residente</h2>
        <a href="{{ $urlCerrarPanel }}" aria-label="Cerrar panel de {{ $nombreResidente }}"><i class="ph-bold ph-x" aria-hidden="true"></i></a>
    </div>

    <header class="rm-admin-resident-panel__identity">
        @if($residente->foto)
            <img src="{{ asset('storage/'.$residente->foto) }}" alt="Foto de {{ $nombreResidente }}">
        @else
            <span class="rm-admin-resident-panel__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($residente->nombres, 0, 1).mb_substr($residente->apellido_paterno, 0, 1)) }}</span>
        @endif
        <div>
            <h3>{{ $nombreResidente }}</h3>
            <p>{{ $residente->cod_residente }}{{ $residente->numero_documento ? ' · CI '.$residente->numero_documento : '' }}{{ $residente->edad !== null ? ' · '.$residente->edad.' años' : '' }}</p>
            <x-ui.status-badge :estado="$residente->estado" />
        </div>
    </header>

    <nav class="rm-admin-resident-panel__tabs" aria-label="Secciones del residente">
        @foreach(['resumen' => 'Resumen', 'datos' => 'Datos', 'salud' => 'Salud', 'documentos' => 'Documentos', 'historial' => 'Historial'] as $clave => $etiqueta)
            <a href="{{ route('admin.administracion.residentes', $panelParams + ['panel_tab' => $clave]) }}" @if($panelTab === $clave) aria-current="page" @endif>{{ $etiqueta }}</a>
        @endforeach
    </nav>

    <div class="rm-admin-resident-panel__body">
        @if($panelTab === 'resumen')
            <dl class="rm-admin-resident-panel__summary" aria-label="Resumen breve del residente">
                <div class="rm-admin-resident-panel__fact">
                    <dt><i class="ph-bold ph-bed" aria-hidden="true"></i> Habitación</dt>
                    <dd>
                        <strong class="rm-admin-resident-panel__value">{{ $habitacion->codigo ?? 'Sin habitación asignada' }}</strong>
                        @if($habitacion?->nombre)<span class="rm-admin-resident-panel__support">{{ $habitacion->nombre }}</span>@endif
                    </dd>
                </div>
                <div class="rm-admin-resident-panel__fact">
                    <dt><i class="ph-bold ph-user-circle" aria-hidden="true"></i> Responsable principal</dt>
                    <dd>
                        <strong class="rm-admin-resident-panel__value">{{ $nombreResponsable ?: 'Sin responsable registrado' }}</strong>
                        @if($nombreResponsable && $responsable->parentesco)<span class="rm-admin-resident-panel__support">{{ $responsable->parentesco }}</span>@endif
                    </dd>
                </div>
                <div class="rm-admin-resident-panel__fact rm-admin-resident-panel__fact--wide">
                    <dt><i class="ph-bold ph-heartbeat" aria-hidden="true"></i> Nivel de cuidado</dt>
                    <dd>{{ $supervision ? mb_convert_case(mb_strtolower(str_replace('_', ' ', $supervision->nivel_supervision)), MB_CASE_TITLE, 'UTF-8') : 'No registrado' }}</dd>
                </div>
                <div class="rm-admin-resident-panel__fact rm-admin-resident-panel__fact--wide">
                    <dt><i class="ph-bold ph-note-pencil" aria-hidden="true"></i> Observación breve</dt>
                    <dd class="rm-admin-resident-panel__observation">{{ $datosPanel['observacion_breve'] ?: 'Sin observación general registrada.' }}</dd>
                </div>
            </dl>
        @elseif($panelTab === 'datos')
            <section class="rm-admin-resident-panel__detail" aria-labelledby="panel-datos-personales">
                <h4 id="panel-datos-personales">Datos personales</h4>
                <dl>
                    <div><dt>Fecha de nacimiento</dt><dd>{{ $residente->fecha_nacimiento?->format('d/m/Y') ?: 'No registrada' }}</dd></div>
                    <div><dt>Documento</dt><dd>{{ $residente->numero_documento ?: 'No registrado' }}</dd></div>
                    <div><dt>Estado civil</dt><dd>{{ $residente->estado_civil ?: 'No registrado' }}</dd></div>
                    <div><dt>Teléfono</dt><dd>{{ $residente->celular ?: $residente->telefono ?: 'No registrado' }}</dd></div>
                </dl>
            </section>
            <section class="rm-admin-resident-panel__detail" aria-labelledby="panel-datos-residenciales">
                <h4 id="panel-datos-residenciales">Ubicación y responsable</h4>
                <dl>
                    <div><dt>Habitación</dt><dd>{{ $habitacion->codigo ?? 'Sin asignación' }}</dd></div>
                    <div><dt>Sector</dt><dd>{{ $habitacion->nombre ?? 'No registrado' }}</dd></div>
                    <div><dt>Planta</dt><dd>{{ $habitacion->piso ?? 'No registrada' }}</dd></div>
                    <div><dt>Tipo</dt><dd>{{ $habitacion->tipo ?? 'No registrado' }}</dd></div>
                    <div><dt>Responsable</dt><dd>{{ $nombreResponsable ?: 'Sin responsable registrado' }}</dd></div>
                    @if($responsable)
                        <div><dt>Parentesco</dt><dd>{{ $responsable->parentesco ?: 'No registrado' }}</dd></div>
                        <div><dt>Teléfono responsable</dt><dd>{{ $responsable->celular ?: $responsable->telefono ?: 'No registrado' }}</dd></div>
                        <div><dt>Email responsable</dt><dd>{{ $responsable->correo ?: 'No registrado' }}</dd></div>
                    @endif
                </dl>
            </section>
        @elseif($panelTab === 'salud')
            <section class="rm-admin-resident-panel__detail" aria-labelledby="panel-situacion-asistencial">
                <h4 id="panel-situacion-asistencial">Situación asistencial</h4>
                <dl>
                    <div><dt>Supervisión de la jornada vigente</dt><dd>{{ $supervision ? mb_convert_case(mb_strtolower(str_replace('_', ' ', $supervision->nivel_supervision)), MB_CASE_TITLE, 'UTF-8') : 'No registrada' }}</dd></div>
                    <div><dt>Alertas activas</dt><dd>{{ $datosPanel['alertas_activas'] }}</dd></div>
                </dl>
                <p>La información clínica detallada se consulta con permisos asistenciales en el expediente.</p>
            </section>
        @elseif($panelTab === 'documentos')
            <section class="rm-admin-resident-panel__detail" aria-labelledby="panel-documentos">
                <h4 id="panel-documentos">Documentos del residente</h4>
                @forelse($datosPanel['documentos'] as $documento)
                    <div class="rm-admin-resident-panel__record"><strong>{{ $documento->nombre }}</strong><span>{{ str_replace('_', ' ', $documento->tipo_documento) }} · {{ $documento->estado }}@if($documento->fecha_vencimiento) · Vence {{ \Carbon\Carbon::parse($documento->fecha_vencimiento)->format('d/m/Y') }}@endif</span></div>
                @empty
                    <p>Sin documentos registrados para este residente.</p>
                @endforelse
            </section>
        @else
            <section class="rm-admin-resident-panel__detail" aria-labelledby="panel-historial">
                <h4 id="panel-historial">Historial de estados</h4>
                @forelse($datosPanel['historial'] as $evento)
                    <div class="rm-admin-resident-panel__record"><strong>{{ str_replace('_', ' ', $evento->estado_nuevo) }}</strong><span>{{ \Carbon\Carbon::parse($evento->fecha_hora)->format('d/m/Y H:i') }}@if($evento->motivo) · {{ $evento->motivo }}@endif</span></div>
                @empty
                    <p>Sin cambios de estado registrados.</p>
                @endforelse
            </section>
        @endif
    </div>

    <footer class="rm-admin-resident-panel__footer">
        <a href="{{ route('admin.administracion.residentes.show', $residente->cod_residente) }}">Ver expediente completo <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
    </footer>
</aside>
