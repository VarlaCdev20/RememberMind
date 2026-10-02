@props(['label', 'current' => null, 'previous' => null, 'unit' => '', 'recordedAt' => null])
<div class="rm-vital-stat">
    <span class="rm-context-label">{{ $label }}</span>
    <strong>{{ $current ?? '—' }}@if($current !== null && $unit)<span class="rm-vital-unit"> {{ $unit }}</span>@endif</strong>
    @if($recordedAt)<small>Último registro: {{ $recordedAt->format('d/m/Y H:i') }}</small>@endif
    @if($previous !== null)<small>Anterior: {{ $previous }}@if($unit) {{ $unit }}@endif</small>@endif
</div>
