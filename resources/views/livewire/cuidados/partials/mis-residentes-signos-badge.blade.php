@php
    $etiquetaBadge = match ($tonoBadge) {
        'danger' => 'Crítico',
        'high' => 'Alto',
        'warning' => 'Advertencia',
        'target' => 'En objetivo',
        'success' => 'Normal',
        default => $claveTarjeta === 'sat' ? 'Sin objetivo médico' : 'Registrada',
    };
    $iconoBadge = in_array($tonoBadge, ['success', 'target'], true) ? 'ph-check-circle' : ($tonoBadge === 'neutral' ? 'ph-info' : 'ph-warning-circle');
@endphp
@if($evaluacionesBadge->isNotEmpty())
    <span class="rm-signos__reading-badge" data-tone="{{ $tonoBadge }}" x-show="hasEntered(@js($claveTarjeta)) && evaluationCurrent(@js($claveTarjeta)) && !hasCardError(@js($claveTarjeta))" aria-live="polite">
        <i class="ph-bold {{ $iconoBadge }}" aria-hidden="true"></i> {{ $etiquetaBadge }}
    </span>
@endif
