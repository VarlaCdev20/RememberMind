@props(['metric', 'kind'])

@php
    $value = (int) ($metric['value'] ?? 0);
    $capacity = (int) ($metric['capacity'] ?? 0);
    $percentage = $capacity > 0 ? min(100, max(0, (int) round($value / $capacity * 100))) : null;
    $occupied = (int) ($metric['occupied'] ?? 0);
    $areas = $metric['areas'] ?? [];
    $areaMaximum = max([1, ...array_column($areas, 'value')]);
    $sparkbars = $metric['sparkbars'] ?? [];
    $sparkbarMaximum = max([1, ...$sparkbars]);
    $quiet = $kind === 'alerts' && $value === 0;
    $label = $metric['label'] ?? 'Indicador';
    $labelId = 'superadmin-kpi-'.\Illuminate\Support\Str::slug($label);
@endphp

<section {{ $attributes->class(['rm-superadmin-kpi', 'rm-superadmin-kpi--'.$kind, 'rm-superadmin-kpi--quiet' => $quiet]) }} aria-labelledby="{{ $labelId }}">
    <div class="rm-superadmin-kpi__top">
        <span class="rm-superadmin-kpi__icon" aria-hidden="true"><i class="ph-bold {{ $metric['icon'] ?? 'ph-chart-bar' }}"></i></span>
        @if($kind === 'staff' && $percentage !== null)
            <span class="rm-superadmin-kpi__radial" style="--rm-kpi-progress: {{ $percentage }}%;" role="img" aria-label="{{ $percentage }}% del personal activo asignado hoy">
                <span>{{ $percentage }}%</span>
            </span>
        @endif
    </div>

    <div class="rm-superadmin-kpi__main">
        <p class="rm-superadmin-kpi__value">
            {{ $value }}@if(in_array($kind, ['beds', 'staff'], true) && $capacity > 0)<small> de {{ $capacity }}</small>@endif
        </p>
        <h2 id="{{ $labelId }}" class="rm-superadmin-kpi__label">{{ $quiet ? 'Sin alertas prioritarias' : $label }}</h2>
        <p class="rm-superadmin-kpi__description">{{ $quiet ? 'No hay críticas o altas abiertas' : ($metric['description'] ?? '') }}</p>
    </div>

    @if($kind === 'beds' && $capacity > 0)
        <div class="rm-superadmin-kpi__detail">
            <div class="rm-superadmin-kpi__detail-row"><span>Ocupación actual</span><strong>{{ 100 - $percentage }}%</strong></div>
            <div class="rm-superadmin-kpi__track" role="progressbar" aria-label="Camas ocupadas" aria-valuemin="0" aria-valuemax="{{ $capacity }}" aria-valuenow="{{ $occupied }}"><span style="width: {{ 100 - $percentage }}%"></span></div>
            <p>{{ $occupied }} ocupadas · {{ $value }} disponibles</p>
        </div>
    @elseif($kind === 'staff' && $capacity > 0)
        <div class="rm-superadmin-kpi__detail">
            <p class="rm-superadmin-kpi__detail-title">Cobertura por área</p>
            @forelse(array_slice($areas, 0, 3) as $area)
                <div class="rm-superadmin-kpi__area">
                    <span title="{{ $area['label'] }}">{{ $area['label'] }}</span><strong>{{ $area['value'] }}</strong>
                    <span class="rm-superadmin-kpi__area-track" aria-hidden="true"><span style="width: {{ round($area['value'] / $areaMaximum * 100) }}%"></span></span>
                </div>
            @empty
                <p>Sin cobertura por área registrada.</p>
            @endforelse
        </div>
    @elseif(in_array($kind, ['residents', 'alerts'], true) && array_sum($sparkbars) > 0)
        <div class="rm-superadmin-kpi__detail">
            <p class="rm-superadmin-kpi__detail-title">{{ $metric['sparkbarLabel'] }}</p>
            <div class="rm-superadmin-kpi__sparkbars" role="img" aria-label="{{ $metric['sparkbarLabel'] }}: {{ implode(', ', $sparkbars) }} por día">
                @foreach($sparkbars as $bar)
                    <span style="height: {{ max(12, round($bar / $sparkbarMaximum * 100)) }}%" aria-hidden="true"></span>
                @endforeach
            </div>
        </div>
    @endif
</section>
