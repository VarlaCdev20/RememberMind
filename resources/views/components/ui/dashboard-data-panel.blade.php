@props(['panel'])

@php
    $items = $panel['items'] ?? [];
    $type = $panel['type'] ?? 'list';
    $span = ($panel['span'] ?? 'normal') === 'wide' ? 'wide' : 'normal';
    $isChart = in_array($type, ['bars', 'segments', 'donut'], true);
    $total = array_sum(array_map(fn ($item) => max(0, (int) ($item['value'] ?? 0)), $items));
    $maximum = max(array_merge([1], array_map(fn ($item) => max(0, (int) ($item['value'] ?? 0)), $items)));
    $cursor = 0;
    $stops = [];
    foreach ($items as $index => $item) {
        $next = $total > 0 ? $cursor + max(0, (int) ($item['value'] ?? 0)) * 100 / $total : $cursor;
        $token = '--rm-chart-'.(($index % 6) + 1);
        $stops[] = "var({$token}) {$cursor}% {$next}%";
        $cursor = $next;
    }
    $gradient = implode(', ', $stops);
@endphp

<section @class(['rm-dashboard-data-panel', 'rm-dashboard-data-panel--'.$span, 'rm-dashboard-data-panel--chart' => $isChart, 'rm-dashboard-data-panel--incident' => ($panel['icon'] ?? '') === 'ph-warning']) aria-label="{{ $panel['title'] }}">
    <div class="rm-dashboard-data-panel__heading">
        <span class="rm-dashboard-data-panel__icon" aria-hidden="true"><i class="ph-bold {{ $panel['icon'] ?? 'ph-chart-bar' }}"></i></span>
        <div class="rm-dashboard-data-panel__heading-copy">
            <h2>{{ $panel['title'] }}</h2>
            @if($isChart && !empty($panel['subtitle']))<p>{{ $panel['subtitle'] }}</p>@endif
        </div>
        @if($isChart && !empty($panel['period']))<span class="rm-dashboard-data-panel__period">{{ $panel['period'] }}</span>@endif
    </div>

    @if(!$items || (in_array($type, ['bars', 'segments', 'donut'], true) && $total === 0))
        <x-ui.empty-state compact :icono="$panel['icon'] ?? 'ph-chart-bar'" titulo="Sin datos por mostrar" :texto="$panel['empty'] ?? 'Aún no hay registros para este bloque.'" />
    @elseif(in_array($type, ['list', 'timeline'], true))
        <ul class="rm-dashboard-data-panel__list {{ $type === 'timeline' ? 'rm-dashboard-data-panel__list--timeline' : '' }}">
            @foreach($items as $item)
                <li>
                    <span class="rm-dashboard-data-panel__row-copy"><strong>{{ $item['label'] }}</strong>@if(!empty($item['detail']))<small>{{ $item['detail'] }}</small>@endif</span>
                    @if(isset($item['value']))<b>{{ $item['value'] }}</b>@endif
                </li>
            @endforeach
        </ul>
    @elseif($type === 'bars')
        <ul class="rm-dashboard-data-panel__bars">
            @foreach($items as $item)
                <li><span><strong>{{ $item['label'] }}</strong><b>{{ $item['value'] }}</b></span><div aria-hidden="true"><i style="width: {{ round(max(0, (int) $item['value']) * 100 / $maximum) }}%"></i></div></li>
            @endforeach
        </ul>
    @elseif($type === 'segments')
        <div class="rm-dashboard-data-panel__segments" role="img" aria-label="{{ collect($items)->map(fn ($item) => $item['label'].': '.$item['value'])->implode(', ') }}">
            @foreach($items as $item)<span style="width: {{ round(max(0, (int) $item['value']) * 100 / $total, 3) }}%"></span>@endforeach
        </div>
        <ul class="rm-dashboard-data-panel__legend">
            @foreach($items as $item)<li><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></li>@endforeach
        </ul>
    @elseif($type === 'donut')
        <div class="rm-dashboard-data-panel__donut-layout">
            <div class="rm-dashboard-data-panel__donut" style="background: conic-gradient({{ $gradient }})" role="img" aria-label="{{ collect($items)->map(fn ($item) => $item['label'].': '.$item['value'])->implode(', ') }}"><span><strong>{{ $total }}</strong><small>total</small></span></div>
            <ul class="rm-dashboard-data-panel__legend">
                @foreach($items as $item)<li><span>{{ $item['label'] }}</span><strong>{{ $item['value'] }}</strong></li>@endforeach
            </ul>
        </div>
    @endif
</section>
