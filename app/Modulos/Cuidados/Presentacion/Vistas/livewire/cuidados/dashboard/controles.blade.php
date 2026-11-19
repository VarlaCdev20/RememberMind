<x-ui.card class="rm-nursing-dashboard__controls rm-nursing-module" aria-labelledby="nursing-controls-title">
    <x-ui.section-header title="Controles por residente" :subtitle="$enTurno ? 'Selecciona al residente antes de registrar' : 'Consulta los registros de tus residentes · Solo lectura'" icon="ph-heartbeat" level="2" id="nursing-controls-title" />
    <div class="rm-nursing-dashboard__control-actions">
        @can('signos_vitales.ver')
            <a class="rm-btn rm-btn-primary" href="{{ route('admin.enfermeria.pacientes', ['cuidado' => 'signos']) }}"><i class="ph-bold ph-heartbeat" aria-hidden="true"></i>Signos vitales</a>
        @endcan
        @can('valoraciones_dolor.ver')
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes', ['cuidado' => 'dolor']) }}"><i class="ph-bold ph-hand-heart" aria-hidden="true"></i>Dolor</a>
        @endcan
    </div>
</x-ui.card>
