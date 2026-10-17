<section class="rm-medication-workspace__analysis" aria-labelledby="medication-analysis-title"
    x-data="rmMedicacionAgendaGraficas" data-agenda='@json($graficasAgenda)'>
    <header class="rm-medication-workspace__analysis-heading">
        <div>
            <h2 id="medication-analysis-title"><i class="ph-bold ph-chart-bar" aria-hidden="true"></i> Distribución de la agenda</h2>
            <p>{{ \Carbon\Carbon::parse($graficasAgenda['fecha'])->format('d/m/Y') }} · Dosis del listado actual. Las gráficas cambian con tus filtros.</p>
        </div>
    </header>
    @if($graficasAgenda['total'] > 0)
        <div class="rm-medication-workspace__charts">
            <article class="rm-chart-card rm-medication-workspace__chart rm-medication-workspace__chart--states" aria-labelledby="medication-state-title">
                <div class="rm-chart-header"><h3 id="medication-state-title"><i class="ph-bold ph-chart-donut" aria-hidden="true"></i> Estado de las dosis</h3></div>
                <div class="rm-medication-workspace__state-layout">
                    <div class="rm-medication-workspace__donut">
                        <div class="rm-medication-workspace__canvas" wire:ignore>
                            <canvas id="medication-agenda-states" role="img" aria-label="Distribución por estado. Cantidades en la leyenda contigua."></canvas>
                        </div>
                        <div class="rm-medication-workspace__donut-total" aria-hidden="true"><strong>{{ $graficasAgenda['total'] }}</strong><span>dosis</span></div>
                    </div>
                    <ul class="rm-medication-workspace__legend" aria-label="Cantidad de dosis por estado">
                        @foreach($graficasAgenda['estados'] as $estado)
                            <li class="is-{{ $estado['tono'] }} {{ $estado['cantidad'] > 0 ? 'has-doses' : '' }}"><span><i class="rm-medication-workspace__dot is-{{ $estado['tono'] }}" aria-hidden="true"></i>{{ $estado['label'] }}</span><strong>{{ $estado['cantidad'] }}</strong></li>
                        @endforeach
                    </ul>
                </div>
                <p class="rm-medication-workspace__chart-note">{{ $graficasAgenda['total'] }} dosis en total · Administradas y omitidas tienen registro confirmado.</p>
            </article>
            <article class="rm-chart-card rm-medication-workspace__chart rm-medication-workspace__chart--hours" aria-labelledby="medication-hours-title">
                <div class="rm-chart-header"><h3 id="medication-hours-title"><i class="ph-bold ph-clock" aria-hidden="true"></i> Dosis por horario</h3><span>Hora programada</span></div>
                <div class="rm-medication-workspace__hours-canvas" wire:ignore>
                    <canvas id="medication-agenda-hours" role="img" aria-label="Cantidad de dosis por hora programada. Detalle disponible debajo."></canvas>
                </div>
                <details class="rm-medication-workspace__hours-detail">
                    <summary>Ver cantidades por horario</summary>
                    <ul>@foreach($graficasAgenda['horarios'] as $horario)<li><time>{{ $horario['label'] }}</time><strong>{{ $horario['cantidad'] }} dosis</strong></li>@endforeach</ul>
                    <p class="rm-medication-workspace__chart-note">Incluye todos los estados visibles. No representa la hora real de administración.</p>
                </details>
            </article>
        </div>
    @else
        <x-ui.empty-state icon="ph-chart-bar" title="Sin dosis para graficar" description="Cambia los filtros o la fecha para consultar otra agenda." />
    @endif
</section>
