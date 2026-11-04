@if($detalleResidente && !$esSelectorRegistro)
    <div class="rm-resident-directory__register-context {{ $registroTipo === 'signos' ? 'rm-signos__resident-context' : '' }}">
        @if($detalleResidente['foto'])
            <img src="{{ $detalleResidente['foto'] }}" alt="Foto de {{ $detalleResidente['nombre_completo'] }}" class="rm-resident-directory__register-avatar">
        @else
            <span class="rm-resident-directory__register-avatar rm-resident-directory__register-avatar--initials" aria-hidden="true">{{ $detalleResidente['iniciales'] }}</span>
        @endif
        <div class="rm-resident-directory__register-identity">
            @if($registroTipo === 'signos')<span class="rm-signos__resident-label">Residente · Control de signos vitales</span>@endif
            <strong @if($esFormularioClinico) data-clinical-resident @endif>{{ $detalleResidente['nombre_completo'] }}</strong>
            <span>{{ $detalleResidente['edad_texto'] ?: 'Edad no registrada' }} · {{ $detalleResidente['habitacion_texto'] }} · {{ $detalleResidente['cama_texto'] }}</span>
        </div>
        @if($esFormularioClinico)
            <div class="rm-clinical-form__personnel">
                <span>{{ $registroTipo === 'movilidad' ? 'Profesional · Enfermería' : 'Profesional que registra'.(in_array($registroTipo, ['dolor', 'alimentacion', 'hidratacion', 'eliminacion'], true) ? ' · Enfermería' : '') }}</span><strong data-clinical-professional>{{ auth()->user()->name }}</strong>
                <span>Jornada · {{ $detalleResidente['turno_nombre'] ?: 'No disponible' }}</span>
                @if(in_array($registroTipo, ['alimentacion', 'hidratacion', 'eliminacion', 'movilidad'], true) && $turnoActual)
                    <span>{{ \Carbon\Carbon::parse($turnoActual->hora_inicio)->format('H:i') }}–{{ \Carbon\Carbon::parse($turnoActual->hora_cierre ?: $turnoActual->hora_fin)->format('H:i') }}</span>
                @endif
            </div>
        @endif
        @if($registroTipo === 'signos')
            <div class="rm-signos__resident-meta">
                <div class="rm-signos__context-personnel"><span><i class="ph-bold ph-user-circle" aria-hidden="true"></i> Profesional que registra</span><strong>{{ auth()->user()->name }}</strong></div>
                <div class="rm-signos__context-shift"><span><i class="ph-bold ph-clock" aria-hidden="true"></i> Jornada actual</span><strong>{{ $signosContextoTurno['nombre'] ?? 'Sin turno activo' }}</strong>@if(filled($signosContextoTurno['horario'] ?? null))<small>{{ $signosContextoTurno['horario'] }}</small>@endif</div>
            </div>
            @if(($detalleResidente['alertas_count'] ?? 0) > 0)
                <span class="rm-badge-pill rm-badge-pill--warning" title="Alertas actualmente registradas para este residente"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> Alertas activas · {{ $detalleResidente['alertas_count'] }}</span>
            @endif
        @else
            @can('alertas.ver')
                @if($detalleResidente['alertas_count'] > 0)
                    <div class="rm-resident-directory__register-alerts" aria-label="Alertas activas del residente">
                        @php($limiteAlertasRegistro = $detalleResidente['alertas_count'] > 2 ? 1 : 2)
                        @foreach(array_slice($detalleResidente['alertas'], 0, $limiteAlertasRegistro) as $alerta)
                            <span class="rm-resident-directory__register-alert" title="{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alerta['motivo']) }}"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> {{ \Illuminate\Support\Str::limit(\App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alerta['motivo']), 24) }}</span>
                        @endforeach
                        @if($detalleResidente['alertas_count'] > $limiteAlertasRegistro)
                            <span class="rm-resident-directory__register-alert" title="{{ $detalleResidente['alertas_count'] - $limiteAlertasRegistro }} alertas adicionales">+{{ $detalleResidente['alertas_count'] - $limiteAlertasRegistro }} alertas</span>
                        @endif
                    </div>
                @endif
            @endcan
        @endif
    </div>
@endif
