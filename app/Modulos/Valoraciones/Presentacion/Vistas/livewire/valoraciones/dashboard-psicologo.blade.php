<div class="rm-dashboard-composition rm-psychologist-dashboard">
    <x-ui.role-dashboard-hero
        eyebrow="Área de Psicología"
        :title="$saludo.', '.$primerNombre"
        role="Psicología"
        :date="now()->locale('es')->translatedFormat('d M Y')"
        scope="Atenciones y valoraciones propias"
        description="Consulta tus registros, próximas atenciones y alertas a tu cargo."
        :image="asset('images/FOTOS CENTRO DE ADULTOS MAYORES/558487013_1337134818424437_2282337776297854403_n.jpg')"
        rotation-context="psicologia"
        image-alt="Profesional acompañando a una persona mayor durante una actividad cognitiva"
    />

    @if($sinPersonalActivo)
        <x-ui.section-card title="Contexto profesional no disponible" icon="ph-identification-badge">
            <p role="status">No hay una vinculación de personal activo de Psicología para consultar registros propios.</p>
        </x-ui.section-card>
    @else
<<<<<<< HEAD
        @if(auth()->user()->hasRole('PSICOLOGO/A') && session(\App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService::SESSION_KEY) === null)
            @can('viewAny', \App\Models\Residente::class)
                <section class="rm-card rm-orion-panel" aria-labelledby="psychology-expert-access-title">
                    <div class="rm-orion-section-heading">
                        <div>
                            <h2 id="psychology-expert-access-title" class="rm-expert-section-title">Sistema experto</h2>
                            <p>Consulta los resultados cognitivos, sus evidencias y el historial de cada residente.</p>
                        </div>
                        <a href="{{ route('admin.sistema-experto.residentes') }}" class="rm-btn rm-btn-primary">
                            <i class="ph ph-brain" aria-hidden="true"></i>Abrir sistema experto
                        </a>
                    </div>
                </section>
            @endcan
        @endif
=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        <section aria-labelledby="psychology-activity-title">
            <div class="rm-psychology-dashboard__section-heading">
                <h2 id="psychology-activity-title">Tu actividad</h2>
            </div>
            <div class="rm-dashboard-overview rm-psychology-dashboard__metrics">
                @foreach($metrics as $metric)
                    <x-ui.metric-card :icon="$metric['icon']" :value="$metric['value']" :label="$metric['label']" :description="$metric['description']" :variant="$metric['variant']" />
                @endforeach
            </div>
        </section>

        <section aria-labelledby="psychology-analytics-title">
            <div class="rm-psychology-dashboard__section-heading">
                <h2 id="psychology-analytics-title">Actividad de Psicología</h2>
            </div>
            <div class="rm-psychology-dashboard__charts">
                <article class="rm-chart-card rm-psychology-chart" aria-labelledby="psychology-monthly-title">
                    <header class="rm-chart-header">
                        <div class="rm-chart-heading">
                            <h3 id="psychology-monthly-title" class="rm-chart-title">Seguimiento mensual</h3>
                            <p class="rm-chart-subtitle">Últimos seis meses · registros propios</p>
                        </div>
                    </header>
                    @if($chartEvolucion['tieneDatos'])
                        <div wire:key="psych-monthly-{{ md5(json_encode($chartEvolucion)) }}" wire:ignore
                             x-data="{
                                chartKey: 'psico-chart-evolucion-mensual',
                                init() { this.render(); window.RMCharts?.onThemeChange(() => this.render(), this.chartKey, this.$el); },
                                render() {
                                    if (typeof Chart === 'undefined' || !window.RMCharts?.presets) { setTimeout(() => this.render(), 80); return; }
                                    const api = window.RMCharts;
                                    const primary = api.color('cognitive');
                                    const secondary = api.color('clinical');
                                    const datasets = [
                                        { label: 'Atenciones finalizadas', data: @js($chartEvolucion['atenciones']), borderColor: primary, backgroundColor: api.hexToRgba(primary, .12), fill: true, tension: .3 },
                                        { label: 'Aplicaciones completas o vigentes', data: @js($chartEvolucion['aplicaciones']), borderColor: secondary, backgroundColor: api.hexToRgba(secondary, .08), fill: true, tension: .3 }
                                    ];
                                    const config = api.presets.area(@js($chartEvolucion['labels']), datasets, { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } });
                                    api.init(this.chartKey, this.$refs.canvas, config, () => this.render());
                                }
                             }" class="rm-psychology-chart__plot">
                            <canvas x-ref="canvas" role="img" aria-label="Atenciones finalizadas y aplicaciones completas o vigentes, mes a mes"></canvas>
                        </div>
                    @else
                        <x-ui.empty-state compact icono="ph-chart-line-up" titulo="Sin actividad registrada en este período" texto="Aquí aparecerán las atenciones finalizadas y aplicaciones completas o vigentes." />
                    @endif
<<<<<<< HEAD
                    <div class="rm-psychology-chart__series" aria-label="Series del seguimiento mensual">
                        <span><i class="rm-psychology-chart__swatch rm-psychology-chart__swatch--1" aria-hidden="true"></i>Atenciones finalizadas</span>
                        <span><i class="rm-psychology-chart__swatch rm-psychology-chart__swatch--2" aria-hidden="true"></i>Aplicaciones completas o vigentes</span>
                    </div>
=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                    <details class="rm-psychology-chart__data">
                        <summary>Ver cifras por mes</summary>
                        <table><caption class="sr-only">Actividad mensual propia de Psicología</caption>
                            <thead><tr><th scope="col">Mes</th><th scope="col">Atenciones</th><th scope="col">Aplicaciones</th></tr></thead>
                            <tbody>@foreach($chartEvolucion['labels'] as $indice => $mes)
                                <tr><th scope="row">{{ $mes }}</th><td>{{ $chartEvolucion['atenciones'][$indice] }}</td><td>{{ $chartEvolucion['aplicaciones'][$indice] }}</td></tr>
                            @endforeach</tbody>
                        </table>
                    </details>
                </article>

                <article class="rm-chart-card rm-psychology-chart" aria-labelledby="psychology-instruments-title">
                    <header class="rm-chart-header">
                        <div class="rm-chart-heading">
                            <h3 id="psychology-instruments-title" class="rm-chart-title">Instrumentos utilizados</h3>
                            <p class="rm-chart-subtitle">Últimos seis meses · aplicaciones completas o vigentes</p>
                        </div>
                    </header>
                    @if($chartInstrumentos['total'] > 0)
                        <div class="rm-psychology-chart__composition">
                        <div wire:key="psych-instruments-{{ md5(json_encode($chartInstrumentos)) }}" wire:ignore
                             x-data="{
                                chartKey: 'psico-chart-instrumentos',
                                init() { this.render(); window.RMCharts?.onThemeChange(() => this.render(), this.chartKey, this.$el); },
                                render() {
                                    if (typeof Chart === 'undefined' || !window.RMCharts?.presets) { setTimeout(() => this.render(), 80); return; }
                                    const api = window.RMCharts;
                                    const colors = ['cognitive', 'clinical', 'care', 'neutral', 'reference'].map(tone => api.color(tone));
                                    const config = api.presets.doughnut(@js($chartInstrumentos['labels']), @js($chartInstrumentos['data']), colors);
                                    api.init(this.chartKey, this.$refs.canvas, config, () => this.render());
                                }
                             }" class="rm-psychology-chart__plot rm-psychology-chart__plot--donut">
                            <canvas x-ref="canvas" role="img" aria-label="Cantidad de aplicaciones completas o vigentes por instrumento"></canvas>
                        </div>
                        <div class="rm-psychology-chart__breakdown">
                            <p class="rm-psychology-chart__total">{{ $chartInstrumentos['total'] }} {{ $chartInstrumentos['total'] === 1 ? 'aplicación registrada' : 'aplicaciones registradas' }}</p>
                            <ul aria-label="Aplicaciones por instrumento">
                                @foreach($chartInstrumentos['labels'] as $indice => $instrumento)
                                    <li><span class="rm-psychology-chart__swatch rm-psychology-chart__swatch--{{ $indice + 1 }}" aria-hidden="true"></span><span>{{ $instrumento }}</span><strong>{{ $chartInstrumentos['data'][$indice] }}</strong></li>
                                @endforeach
                            </ul>
                        </div>
                        </div>
                    @else
                        <x-ui.empty-state compact icono="ph-chart-donut" titulo="Aún no hay aplicaciones completas o vigentes" texto="Cuando se registren, aquí verás qué instrumentos se han utilizado." />
                    @endif
                    <details class="rm-psychology-chart__data">
                        <summary>Ver cantidades por instrumento</summary>
                        <table><caption class="sr-only">Aplicaciones completas o vigentes por instrumento</caption>
                            <thead><tr><th scope="col">Instrumento</th><th scope="col">Aplicaciones</th></tr></thead>
                            <tbody>@foreach($chartInstrumentos['labels'] as $indice => $instrumento)
                                <tr><th scope="row">{{ $instrumento }}</th><td>{{ $chartInstrumentos['data'][$indice] }}</td></tr>
                            @endforeach</tbody>
                        </table>
                    </details>
                </article>
            </div>
        </section>

        <section aria-labelledby="psychology-followup-title">
            <div class="rm-psychology-dashboard__section-heading">
                <h2 id="psychology-followup-title">Pendientes y registros recientes</h2>
            </div>
<<<<<<< HEAD
            <div class="rm-psychology-dashboard__followup">
                <div class="rm-psychology-dashboard__pending">
                    @foreach($panels as $panel)
                        <x-ui.dashboard-data-panel :panel="$panel" />
                    @endforeach
                </div>
=======
            <div class="rm-dashboard-data-grid rm-psychology-dashboard__panels">
                @foreach($panels as $panel)
                    <x-ui.dashboard-data-panel :panel="$panel" />
                @endforeach
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                <section class="rm-dashboard-data-panel rm-psychology-recent" aria-labelledby="psychology-recent-title">
                    <div class="rm-dashboard-data-panel__heading">
                        <span class="rm-dashboard-data-panel__icon" aria-hidden="true"><i class="ph-bold ph-clipboard-text"></i></span>
                        <div class="rm-dashboard-data-panel__heading-copy"><h2 id="psychology-recent-title">Aplicaciones recientes</h2></div>
                    </div>
                    @if($recientes === [])
                        <x-ui.empty-state compact icono="ph-clipboard-text" titulo="Sin aplicaciones recientes" texto="Aún no hay aplicaciones completas o vigentes a tu nombre." />
                    @else
                        <ul class="rm-psychology-recent__list">
                            @foreach($recientes as $registro)
                                <li>
                                    <div><strong>{{ $registro['residente'] }}</strong><span>{{ $registro['instrumento'] }}</span></div>
                                    <time datetime="{{ $registro['fecha_iso'] }}">{{ $registro['fecha'] }}</time>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
<<<<<<< HEAD
                <section class="rm-psychology-areas" aria-labelledby="psychology-areas-title">
                    <h2 id="psychology-areas-title">Áreas de valoración</h2>
                    <div class="rm-psychology-areas__links rm-psychology-areas__links--four">
                        @foreach([
                            ['route' => 'admin.psicologia.evaluacion.cognitiva', 'label' => 'Cognitiva', 'icon' => 'ph-brain'],
                            ['route' => 'admin.psicologia.evaluacion.afectiva', 'label' => 'Afectiva y emocional', 'icon' => 'ph-heart'],
                            ['route' => 'admin.psicologia.evaluacion.funcionamiento', 'label' => 'Funcionamiento', 'icon' => 'ph-person-simple-walk'],
                            ['route' => 'admin.psicologia.evaluacion.entorno', 'label' => 'Entorno y red social', 'icon' => 'ph-users-four'],
                        ] as $area)
                            <a href="{{ route($area['route']) }}" wire:navigate><i class="ph-bold {{ $area['icon'] }}" aria-hidden="true"></i><span>{{ $area['label'] }}</span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
                        @endforeach
                    </div>
                </section>
=======
            </div>
        </section>

        <section class="rm-psychology-areas" aria-labelledby="psychology-areas-title">
            <h2 id="psychology-areas-title">Áreas de valoración</h2>
            <div class="rm-psychology-areas__links rm-psychology-areas__links--four">
                @foreach([
                    ['route' => 'admin.psicologia.evaluacion.cognitiva', 'label' => 'Cognitiva', 'icon' => 'ph-brain'],
                    ['route' => 'admin.psicologia.evaluacion.afectiva', 'label' => 'Afectiva y emocional', 'icon' => 'ph-heart'],
                    ['route' => 'admin.psicologia.evaluacion.funcionamiento', 'label' => 'Funcionamiento', 'icon' => 'ph-person-simple-walk'],
                    ['route' => 'admin.psicologia.evaluacion.entorno', 'label' => 'Entorno y red social', 'icon' => 'ph-users-four'],
                ] as $area)
                    <a href="{{ route($area['route']) }}" wire:navigate><i class="ph-bold {{ $area['icon'] }}" aria-hidden="true"></i><span>{{ $area['label'] }}</span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
                @endforeach
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            </div>
        </section>

    @endif
</div>
