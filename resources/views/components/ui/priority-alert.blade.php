@props(['alert', 'href' => null])
@php
    $priority = strtoupper(trim((string) ($alert['prioridad'] ?? '')));
    $tone = match ($priority) {
        'CRITICO', 'CRITICA' => 'critical',
        'ALTO', 'ALTA' => 'high',
        'MEDIO', 'MEDIA' => 'medium',
        default => 'low',
    };
    $label = match ($priority) {
        'CRITICO', 'CRITICA' => 'Crítica',
        'ALTO', 'ALTA' => 'Alta',
        'MEDIO', 'MEDIA' => 'Media',
        'BAJO', 'BAJA' => 'Baja',
        default => 'Sin clasificar',
    };
    $type = mb_strtolower((string) ($alert['tipo'] ?? ''));
    $icon = match (true) {
        str_contains($type, 'medic') => 'ph-pill',
        str_contains($type, 'caida'), str_contains($type, 'caída') => 'ph-warning',
        str_contains($type, 'conduct'), str_contains($type, 'cogn') => 'ph-brain',
        str_contains($type, 'hidrat') => 'ph-drop',
        default => 'ph-warning-circle',
    };
@endphp
@if($href)<a href="{{ $href }}" class="rm-priority-alert rm-priority-alert--{{ $tone }} rm-priority-alert--link">@else<div class="rm-priority-alert rm-priority-alert--{{ $tone }}">@endif
    <span class="rm-priority-alert__icon" aria-hidden="true"><i class="ph-bold {{ $icon }}"></i></span>
    <span class="rm-priority-alert__body">
        <span class="rm-priority-alert__top">
            <strong class="rm-priority-alert__name">{{ $alert['paciente'] ?? 'Residente no disponible' }}</strong>
            @if(!empty($alert['tiempo']))<time class="rm-priority-alert__time">{{ $alert['tiempo'] }}</time>@endif
        </span>
        <span class="rm-priority-alert__meta">
            @if(!empty($alert['habitacion']))<span>{{ $alert['habitacion'] }}</span>@endif
            <span class="rm-priority-alert__priority">{{ $label }}</span>
        </span>
        <span class="rm-priority-alert__description">{{ $alert['descripcion'] ?? 'Alerta sin descripción' }}</span>
    </span>
@if($href)</a>@else</div>@endif
