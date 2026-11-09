@inject('presentarEstadisticas', 'App\Modulos\Reportes\Presentacion\Reportes\GraficasGerencia')
@php($esInicioGerencia = $esInicioGerencia ?? false)
<section @class(['rm-card rm-view-statistics', 'rm-manager-home__statistics' => $esInicioGerencia]) x-data="rmReportStatistics()"
    data-graficas-iniciales="{{ json_encode($esInicioGerencia ? $presentarEstadisticas->paraInicio($reporte) : $presentarEstadisticas->series($reporte)) }}">
    @if($esInicioGerencia)
        <header class="rm-manager-home__statistics-header">
            <div class="rm-manager-home__statistics-title">
                <i class="ph-bold ph-chart-pie-slice" aria-hidden="true"></i>
                <div>
                    <h2>Estadísticas institucionales</h2>
                    <p class="rm-help">Situación actual de personal, camas y documentación.</p>
                </div>
            </div>
            <div class="rm-report-visuals__controls">
                @include('reportes::paginas.gerencia.partials.selector-graficas', ['selectorGraficaCompacto' => true])
                @include('reportes::paginas.gerencia.partials.exportar-vista', ['origenReporte' => 'inicio'])
            </div>
        </header>
        @include('reportes::paginas.gerencia.partials.visor-graficas', ['visorInicioGerencia' => true])
    @else
    <details open @toggle="if ($event.target.open) $nextTick(() => dibujarGraficas())">
        <summary><i class="ph-bold ph-chart-pie-slice" aria-hidden="true"></i> Estadísticas institucionales <i class="ph ph-caret-down" aria-hidden="true"></i></summary>
        @include('reportes::paginas.gerencia.partials.visor-graficas')
    </details>
    @endif
</section>
