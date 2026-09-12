@props([
    'id',
    'titulo' => 'Gráfico',
    'subtitulo' => '',
    'icono' => 'ph-chart-line-up',
    'color' => 'terracota',
])
@php
    $colores = [
        'terracota' => 'bg-estado-advertencia-bg text-boton-acento',
        'azul' => 'bg-fondo-hover text-titulo',
        'verde' => 'bg-estado-exito-bg text-estado-exito-texto',
        'marron' => 'bg-modulo-voluntariosFondo text-modulo-voluntariosTexto',
    ];

    $estilo = $colores[$color] ?? $colores['terracota'];
@endphp

<div class="rm-chart-card rm-chart-glass group h-full">
    <div class="rm-chart-header">
        <div>
            <h3 class="rm-chart-title">
                {{ $titulo }}
            </h3>
            @if($subtitulo)
            <p class="rm-chart-subtitle">
                {{ $subtitulo }}
            </p>
            @endif
        </div>
        <div class="flex h-9 w-9 items-center justify-center rounded-xl shadow-xs transition-all duration-300 group-hover:rotate-6 group-hover:scale-105 {{ $estilo }}">
            <i class="ph-bold {{ $icono }} text-lg"></i>
        </div>
    </div>

    <div class="rm-chart-body is-sm">
        <canvas id="{{ $id }}"></canvas>
    </div>
</div>
