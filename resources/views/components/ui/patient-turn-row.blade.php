@props(['patient', 'href' => null])
@php
    $room = $patient['ubicacion'] ?? null;
    $room = $room && str_starts_with($room, 'Hab.') ? $room : null;
    $age = $patient['edad'] ?? null;
    $age = $age && preg_match('/^\d+ años$/u', $age) ? $age : null;
    $risk = $patient['riesgo'] ?? null;
    $riskVariant = match (mb_strtoupper((string) $risk)) {
        'ALTO', 'ALTA', 'CRÍTICO', 'CRITICO' => 'critical',
        'BAJO', 'BAJA' => 'success',
        default => 'neutral',
    };
@endphp
@if($href)<a href="{{ $href }}" class="rm-patient-turn-row rm-patient-turn-row--link">@else<div class="rm-patient-turn-row">@endif
    <span class="rm-patient-turn-row__identity">
        <span class="rm-patient-turn-row__avatar" aria-hidden="true">
            @if(!empty($patient['foto']))<img src="{{ asset($patient['foto']) }}" alt="" loading="lazy" decoding="async">@else{{ $patient['iniciales'] ?? 'RE' }}@endif
        </span>
        <span class="rm-patient-turn-row__person">
            <strong>{{ $patient['nombre_completo'] ?? 'Residente sin nombre' }}</strong>
            @if($age)<small>{{ $age }}</small>@endif
            @if($room)<small class="rm-patient-turn-row__room-inline">{{ $room }}</small>@endif
        </span>
    </span>
    <span class="rm-patient-turn-row__room"><span class="rm-patient-turn-row__mobile-label">Ubicación: </span>{{ $room ?? 'Sin asignar' }}</span>
    <span class="rm-patient-turn-row__cognition"><span class="rm-patient-turn-row__mobile-label">Cognición: </span>@if(!empty($patient['estado_cognitivo']) && $patient['estado_cognitivo'] !== 'Sin evaluación'){{ $patient['estado_cognitivo'] }}@else<span class="rm-patient-turn-row__missing">Sin evaluación</span>@endif</span>
    <span class="rm-patient-turn-row__risk"><span class="rm-patient-turn-row__mobile-label">Riesgo: </span>@if($risk)<x-ui.status-badge :label="$risk" :variant="$riskVariant" />@else<span class="rm-patient-turn-row__missing">Sin clasificar</span>@endif</span>
    <span class="rm-patient-turn-row__next"><span class="rm-patient-turn-row__mobile-label">Próxima atención: </span>@if(!empty($patient['proxima_atencion']))<strong>{{ $patient['proxima_atencion']['hora'] }}</strong><small>{{ $patient['proxima_atencion']['tipo'] }}</small>@else<span>Sin atención pendiente</span>@endif</span>
@if($href)</a>@else</div>@endif
