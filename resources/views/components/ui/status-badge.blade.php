@props(['estado' => '', 'variant' => null])
@php
    $valor = strtoupper((string) $estado);
    $variant ??= match ($valor) {
        'ACTIVO', 'ACTIVA', 'ADMITIDO', 'APROBADA', 'VIGENTE', 'DISPONIBLE' => 'success',
        'PENDIENTE', 'EN_REVISION' => 'warning',
        'RECONOCIDA', 'ASIGNADA', 'ATENDIDA', 'TRASLADADO' => 'info',
        default => 'neutral',
    };
    $variant = in_array($variant, ['success', 'warning', 'danger', 'info', 'neutral'], true) ? $variant : 'neutral';
@endphp
<span {{ $attributes->class(['rm-badge', 'rm-badge-'.$variant]) }}><span class="rm-badge-dot" aria-hidden="true"></span>{{ $valor ?: 'Sin estado' }}</span>
