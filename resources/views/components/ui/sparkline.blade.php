@props([
    'id' => null,
    'data' => [],
    'labels' => [],
    'color' => null,
    'height' => '44px',
    'class' => '',
    'showPoints' => false,
])

@php
    $canvasId = $id ?? ('rm-sparkline-' . \Illuminate\Support\Str::random(8));

    $colorMap = [
        'danger' => '#F43F5E',
        'warning' => '#F59E0B',
        'success' => '#10B981',
        'info' => '#0EA5E9',
        'primary' => '#344D7A',
        'terracota' => '#D9745B',
        'sage' => '#5F9271',
        'purple' => '#8B5CF6',
    ];
    $resolvedColor = $colorMap[$color] ?? $color;
@endphp

<div wire:ignore
     x-data="{
        chart: null,
        dataValues: @js($data),
        labels: @js($labels),
        color: @js($resolvedColor),
        init() {
            const render = () => {
                if (typeof Chart === 'undefined' || typeof window.RMCharts === 'undefined' || !window.RMCharts.presets) {
                    setTimeout(render, 60);
                    return;
                }
                const canvas = document.getElementById('{{ $canvasId }}');
                if (!canvas) return;

                const cfg = window.RMCharts.presets.sparkline(
                    this.labels,
                    this.dataValues,
                    this.color,
                    {
                        _showPoints: @js($showPoints),
                    }
                );

                this.chart = window.RMCharts.init('{{ $canvasId }}', canvas, cfg, () => render());
            };
            this.$nextTick(render);

            window.RMCharts?.onThemeChange(() => {
                render();
            });
        }
     }"
     class="rm-sparkline {{ $class }}"
     style="height: {{ $height }};">
    <canvas id="{{ $canvasId }}"></canvas>
</div>
