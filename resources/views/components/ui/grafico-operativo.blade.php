@props(['tipo' => 'barras', 'datos' => [], 'etiqueta' => 'Distribución', 'unidad' => 'registros'])
@php
    $series = collect($datos)->values();
    $total = (int) $series->sum('cantidad');
    $maximo = max(1, (int) $series->max('cantidad'));
    $colores = ['--rm-chart-care-500', '--rm-chart-clinical-500', '--rm-chart-cognitive-500', '--rm-chart-neutral-500', '--rm-chart-rehab-500', '--rm-chart-reference-500'];
@endphp
<div class="rm-data-graphic is-{{ $tipo }}" data-grafico="{{ $tipo }}" aria-label="{{ $etiqueta }}" x-data="{ activo: null }">
    @if($series->isEmpty() || ($total === 0 && $tipo !== 'comparacion'))
        <div class="rm-data-empty"><i class="ph-bold ph-chart-scatter" aria-hidden="true"></i><strong>Sin datos en este contexto</strong><p>Ajusta los filtros para explorar {{ $unidad }} registrados.</p></div>
    @elseif($tipo === 'comparacion')
        <div class="rm-data-comparison">@foreach($series as $i=>$serie)
            @php $escala = max(1, (int)$series->max('cantidad'), (int)$series->max('cupo')); @endphp
            <a href="{{ $serie['url'] }}" @click.prevent="abrirFicha(@js($serie['url']), @js($serie['ficha']), $event)" data-registro="{{ $serie['ficha'] }}"><header><div><strong>{{ $serie['etiqueta'] }}</strong>@if(filled($serie['subetiqueta'] ?? null))<small class="block">{{ $serie['subetiqueta'] }}</small>@endif</div><i class="ph-bold ph-eye" aria-hidden="true"></i></header><div><span>{{ $serie['medida'] ?? 'Participantes registrados' }}</span><b>{{ $serie['cantidad'] }}</b><span class="rm-data-track" aria-hidden="true"><span style="--rm-data-size:{{ $serie['cantidad'] / $escala * 100 }}%;--rm-data-color:var(--rm-chart-clinical-500)"></span></span></div>
            @if(array_key_exists('cupo',$serie))<div><span>Cupo registrado</span><b>{{ $serie['cupo'] === null ? 'Sin registrar' : $serie['cupo'] }}</b>@if($serie['cupo'] !== null)<span class="rm-data-track" aria-hidden="true"><span style="--rm-data-size:{{ $serie['cupo'] / $escala * 100 }}%;--rm-data-color:var(--rm-chart-neutral-500)"></span></span>@endif</div>@endif</a>
        @endforeach</div>
    @elseif($tipo === 'anillo')
        <div class="rm-data-ring">
            <svg viewBox="0 0 160 160" aria-hidden="true"><circle class="rm-data-ring__base" cx="80" cy="80" r="58" />
                @php $acumulado = 0; $circunferencia = 2 * M_PI * 58; @endphp
                @foreach($series as $i => $serie)
                    <circle class="rm-data-ring__segment" :class="{ 'is-muted': activo !== null && activo !== {{ $i }} }" cx="80" cy="80" r="58" stroke="var({{ $serie['color'] ?? $colores[$i % count($colores)] }})" stroke-dasharray="{{ $serie['cantidad'] / $total * $circunferencia }} {{ $circunferencia }}" stroke-dashoffset="{{ -$acumulado / $total * $circunferencia }}" />
                    @php $acumulado += $serie['cantidad']; @endphp
                @endforeach
            </svg>
            <div><strong x-text="activo === null ? {{ $total }} : @js($series->pluck('cantidad')->all())[activo]">{{ $total }}</strong><span x-text="activo === null ? @js($unidad.' en los grupos mostrados') : @js($series->pluck('etiqueta')->all())[activo]">{{ $unidad }} en los grupos mostrados</span></div>
        </div>
        <ul class="rm-data-legend">@foreach($series as $i => $serie)<li><a @if(isset($serie['indicador'])) @click.prevent="abrirIndicador(@js($serie['indicador']), $event)" @elseif(isset($serie['ficha'])) @click.prevent="abrirFicha(@js($serie['url']), @js($serie['ficha']), $event)" @else wire:navigate @endif href="{{ $serie['url'] }}" @if($serie['seleccionado'] ?? false) aria-current="true" @endif @mouseenter="activo = {{ $i }}" @mouseleave="activo = null" @focusin="activo = {{ $i }}" @focusout="activo = null" style="--rm-data-color: var({{ $serie['color'] ?? $colores[$i % count($colores)] }})"><span class="rm-data-dot" aria-hidden="true"></span><span>{{ $serie['etiqueta'] }}</span><strong>{{ $serie['cantidad'] }}</strong><small>{{ round($serie['cantidad'] / $total * 100, 1) }}%</small></a></li>@endforeach</ul>
    @elseif($tipo === 'franja')
        <div class="rm-data-strip" aria-hidden="true">@foreach($series as $i => $serie)<span style="flex: {{ $serie['cantidad'] }}; background: var({{ $serie['color'] ?? $colores[$i % count($colores)] }})"></span>@endforeach</div>
        <ul class="rm-data-legend">@foreach($series as $i => $serie)<li><a @if(isset($serie['indicador'])) @click.prevent="abrirIndicador(@js($serie['indicador']), $event)" @elseif(isset($serie['ficha'])) @click.prevent="abrirFicha(@js($serie['url']), @js($serie['ficha']), $event)" @else wire:navigate @endif href="{{ $serie['url'] }}" @if($serie['seleccionado'] ?? false) aria-current="true" @endif style="--rm-data-color: var({{ $serie['color'] ?? $colores[$i % count($colores)] }})"><span class="rm-data-dot" aria-hidden="true"></span><span>{{ $serie['etiqueta'] }}</span><strong>{{ $serie['cantidad'] }}</strong><small>{{ round($serie['cantidad'] / $total * 100, 1) }}%</small></a></li>@endforeach</ul>
    @elseif($series->count() === 1 && in_array($tipo, ['columnas', 'puntos'], true))
        @php $serie = $series->first(); @endphp
        <a wire:navigate class="rm-data-single" href="{{ $serie['url'] }}" @if($serie['seleccionado'] ?? false) aria-current="true" @endif><span><i class="ph-bold {{ $tipo === 'puntos' ? 'ph-calendar-dots' : 'ph-chart-bar' }}" aria-hidden="true"></i></span><div><small>{{ $serie['etiqueta'] }}</small><strong>{{ $serie['cantidad'] }} <span>{{ $unidad }}</span></strong><p>{{ $tipo === 'puntos' ? 'Un mes con datos en este contexto; abre sus registros.' : 'Un único grupo en este contexto; abre sus registros.' }}</p></div><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
    @elseif(in_array($tipo, ['columnas', 'puntos'], true))
        <div class="rm-data-columns" style="--rm-data-count: {{ $series->count() }}">
            @foreach($series as $i => $serie)<a @if(isset($serie['indicador'])) @click.prevent="abrirIndicador(@js($serie['indicador']), $event)" @elseif(isset($serie['ficha'])) @click.prevent="abrirFicha(@js($serie['url']), @js($serie['ficha']), $event)" @else wire:navigate @endif href="{{ $serie['url'] }}" @if($serie['seleccionado'] ?? false) aria-current="true" @endif style="--rm-data-size: {{ $serie['cantidad'] / $maximo * 100 }}%; --rm-data-color: var({{ $serie['color'] ?? $colores[$i % count($colores)] }})" aria-label="{{ $serie['etiqueta'] }}: {{ $serie['cantidad'] }} {{ $unidad }}. Filtrar">
                <span class="rm-data-column"><strong>{{ $serie['cantidad'] }}</strong><span></span></span><small>{{ $serie['etiqueta'] }}</small>
            </a>@endforeach
        </div>
        <p class="rm-control-help">Escala: 0–{{ $maximo }} {{ $unidad }}. Selecciona {{ $tipo === 'puntos' ? 'un punto' : 'una columna' }} para consultar.</p>
    @elseif($tipo === 'horarios' || $tipo === 'estaciones' || $tipo === 'mosaico')
        <div class="rm-data-stations">@foreach($series as $i => $serie)<a @if(isset($serie['indicador'])) @click.prevent="abrirIndicador(@js($serie['indicador']), $event)" @elseif(isset($serie['ficha'])) @click.prevent="abrirFicha(@js($serie['url']), @js($serie['ficha']), $event)" @else wire:navigate @endif href="{{ $serie['url'] }}" @if($serie['seleccionado'] ?? false) aria-current="true" @endif style="--rm-data-color: var({{ $serie['color'] ?? $colores[$i % count($colores)] }})"><span class="rm-data-station-icon"><i class="ph-bold {{ $tipo === 'horarios' ? 'ph-clock' : ($serie['icono'] ?? 'ph-door-open') }}" aria-hidden="true"></i></span><div><span>{{ $serie['etiqueta'] }}</span><strong>{{ $serie['cantidad'] }}</strong><small>{{ $unidad }}</small></div><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>@endforeach</div>
    @else
        <div class="rm-data-ranks">@foreach($series as $i => $serie)<a @if(isset($serie['indicador'])) @click.prevent="abrirIndicador(@js($serie['indicador']), $event)" @elseif(isset($serie['ficha'])) @click.prevent="abrirFicha(@js($serie['url']), @js($serie['ficha']), $event)" @else wire:navigate @endif href="{{ $serie['url'] }}" @if($serie['seleccionado'] ?? false) aria-current="true" @endif style="--rm-data-size: {{ $serie['cantidad'] / $maximo * 100 }}%; --rm-data-color: var({{ $serie['color'] ?? $colores[$i % count($colores)] }})"><span class="rm-data-rank-number">{{ $i + 1 }}</span><div><span>{{ $serie['etiqueta'] }}</span><span class="rm-data-track" aria-hidden="true"><span></span></span></div><strong>{{ $serie['cantidad'] }}</strong></a>@endforeach</div>
    @endif
</div>
