@props(['code'])
@php
    $custom = ['BASTON', 'ANDADOR', 'BARANDILLA', 'GRUA'];
    $icons = ['CAMINAR_HABITACION' => 'ph-person-simple-walk', 'CAMINAR_PASILLO' => 'ph-person-simple-walk',
        'LEVANTARSE_CAMA' => 'ph-bed', 'TRANSFERENCIA_CAMA_SILLON' => 'ph-arrows-left-right',
        'CAMBIO_POSTURAL' => 'ph-arrows-clockwise', 'SEDESTACION' => 'ph-user', 'BIPEDESTACION' => 'ph-person-arms-spread',
        'INDEPENDIENTE' => 'ph-person-simple-walk', 'ASISTIDA' => 'ph-users', 'SILLA_RUEDAS' => 'ph-wheelchair',
        'ENCAMADO' => 'ph-bed', 'SUPERVISION' => 'ph-eye', 'AYUDA_UNA_PERSONA' => 'ph-user', 'AYUDA_DOS_PERSONAS' => 'ph-users',
        'NINGUNO' => 'ph-prohibit', 'OTRO' => 'ph-dots-three', 'SIN_CAMBIOS' => 'ph-equals', 'MEJOR' => 'ph-trend-up', 'PEOR' => 'ph-trend-down'];
@endphp
@if(in_array($code, $custom, true))
    <svg class="rm-movilidad__choice-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        @switch($code)
            @case('BASTON')<path d="M8 7a4 4 0 0 1 8 0v14M14 21h4"/>@break
            @case('ANDADOR')<path d="M5 21 7 5h10l2 16M6 14h12M8 5V3m8 2V3"/><circle cx="5" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>@break
            @case('BARANDILLA')<path d="M3 21V6h18v15M3 10h18M7 10v8m5-8v8m5-8v8M3 18h18"/>@break
            @case('GRUA')<path d="M4 21h11M7 21V4l11 3v5m-3 0h6l-2 6h-2z"/><circle cx="4" cy="22" r="1"/><circle cx="14" cy="22" r="1"/>@break
        @endswitch
    </svg>
@else
    <i class="ph-bold {{ $icons[$code] ?? 'ph-minus' }} rm-movilidad__choice-icon" aria-hidden="true"></i>
@endif
