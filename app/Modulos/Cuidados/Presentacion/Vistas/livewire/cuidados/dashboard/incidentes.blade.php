<x-ui.card variant="soft" class="rm-nursing-dashboard__incidents rm-chart-card rm-incident-trend"
    aria-label="Evolución de incidentes" data-incident-trend
    data-trend="{{ $incidentesTendencia ? json_encode($incidentesTendencia, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG) : '' }}">
    <x-ui.section-header title="Evolución de incidentes"
        :subtitle="$incidentesTendencia === null ? 'Últimos 7 días · sin turno activo' : (($dashboard['modo'] ?? '') === 'EN_TURNO' ? 'Últimos 7 días · residentes de mi turno' : 'Últimos 7 días · mis residentes asignados')"
        icon="ph-chart-line" level="2" />
    <div class="rm-incident-trend__loading" wire:loading aria-live="polite">
        <x-ui.skeleton variant="text" label="Cargando evolución de incidentes" />
        <x-ui.skeleton variant="card" label="Cargando gráfica de incidentes" />
    </div>
    <div class="rm-incident-trend__content" wire:loading.remove>
        @if($incidentesTendencia === null)
            <x-ui.empty-state compact icono="ph-chart-line" titulo="Sin datos de incidentes" texto="La evolución aparecerá con un turno activo." />
        @elseif($incidentesTendencia['total_periodo'] === 0)
            <x-ui.empty-state compact icono="ph-check-circle" titulo="Sin incidentes recientes" texto="No hay registros en los últimos 7 días." />
        @else
            <div class="rm-incident-trend__chart" wire:ignore>
                <canvas id="incidentTrendCanvas-{{ $this->getId() }}" role="img"
                    aria-label="Evolución diaria de incidentes de los últimos siete días para residentes del turno">Evolución diaria de incidentes.</canvas>
            </div>
            <div class="rm-incident-trend__legend" aria-label="Categorías mostradas">
                @foreach($incidentesTendencia['datasets'] as $serie)
                    <span><i style="--incident-series-color: {{ $serie['color'] }}" aria-hidden="true"></i>{{ $serie['label'] }}</span>
                @endforeach
            </div>
            <details class="rm-nursing-chart-detail">
                <summary>Ver cantidades por fecha</summary>
                <ul>
                    @foreach($incidentesTendencia['labels'] as $indice => $fecha)
                        <li><strong>{{ $incidentesTendencia['labels_completas'][$indice] ?? $fecha }}</strong>
                            <span>@foreach($incidentesTendencia['datasets'] as $serie){{ $serie['label'] }}: {{ $serie['data'][$indice] ?? 'Sin dato' }}@if(!$loop->last) · @endif @endforeach</span>
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif

        @if($incidentesTendencia !== null)
            <div class="rm-incident-trend__footer">
                <div class="rm-incident-trend__stat">
                    <span>Total semanal</span>
                    <strong>{{ $incidentesTendencia['total_periodo'] }}</strong>
                    <small class="rm-incident-trend__variation rm-incident-trend__variation--{{ $incidentesTendencia['variacion_tono'] }}">
                        {{ $incidentesTendencia['variacion_texto'] }}
                    </small>
                </div>
                @if($incidentesTendencia['categoria_principal'])
                    <div class="rm-incident-trend__stat rm-incident-trend__stat--category">
                        <span>Incidente más frecuente</span>
                        <strong>{{ $incidentesTendencia['categoria_principal'] }}</strong>
                        <small>{{ $incidentesTendencia['categoria_principal_total'] }} esta semana</small>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-ui.card>
