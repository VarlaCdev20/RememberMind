@props([
    'tipo' => 'info', // positive, warning, risk, info
    'titulo' => null,
    'icono' => null,
])

@php
$alertClass = match($tipo) {
    'positive', 'exito' => 'enf-alert-positive',
    'warning', 'advertencia' => 'enf-alert-warning',
    'risk', 'peligro', 'critico' => 'enf-alert-risk',
    default => 'enf-alert-info',
};

$defaultIcon = match($tipo) {
    'positive', 'exito' => 'ph-check-circle',
    'warning', 'advertencia' => 'ph-warning',
    'risk', 'peligro', 'critico' => 'ph-warning-octagon',
    default => 'ph-info',
};
$iconName = $icono ?? $defaultIcon;
@endphp

<div {{ $attributes->merge(['class' => 'enf-alert ' . $alertClass]) }} role="alert">
    <div class="shrink-0 mt-0.5">
        <i class="ph-bold {{ $iconName }} text-base"></i>
    </div>
    <div class="space-y-0.5 flex-1">
        @if($titulo)
            <h4 class="font-bold text-xs uppercase tracking-wide">{{ $titulo }}</h4>
        @endif
        <div class="text-xs leading-relaxed">
            {{ $slot }}
        </div>
    </div>
</div>
