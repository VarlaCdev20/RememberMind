@props([
    'icon' => null,
    'icono' => null,
    'value' => null,
    'valor' => null,
    'label' => null,
    'etiqueta' => null,
    'description' => null,
    'variant' => 'neutral',
    'trend' => null,
    'progress' => null,
    'href' => null,
    'loading' => false,
    'colorValor' => null,
])

@php
    $resolvedIcon = $icon ?? $icono ?? 'ph-chart-bar';
    $resolvedValue = $value ?? $valor;
    $resolvedLabel = trim((string) ($label ?? $etiqueta ?? 'Indicador'));
    $tone = in_array($variant, ['mint', 'sky', 'critical', 'coral', 'neutral'], true) ? $variant : 'neutral';
    $hasValue = $resolvedValue !== null && $resolvedValue !== '';
    $hasProgress = is_numeric($progress) && $hasValue;
    $progressValue = $hasProgress ? min(100, max(0, (float) $progress)) : null;
    $labelId = 'metric-'.\Illuminate\Support\Str::slug($resolvedLabel).'-'.substr(md5($resolvedLabel.$resolvedIcon), 0, 8);
    $classes = ['rm-card rm-surface--default rm-metric-card rm-metric-card--'.$tone, 'rm-card--interactive' => (bool) $href];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }} aria-labelledby="{{ $labelId }}">
@else
    <section {{ $attributes->class($classes) }} aria-labelledby="{{ $labelId }}">
@endif
    <div class="rm-metric-card__top">
        <span class="rm-metric-card__icon" aria-hidden="true"><i class="ph-bold {{ $resolvedIcon }}"></i></span>
        @if($trend !== null)
            <span class="rm-metric-card__trend">{{ $trend }}</span>
        @endif
    </div>
    <div class="rm-metric-card__body" @if($loading) aria-busy="true" @endif>
        @if($loading)
            <x-ui.skeleton class="rm-metric-card__skeleton" label="Cargando {{ $resolvedLabel }}" />
        @else
            <strong @class(['rm-metric rm-metric-card__value', $colorValor])>{{ $hasValue ? $resolvedValue : '—' }}</strong>
            <h2 id="{{ $labelId }}" class="rm-card-title rm-metric-card__label">{{ $resolvedLabel }}</h2>
            <p class="rm-body rm-metric-card__description">{{ $description ?? ($hasValue ? '' : 'Sin datos disponibles') }}</p>
            @if($hasProgress)
                <div class="rm-metric-card__progress" role="progressbar" aria-label="{{ $resolvedLabel }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progressValue) }}">
                    <span style="width: {{ $progressValue }}%"></span>
                </div>
            @endif
        @endif
    </div>
@if($href)
    </a>
@else
    </section>
@endif
