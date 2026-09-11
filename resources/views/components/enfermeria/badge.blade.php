@props([
    'tipo' => 'neutral', // positive, warning, risk, info, coral, neutral
    'dot' => true,
    'icon' => null,
])

@php
$badgeClass = match($tipo) {
    'positive', 'positivo', 'exito', 'activo', 'normal' => 'enf-badge-positive',
    'warning', 'advertencia', 'pendiente', 'observacion' => 'enf-badge-warning',
    'risk', 'riesgo', 'peligro', 'critico', 'urgente' => 'enf-badge-risk',
    'info', 'informacion' => 'enf-badge-info',
    'coral', 'destacado' => 'enf-badge-coral',
    default => 'enf-badge-neutral',
};
@endphp

<span {{ $attributes->merge(['class' => 'enf-badge ' . $badgeClass]) }}>
    @if($dot && !$icon)
        <span class="enf-badge-dot"></span>
    @endif
    @if($icon)
        <i class="ph-bold {{ $icon }} text-xs"></i>
    @endif
    <span>{{ $slot }}</span>
</span>
