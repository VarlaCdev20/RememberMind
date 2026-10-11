<div class="rm-signos__visual">
<div class="rm-signos__visual-heading"><h6><i class="ph ph-chart-line" aria-hidden="true"></i> Evolución</h6><span x-text="meta[active].label"></span></div>
<p class="rm-signos__chart-summary" x-text="chartSummary(active)"></p>
<div class="rm-signos__visual-metrics">
    <div><span>Última guardada</span><p><strong x-text="previous(active) ? labelOf(previous(active), active) : '—'"></strong><small x-text="meta[active].unit"></small></p><small x-text="previous(active) ? previous(active).fecha : 'Sin lectura anterior'"></small></div>
    <div><span>Actual · Sin guardar</span><p><strong x-text="validPreview(active) ? previewLabel(active) : '—'"></strong><small x-text="meta[active].unit"></small></p><small x-show="!validPreview(active)">Introduce una medición</small>
        <span x-show="validPreview(active)" class="rm-signos__current-status" :data-tone="toneOf(active)" aria-live="polite"><i class="ph-bold" :class="['warning', 'high', 'danger'].includes(toneOf(active)) ? 'ph-warning-circle' : (['success', 'target'].includes(toneOf(active)) ? 'ph-check-circle' : 'ph-info')" aria-hidden="true"></i><span x-text="toneLabel(active)"></span></span>
    </div>
</div>
<template x-if="chartRows(active).length > 0">
    <div class="rm-signos__chart">
        <div class="rm-signos__chart-canvas-wrap">
            <svg class="rm-signos__chart-svg" x-id="['signos-area-primary', 'signos-area-secondary']" viewBox="0 0 360 224" role="group" :aria-label="'Evolución de ' + meta[active].label + ' en ' + meta[active].unit">
                <defs>
                    <linearGradient :id="$id('signos-area-primary')" x1="0" y1="0" x2="0" y2="1"><stop class="rm-signos__area-stop" offset="0%" stop-opacity=".32"></stop><stop class="rm-signos__area-stop" offset="100%" stop-opacity=".02"></stop></linearGradient>
                    <linearGradient :id="$id('signos-area-secondary')" x1="0" y1="0" x2="0" y2="1"><stop class="rm-signos__area-stop rm-signos__area-stop--dia" offset="0%" stop-opacity=".32"></stop><stop class="rm-signos__area-stop rm-signos__area-stop--dia" offset="100%" stop-opacity=".02"></stop></linearGradient>
                </defs>
                <text class="rm-signos__chart-unit" x="48" y="15" x-text="meta[active].unit"></text>
                @for($tickIndex = 0; $tickIndex < 7; $tickIndex++)
                    <g x-data="{ tick: null }" x-effect="tick = chartTicks(active)[{{ $tickIndex }}] || null" x-show="tick" x-cloak>
                        <line class="rm-signos__chart-grid" x1="48" x2="336" :y1="tick?.y ?? 0" :y2="tick?.y ?? 0"></line>
                        <text class="rm-signos__chart-axis" x="40" :y="(tick?.y ?? 0) + 4" text-anchor="end" x-text="tick?.label ?? ''"></text>
                    </g>
                @endfor
                @for($bandIndex = 0; $bandIndex < 2; $bandIndex++)
                    <g aria-hidden="true" x-data="{ band: null }" x-effect="band = chartBands(active)[{{ $bandIndex }}] || null" x-show="band" x-cloak>
                        <rect class="rm-signos__chart-band" :class="band?.field === 'dia' ? 'rm-signos__chart-band--dia' : ''" x="48" width="288" :y="band?.y ?? 0" :height="band?.height ?? 0"><title x-text="band?.label ?? ''"></title></rect>
                    </g>
                @endfor
                <polygon class="rm-signos__chart-area" :fill="'url(#' + $id('signos-area-primary') + ')'" :points="chartAreaPoints(active)" aria-hidden="true"></polygon>
                <polygon class="rm-signos__chart-area rm-signos__chart-area--preview" :fill="'url(#' + $id('signos-area-primary') + ')'" :points="chartAreaPoints(active, 'value', true)" aria-hidden="true"></polygon>
                <polygon x-show="active === 'pa'" class="rm-signos__chart-area" :fill="'url(#' + $id('signos-area-secondary') + ')'" :points="chartAreaPoints(active, 'dia')" aria-hidden="true"></polygon>
                <polygon x-show="active === 'pa'" class="rm-signos__chart-area rm-signos__chart-area--preview" :fill="'url(#' + $id('signos-area-secondary') + ')'" :points="chartAreaPoints(active, 'dia', true)" aria-hidden="true"></polygon>
                <polyline class="rm-signos__chart-path" fill="none" :points="chartHistoryPoints(active)"></polyline>
                <polyline class="rm-signos__chart-path rm-signos__chart-path--preview" fill="none" :points="chartPreviewPoints(active)"></polyline>
                <polyline x-show="active === 'pa'" class="rm-signos__chart-path rm-signos__chart-path--dia" fill="none" :points="chartHistoryPoints(active, 'dia')"></polyline>
                <polyline x-show="active === 'pa'" class="rm-signos__chart-path rm-signos__chart-path--dia rm-signos__chart-path--preview" fill="none" :points="chartPreviewPoints(active, 'dia')"></polyline>
                <line x-show="selectedPoint" class="rm-signos__chart-crosshair" :x1="selectedPoint?.x ?? 0" :x2="selectedPoint?.x ?? 0" y1="28" y2="160" aria-hidden="true"></line>
                @foreach(['value', 'dia'] as $chartSeries)
                    @for($markerIndex = 0; $markerIndex < 5; $markerIndex++)
                        <g x-data="{ row: null }" x-effect="row = {{ $chartSeries === 'dia' ? "active === 'pa' ? chartMarkers(active, 'dia')[{$markerIndex}] || null : null" : "chartMarkers(active)[{$markerIndex}] || null" }}" x-show="row" x-cloak>
                            <circle class="rm-signos__chart-hit" :cx="row?.x ?? 0" :cy="row?.y ?? 0" r="18" aria-hidden="true" @click="selectedPoint = row" @mouseenter="selectedPoint = row"></circle>
                            @if($chartSeries === 'value')
                                <circle :cx="row?.x ?? 0" :cy="row?.y ?? 0" r="5"
                            @else
                                <rect :x="(row?.x ?? 0) - 5" :y="(row?.y ?? 0) - 5" width="10" height="10" rx="2"
                            @endif
                                class="rm-signos__chart-dot {{ $chartSeries === 'dia' ? 'rm-signos__chart-dot--dia' : '' }}"
                                :data-tone="row?.tone" :class="row?.preview ? 'rm-signos__chart-dot--current' : 'rm-signos__chart-dot--history'"
                                tabindex="0" role="button" :aria-label="row ? row.date + ': ' + row.markerLabel : ''"
                                @mouseenter="selectedPoint = row" @focus="selectedPoint = row" @click="selectedPoint = row"
                                @keydown.enter.prevent="selectedPoint = row" @keydown.space.prevent="selectedPoint = row"
                                @keydown.escape.prevent="selectedPoint = null">
                                <title x-text="row ? row.date + ': ' + row.markerLabel : ''"></title>
                            @if($chartSeries === 'value')</circle>@else</rect>@endif
                        </g>
                    @endfor
                @endforeach
                @for($tooltipIndex = 0; $tooltipIndex < 2; $tooltipIndex++)
                    <g aria-hidden="true" class="rm-signos__chart-tooltip" x-data="{ tip: null }" x-effect="tip = chartTooltip(active)[{{ $tooltipIndex }}] || null" x-show="tip" :data-series="tip?.field" x-cloak>
                        <rect :x="tip?.x ?? 0" :y="tip?.y ?? 0" width="142" height="24" rx="12"></rect>
                        <text :x="(tip?.x ?? 0) + 71" :y="(tip?.y ?? 0) + 16" text-anchor="middle" x-text="tip?.label ?? ''"></text>
                    </g>
                @endfor
                @for($timeIndex = 0; $timeIndex < 2; $timeIndex++)
                    <g x-data="{ time: null }" x-effect="time = chartTimeLabels(active)[{{ $timeIndex }}] || null" x-show="time" x-cloak>
                        <text class="rm-signos__chart-axis" :x="time?.x ?? 0" y="184" :text-anchor="time?.anchor ?? 'middle'">
                            <tspan :x="time?.x ?? 0" x-text="time?.day ?? ''"></tspan>
                            <tspan :x="time?.x ?? 0" dy="17" x-text="time?.time ?? ''"></tspan>
                        </text>
                    </g>
                @endfor
            </svg>
        </div>
        <p class="rm-signos__chart-legend rm-signos__chart-legend--series" x-show="active === 'pa'"><span>Sistólica · círculo</span><span>Diastólica · cuadrado</span></p>
        <p class="rm-signos__chart-legend rm-signos__chart-legend--records"><span>Lectura guardada</span><span x-show="validPreview(active)">Actual · Sin guardar</span></p>
        <div class="rm-signos__point-detail">
            <i class="ph ph-cursor-click" aria-hidden="true"></i>
            <p class="rm-signos__chart-detail" aria-live="polite" x-text="selectedPoint ? selectedPoint.date + ' · ' + selectedPoint.markerLabel : 'Selecciona una lectura para ver su detalle.'"></p>
            <button type="button" x-show="selectedPoint" x-cloak @click="selectedPoint = null" class="rm-signos__point-clear" aria-label="Cerrar detalle de la lectura"><i class="ph ph-x" aria-hidden="true"></i></button>
        </div>
        <div class="rm-signos__chart-objectives" x-show="chartBands(active).length">
            <strong>Objetivo médico de esta lectura</strong>
            <template x-for="band in chartBands(active)" :key="band.field"><span x-text="(active === 'pa' ? (band.field === 'sis' ? 'Sistólica · ' : 'Diastólica · ') : '') + band.min + '–' + band.max + ' ' + meta[active].unit"></span></template>
        </div>
        <details class="rm-signos__chart-help"><summary>Cómo leer la gráfica</summary><p>Escala ajustada a los valores mostrados; las áreas no se suman. El marcador hueco identifica una lectura sin guardar. Usa clic, Tab y Enter para consultar cada punto.</p></details>
    </div>
</template>
<p class="rm-signos__chart-empty" x-show="!chartRows(active).length" x-text="emptyHistoryMessage(active)"></p>
<section class="rm-signos__comparison" x-show="validPreview(active) && previous(active)" aria-label="Comparación con la última medición">
    <h6>Comparación con la última medición</h6>
    <p class="rm-signos__comparison-date" x-text="'Valores en ' + meta[active].unit + ' · Actual sin guardar'"></p>
    <table class="rm-signos__comparison-table">
        <caption class="sr-only">Diferencias numéricas con la última lectura guardada; no indican mejoría o deterioro clínico.</caption>
        <thead><tr><th scope="col">Medición</th><th scope="col">Anterior</th><th scope="col">Actual</th><th scope="col">Cambio</th></tr></thead>
        <tbody><template x-for="(comparison, index) in comparisonRows(active)" :key="index"><tr>
            <th scope="row" x-text="comparison.label"></th>
            <td x-text="comparison.previous"></td><td class="rm-signos__comparison-current" x-text="comparison.current"></td>
            <td><span class="rm-signos__change-value"><i class="ph-bold" :class="comparison.icon" aria-hidden="true"></i><strong x-text="comparison.difference"></strong></span><small x-text="comparison.direction"></small></td>
        </tr></template></tbody>
    </table>
    <p class="rm-signos__chart-scale-note">Cambio numérico; consulta la interpretación clínica en el formulario.</p>
</section>
</div>
