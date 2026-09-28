{{--
 Componente: ui/status-badge
 Resuelve la variante semántica según el valor del estado o la variante solicitada.
 Normalizado:
 - primary (principal) = eucalipto
 - success (éxito) = verde
 - info (información) = azul
 - warning (advertencia) = cobre (cero amarillo fluorescente)
 - danger (peligro) = coral
 - neutral (neutral) = taupe/crema
 Tokens: var(--rm-radius-pill), 100% tokens semánticos, cero colores hardcodeados.
--}}
@props([
    'estado' => '',
    'label' => null,
    'variant' => null,
])

@php
    $valor = strtoupper(trim((string) $estado));
    $textoMostrar = $label ?? match($valor) {
        'PRIMARY', 'PRINCIPAL' => 'Principal',
        'ACTIVO', 'ACTIVA' => 'Activo',
        'ESTABLE' => 'Estable',
        'VIGENTE' => 'Vigente',
        'DISPONIBLE' => 'Disponible',
        'VIGILANCIA' => 'Vigilancia',
        'PENDIENTE' => 'Pendiente',
        'SUSPENDIDO', 'SUSPENDIDA' => 'Suspendido',
        'EN_REVISION' => 'En Revisión',
        'EN_ATENCION' => 'En Atención',
        'CRITICO', 'CRÍTICO' => 'Crítico',
        'ALTO' => 'Alto',
        'MEDIO' => 'Medio',
        'BAJO' => 'Bajo',
        'ALERTA' => 'Alerta',
        'ERROR' => 'Error',
        'FALLECIDO' => 'Fallecido',
        'INACTIVO', 'INACTIVA' => 'Inactivo',
        'CERRADA', 'CERRADO' => 'Cerrada',
        'ARCHIVADO', 'ARCHIVADA' => 'Archivado',
        default => ($valor !== '' ? ucfirst(strtolower(str_replace('_', ' ', $valor))) : ''),
    };

    $badgeVariant = match($variant ? strtolower($variant) : null) {
        'primary', 'principal' => 'rm-badge-primary',
        'success', 'exito', 'éxito' => 'rm-badge-success',
        'info', 'informacion', 'información' => 'rm-badge-info',
        'warning', 'advertencia' => 'rm-badge-warning',
        'clinical' => 'rm-badge-clinical',
        'danger', 'peligro' => 'rm-badge-danger',
        'neutral' => 'rm-badge-neutral',
        default => match($valor) {
            'PRIMARY', 'PRINCIPAL', 'INSTITUCIONAL'
                => 'rm-badge-primary',
            'ACTIVO', 'ACTIVA', 'ESTABLE', 'VIGENTE', 'DISPONIBLE', 'EXITO', 'RESUELTA', 'RESUELTO', 'ADMINISTRADO', 'ADMINISTRADA'
                => 'rm-badge-success',
            'VIGILANCIA', 'PENDIENTE', 'SUSPENDIDO', 'SUSPENDIDA', 'EN_REVISION', 'ABIERTA', 'ABIERTO', 'MEDIO'
                => 'rm-badge-warning',
            'CRITICO', 'CRÍTICO', 'ALTO', 'ALERTA', 'ERROR', 'FALLECIDO', 'RETIRADO'
                => 'rm-badge-danger',
            'EN_ATENCION', 'TRASLADADO', 'SEGUIMIENTO_ESPECIAL', 'INFORMATIVO', 'PRESCRITO', 'PROGRAMADO', 'BAJO'
                => 'rm-badge-info',
            default
                => 'rm-badge-neutral',
        }
    };
@endphp

<span {{ $attributes->merge(['class' => "rm-badge {$badgeVariant}"]) }}>
    <span class="rm-badge-dot"></span>
    <span>{{ $textoMostrar ?: '—' }}</span>
</span>
