<x-ui.card class="rm-nursing-dashboard__distribution rm-nursing-module rm-nursing-distribution rm-chart-card" aria-labelledby="nursing-distribution-title"
data-nursing-followup="{{ json_encode(['labels' => ['Sin alertas activas', 'Vigilancia', 'Alerta crítica'], 'values' => array_values($distribucionPacientes)], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) }}">
    <x-ui.section-header title="Estado de seguimiento" :subtitle="($dashboard['estado'] ?? '') === 'SIN_JORNADA_ACTIVA' ? 'Sin jornada activa' : (($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Mis residentes asignados' : 'Residentes asignados a tu turno')" icon="ph-chart-donut" level="2" id="nursing-distribution-title" />
    @if($totalSeguimiento === 0)
        <x-ui.empty-state compact icono="ph-users-three" titulo="Sin residentes en turno" texto="La distribución aparecerá cuando haya residentes asignados a una jornada activa." />
    @else
        <div class="rm-nursing-donut-layout">
            <div class="rm-nursing-donut-stage">
                <div class="rm-nursing-donut-canvas" wire:ignore>
                    <canvas id="nursingFollowupCanvas-{{ $this->getId() }}" role="img"
                        aria-label="{{ $totalSeguimiento }} residentes: {{ $distribucionPacientes['sin_alertas'] }} sin alertas activas, {{ $distribucionPacientes['vigilancia'] }} en vigilancia y {{ $distribucionPacientes['atencion'] }} con alerta crítica">Estado de seguimiento de los residentes.</canvas>
                </div>
            </div>
            <div class="rm-nursing-donut-detail">
            <p class="rm-nursing-donut-total"><strong>{{ $totalSeguimiento }}</strong> residentes</p>
            <ul class="rm-nursing-donut-legend" aria-label="Detalle del seguimiento">
                @foreach(['sin_alertas' => ['Sin alertas activas', 'mint'], 'vigilancia' => ['Vigilancia', 'sky'], 'atencion' => ['Alerta crítica', 'coral']] as $clave => [$etiqueta, $tono])
                    <li><span class="rm-nursing-donut-legend__label"><i class="rm-nursing-donut-legend__dot rm-nursing-donut-legend__dot--{{ $tono }}" aria-hidden="true"></i>{{ $etiqueta }}</span><span class="rm-nursing-donut-legend__value"><strong>{{ $distribucionPacientes[$clave] }}</strong><small>{{ round($distribucionPacientes[$clave] / $totalSeguimiento * 100) }}%</small></span></li>
                @endforeach
            </ul>
            </div>
        </div>
        <p class="rm-nursing-chart-note">Basado en alertas activas; no equivale a una valoración clínica.</p>
    @endif
</x-ui.card>
