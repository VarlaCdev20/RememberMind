@php
    $categoriasGrafico = [
        'alterado' => 'Alteración sustentada',
        'esperado' => 'Rendimiento esperado sustentado',
        'insuficiente' => 'Información insuficiente',
        'no_interpretable' => 'No interpretable',
        'contradictorio' => 'Información contradictoria',
        'disponible' => 'Resultado publicado',
        'sin_resultado' => 'Sin resultado publicado',
    ];
    $conteosGrafico = array_fill_keys(array_keys($categoriasGrafico), 0);
    foreach ($areasGrafico as $areaGrafico) {
        $conteosGrafico[$areaGrafico['grupo']]++;
    }
    $totalGrafico = count($areasGrafico);
    $resumenGrafico = implode(' ', array_map(
        fn ($grupo, $cantidad) => $cantidad ? $cantidad.' '.$categoriasGrafico[$grupo].'.' : '',
        array_keys($conteosGrafico),
        $conteosGrafico,
    ));
    $modoRadial = ($modoGrafico ?? 'barras') === 'radial' && $totalGrafico === 5;
    $nodosRadiales = [[180, 52], [302, 140], [255, 284], [105, 284], [58, 140]];
@endphp

<section class="rm-cognitive-status-chart {{ $modoRadial ? 'rm-cognitive-status-chart--radial' : '' }}" aria-labelledby="{{ $graficoId }}-title" @if($modoRadial) x-data="{ seleccion: 0 }" @endif>
    <div class="rm-cognitive-status-chart__heading">
        <div>
            <h3 id="{{ $graficoId }}-title">{{ $graficoTitulo }}</h3>
            <p>{{ $graficoSubtitulo }}</p>
        </div>
        <span class="rm-cognitive-status-chart__total">{{ $totalGrafico }} áreas</span>
    </div>
    @if($totalGrafico)
        @if($modoRadial)
            <div class="rm-cognitive-status-chart__layout">
                <div class="rm-cognitive-status-chart__plot">
                    <svg viewBox="0 0 360 360" role="group" aria-label="Mapa radial interactivo de cinco áreas. Seleccione un punto para consultar su estado. {{ $resumenGrafico }}">
                        <polygon class="rm-cognitive-status-chart__frame" points="180,52 302,140 255,284 105,284 58,140" />
                        @foreach($nodosRadiales as $indice => [$x, $y])
                            <line class="rm-cognitive-status-chart__spoke" x1="180" y1="180" x2="{{ $x }}" y2="{{ $y }}" x-bind:class="{ 'is-selected': seleccion === {{ $indice }} }" />
                        @endforeach
                        <circle class="rm-cognitive-status-chart__center" cx="180" cy="180" r="33" />
                        <text class="rm-cognitive-status-chart__center-label" x="180" y="176" text-anchor="middle">5</text>
                        <text class="rm-cognitive-status-chart__center-caption" x="180" y="194" text-anchor="middle">áreas</text>
                        @foreach($areasGrafico as $indice => $areaGrafico)
                            <g class="rm-cognitive-status-chart__point" role="button" tabindex="0" aria-label="{{ $areaGrafico['nombre'] }}: {{ $areaGrafico['estado'] }}" aria-controls="{{ $graficoId }}-detalle" x-bind:aria-pressed="seleccion === {{ $indice }}" x-bind:class="{ 'is-selected': seleccion === {{ $indice }} }" x-on:click="seleccion = {{ $indice }}" x-on:focus="seleccion = {{ $indice }}" x-on:keydown.enter.prevent="seleccion = {{ $indice }}" x-on:keydown.space.prevent="seleccion = {{ $indice }}">
                                <circle class="rm-cognitive-status-chart__node" data-status="{{ $areaGrafico['grupo'] }}" cx="{{ $nodosRadiales[$indice][0] }}" cy="{{ $nodosRadiales[$indice][1] }}" r="19" />
                                <text class="rm-cognitive-status-chart__node-label" x="{{ $nodosRadiales[$indice][0] }}" y="{{ $nodosRadiales[$indice][1] + 1 }}" text-anchor="middle" dominant-baseline="middle">{{ $indice + 1 }}</text>
                                <title>{{ $areaGrafico['nombre'] }}: {{ $areaGrafico['estado'] }}</title>
                            </g>
                        @endforeach
                    </svg>
                    <p>Cada punto representa un área. La distancia al centro es igual para todas.</p>
                </div>
                <ol class="rm-cognitive-status-chart__rows" aria-label="Estado de cada área cognitiva">
                    @foreach($areasGrafico as $indice => $areaGrafico)
                        <li class="rm-cognitive-status-chart__row">
                            <button type="button" class="rm-cognitive-status-chart__choice" aria-controls="{{ $graficoId }}-detalle" x-bind:aria-pressed="seleccion === {{ $indice }}" x-on:click="seleccion = {{ $indice }}">
                                <span class="rm-cognitive-status-chart__index" aria-hidden="true">{{ $indice + 1 }}</span>
                                <span class="rm-cognitive-status-chart__choice-text"><strong>{{ $areaGrafico['nombre'] }}</strong><span class="rm-cognitive-status-chart__badge" data-status="{{ $areaGrafico['grupo'] }}">{{ $areaGrafico['estado'] }}</span></span>
                            </button>
                        </li>
                    @endforeach
                </ol>
            </div>
            <div id="{{ $graficoId }}-detalle" class="rm-cognitive-status-chart__selection" aria-live="polite">
                <p class="rm-cognitive-status-chart__selection-label">Área seleccionada</p>
                @foreach($areasGrafico as $indice => $areaGrafico)
                    <div x-show="seleccion === {{ $indice }}" @if($indice !== 0) x-cloak @endif>
                        <strong>{{ $areaGrafico['nombre'] }}</strong>
                        <span class="rm-cognitive-status-chart__badge" data-status="{{ $areaGrafico['grupo'] }}">{{ $areaGrafico['estado'] }}</span>
                    </div>
                @endforeach
            </div>
        @else
        <div class="rm-cognitive-status-chart__bar" role="img" aria-label="Distribución de las {{ $totalGrafico }} áreas: {{ $resumenGrafico }}">
            @foreach($conteosGrafico as $grupo => $cantidad)
                @if($cantidad)
                    <span data-status="{{ $grupo }}" style="width: {{ $cantidad * 100 / $totalGrafico }}%"></span>
                @endif
            @endforeach
        </div>
        <ul class="rm-cognitive-status-chart__legend" aria-label="Valores de la gráfica">
            @foreach($conteosGrafico as $grupo => $cantidad)
                @if($cantidad)
                    <li><span class="rm-cognitive-status-chart__key" data-status="{{ $grupo }}" aria-hidden="true"></span><strong>{{ $cantidad }}</strong> {{ $categoriasGrafico[$grupo] }}</li>
                @endif
            @endforeach
        </ul>
        <div class="rm-cognitive-status-chart__rows" aria-label="Estado de cada área">
            @foreach($areasGrafico as $areaGrafico)
                <div class="rm-cognitive-status-chart__row"><strong>{{ $areaGrafico['nombre'] }}</strong><span class="rm-cognitive-status-chart__badge" data-status="{{ $areaGrafico['grupo'] }}">{{ $areaGrafico['estado'] }}</span></div>
            @endforeach
        </div>
        @endif
    @else
        <p class="rm-orion-empty">No hay áreas disponibles para representar.</p>
    @endif
    <p class="rm-cognitive-status-chart__note">{{ $modoRadial ? 'Este mapa muestra estados cualitativos; no representa puntuaciones ni magnitud de deterioro.' : 'La gráfica cuenta las cinco áreas del informe según su estado disponible; no representa puntuaciones ni magnitud de deterioro.' }}</p>
</section>
