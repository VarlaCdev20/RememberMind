<x-ui.card class="rm-nursing-dashboard__activity-bars rm-nursing-module rm-chart-card" aria-labelledby="nursing-activity-bars-title"
    data-nursing-activity="{{ json_encode(['labels' => ['Realizadas', 'Pendientes', 'Retrasadas'], 'values' => [$accionesRealizadas, $accionesPendientes, $accionesRetrasadas]], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) }}">
    <x-ui.section-header title="Actividad del turno" subtitle="Acciones programadas según estado" icon="ph-chart-bar" level="2" id="nursing-activity-bars-title" />
    @if(($accionesTurno['total'] ?? 0) === 0)
        <x-ui.empty-state compact icono="ph-chart-bar" titulo="Sin acciones para graficar" texto="Las acciones del turno aparecerán aquí cuando exista una programación activa." />
    @else
        <div class="rm-nursing-analytics-canvas" wire:ignore>
            <canvas id="nursingActivityCanvas-{{ $this->getId() }}" role="img"
                aria-label="{{ $accionesRealizadas }} acciones realizadas, {{ $accionesPendientes }} pendientes y {{ $accionesRetrasadas }} retrasadas">Actividad del turno por estado.</canvas>
        </div>
        <ul class="rm-nursing-activity-key" aria-label="Cantidades de acciones por estado">
            @foreach([['Realizadas', $accionesRealizadas, 'mint'], ['Pendientes', $accionesPendientes, 'warning'], ['Retrasadas', $accionesRetrasadas, 'coral']] as [$etiqueta, $cantidad, $tono])
                <li><span><i class="rm-nursing-donut-legend__dot rm-nursing-donut-legend__dot--{{ $tono }}" aria-hidden="true"></i>{{ $etiqueta }}</span><strong>{{ $cantidad }}</strong></li>
            @endforeach
        </ul>
        <p class="rm-nursing-chart-note">{{ $accionesTurno['total'] }} {{ $accionesTurno['total'] === 1 ? 'acción programada' : 'acciones programadas' }}. Las retrasadas se cuentan por separado de las pendientes.</p>
    @endif
</x-ui.card>
