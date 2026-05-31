{{--
    Componente: ui/status-badge
    Resuelve automáticamente la clase de color según el valor del estado.

    Uso: <x-ui.status-badge :estado="$registro->estado" />
         <x-ui.status-badge estado="ACTIVO" />
--}}
@props(['estado' => ''])

@php
    $valor = strtoupper((string) $estado);

    $clase = match($valor) {
        'ACTIVO', 'ACTIVA', 'VIGENTE', 'DISPONIBLE'
            => 'rm-badge-success',
        'SUSPENDIDO', 'SUSPENDIDA', 'EN_REVISION', 'PENDIENTE'
            => 'rm-badge-warning',
        'INACTIVO', 'INACTIVA', 'ANULADO', 'ANULADA', 'ARCHIVADO', 'ARCHIVADA', 'FINALIZADO', 'FINALIZADA'
            => 'rm-badge-neutral',
        'CRÍTICO', 'CRITICO', 'FALLECIDO', 'RETIRADO'
            => 'rm-badge-danger',
        'TRASLADADO', 'SEGUIMIENTO_ESPECIAL'
            => 'rm-badge-info',
        'RESTAURADO'
            => 'rm-badge-purple',
        default
            => 'rm-badge-neutral',
    };
@endphp

<span class="{{ $clase }}">{{ $valor ?: '—' }}</span>
