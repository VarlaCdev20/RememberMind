<div class="rm-resident-summary">
    <header class="rm-resident-summary__identity">
        <h3>{{ mb_strtoupper($detalleResidente['nombre_completo']) }}</h3>
        <p>{{ $detalleResidente['edad_texto'] ?: 'Edad no registrada' }} · {{ $detalleResidente['habitacion_texto'] }} · {{ $detalleResidente['cama_texto'] }}</p>
        @can('alertas.ver')
            <span class="rm-resident-summary__alert {{ $detalleResidente['alertas_count'] > 0 ? 'has-alerts' : '' }}"><i class="ph-bold {{ $detalleResidente['alertas_count'] > 0 ? 'ph-warning-circle' : 'ph-check-circle' }}" aria-hidden="true"></i>{{ $detalleResidente['alertas_count'] > 0 ? $detalleResidente['alertas_count'].' '.($detalleResidente['alertas_count'] === 1 ? 'alerta activa' : 'alertas activas') : 'Sin alertas activas' }}</span>
        @endcan
    </header>

    @if(auth()->user()?->can('signos_vitales.ver') || auth()->user()?->can('alertas.ver'))
        <section aria-labelledby="resident-summary-current-title">
            <h4 id="resident-summary-current-title" class="rm-resident-summary__section-title">Estado actual</h4>
            <div class="rm-resident-summary__grid rm-resident-summary__grid--current">
                @can('signos_vitales.ver')
                    <x-ui.quick-summary-card icon="ph-heartbeat" title="Último control" tone="clinical" :value="$detalleResidente['ultimos_signos'] ? 'PA '.$detalleResidente['ultimos_signos']['pa'] : 'Sin registro'" :detail="$detalleResidente['ultimos_signos'] ? 'FC '.$detalleResidente['ultimos_signos']['fc'].' · SpO₂ '.$detalleResidente['ultimos_signos']['sat'].' · '.$detalleResidente['ultimos_signos']['fecha_hora'] : 'Aún no hay signos vitales registrados.'" />
                @endcan
                @can('alertas.ver')
                    <x-ui.quick-summary-card icon="ph-warning-circle" title="Alertas activas" :tone="$detalleResidente['alertas_count'] > 0 ? 'danger' : 'calm'" :value="(string) $detalleResidente['alertas_count']" :detail="$detalleResidente['alertas_count'] > 0 ? 'Revisar en la ficha clínica.' : 'Sin alertas activas registradas.'" />
                @endcan
            </div>
        </section>
    @endif

    <section aria-labelledby="resident-summary-important-title">
        <h4 id="resident-summary-important-title" class="rm-resident-summary__section-title">Información importante</h4>
        <div class="rm-resident-summary__grid">
            @can('alergias.ver')
                <x-ui.clinical-info-card icon="ph-shield-warning" title="Alergias" :value="$detalleResidente['alergias_resumen'] ?: 'No registradas'" />
            @endcan
            @can('indicaciones_clinicas.ver')
                <x-ui.clinical-info-card icon="ph-clipboard-text" title="Indicaciones importantes" :value="$detalleResidente['indicaciones_resumen'] ?: 'Sin indicaciones activas'" />
            @endcan
            @can('prescripciones.ver')
                <x-ui.clinical-info-card icon="ph-pill" title="Próxima medicación" :value="!empty($detalleResidente['proxima_medicacion']['hora']) ? $detalleResidente['proxima_medicacion']['nombre'] : 'Sin dosis programada'" :detail="!empty($detalleResidente['proxima_medicacion']['hora']) ? 'Hoy · '.$detalleResidente['proxima_medicacion']['hora'] : null" />
            @endcan
            @can('ejecuciones_cuidado.ver')
                <x-ui.clinical-info-card icon="ph-calendar-check" title="Próximo cuidado" :value="$detalleResidente['proxima_atencion_texto'] ?: 'Sin cuidado programado'" :detail="$detalleResidente['proxima_atencion_hora']" />
            @endcan
        </div>
    </section>

    @if(auth()->user()?->canAny(['atenciones.ver', 'ejecuciones_cuidado.ver', 'signos_vitales.ver']))
        <section aria-labelledby="resident-summary-recent-title">
            <h4 id="resident-summary-recent-title" class="rm-resident-summary__section-title">Seguimiento reciente</h4>
            @if($detalleResidente['seguimiento_reciente'])
                <ol class="rm-resident-summary__timeline">
                    @foreach($detalleResidente['seguimiento_reciente'] as $registro)
                        <x-ui.timeline-item :fecha="$registro['fecha']" :hora="$registro['hora']" :tipo="$registro['tipo']" :dato="$registro['dato']" />
                    @endforeach
                </ol>
            @else
                <p class="rm-resident-summary__empty">Aún no hay registros de Enfermería para mostrar.</p>
            @endif
        </section>
    @endif
</div>
