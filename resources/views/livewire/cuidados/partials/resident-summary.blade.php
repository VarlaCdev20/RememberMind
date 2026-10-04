@php
    $alertCount = (int) $detalleResidente['alertas_count'];
    $alertLabel = auth()->user()?->can('alertas.ver')
        ? ($alertCount > 0 ? $alertCount.' '.($alertCount === 1 ? 'alerta activa' : 'alertas activas') : 'Sin alertas activas')
        : null;
@endphp
<x-ui.resident-summary :name="$detalleResidente['nombre_completo']" :metadata="($detalleResidente['edad_texto'] ?: 'Edad no registrada').' · '.$detalleResidente['habitacion_texto'].' · '.$detalleResidente['cama_texto']" :alert-label="$alertLabel" :has-alerts="$alertCount > 0">
    @if(auth()->user()?->can('signos_vitales.ver') || auth()->user()?->can('alertas.ver'))
        <x-slot:current>
            <div class="rm-resident-summary__grid rm-resident-summary__grid--current">
                @can('signos_vitales.ver')
                    <x-ui.quick-summary-card icon="ph-heartbeat" title="Último control" tone="clinical" :value="$detalleResidente['ultimos_signos'] ? 'PA '.$detalleResidente['ultimos_signos']['pa'] : 'Sin registro'" :detail="$detalleResidente['ultimos_signos'] ? 'FC '.$detalleResidente['ultimos_signos']['fc'].' · SpO₂ '.$detalleResidente['ultimos_signos']['sat'].' · '.$detalleResidente['ultimos_signos']['fecha_hora'] : 'Aún no hay signos vitales registrados.'" />
                @endcan
                @can('alertas.ver')
                    <x-ui.quick-summary-card icon="ph-warning-circle" title="Alertas activas" :tone="$alertCount > 0 ? 'danger' : 'calm'" :value="(string) $alertCount" :detail="$alertCount > 0 ? 'Revisar en la ficha clínica.' : 'Sin alertas activas registradas.'" />
                @endcan
            </div>
        </x-slot:current>
    @endif
    <x-slot:important>
        <div class="rm-resident-summary__grid">
            @can('alergias.ver')<x-ui.clinical-info-card icon="ph-shield-warning" title="Alergias" :value="$detalleResidente['alergias_resumen'] ?: 'No registradas'" />@endcan
            @can('indicaciones_clinicas.ver')<x-ui.clinical-info-card icon="ph-clipboard-text" title="Indicaciones importantes" :value="$detalleResidente['indicaciones_resumen'] ?: 'Sin indicaciones activas'" />@endcan
            @can('prescripciones.ver')<x-ui.clinical-info-card icon="ph-pill" title="Próxima medicación" :value="!empty($detalleResidente['proxima_medicacion']['hora']) ? $detalleResidente['proxima_medicacion']['nombre'] : 'Sin dosis programada'" :detail="!empty($detalleResidente['proxima_medicacion']['hora']) ? 'Hoy · '.$detalleResidente['proxima_medicacion']['hora'] : null" />@endcan
            @can('ejecuciones_cuidado.ver')<x-ui.clinical-info-card icon="ph-calendar-check" title="Próximo cuidado" :value="$detalleResidente['proxima_atencion_texto'] ?: 'Sin cuidado programado'" :detail="$detalleResidente['proxima_atencion_hora']" />@endcan
        </div>
    </x-slot:important>
    @if(auth()->user()?->canAny(['atenciones.ver', 'ejecuciones_cuidado.ver', 'signos_vitales.ver']))
        <x-slot:recent>
            @if($detalleResidente['seguimiento_reciente'])
                <ol class="rm-resident-summary__timeline">
                    @foreach($detalleResidente['seguimiento_reciente'] as $record)
                        <x-ui.timeline-item :fecha="$record['fecha']" :hora="$record['hora']" :tipo="$record['tipo']" :dato="$record['dato']" />
                    @endforeach
                </ol>
            @else
                <p class="rm-resident-summary__empty">Aún no hay registros de Enfermería para mostrar.</p>
            @endif
        </x-slot:recent>
    @endif
</x-ui.resident-summary>
