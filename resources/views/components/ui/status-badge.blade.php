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
            => 'enf-badge enf-badge-positive',
        'SUSPENDIDO', 'SUSPENDIDA', 'EN_REVISION', 'PENDIENTE'
            => 'enf-badge enf-badge-warning',
        'INACTIVO', 'INACTIVA', 'ANULADO', 'ANULADA', 'ARCHIVADO', 'ARCHIVADA', 'FINALIZADO', 'FINALIZADA'
            => 'enf-badge enf-badge-neutral',
        'CRÍTICO', 'CRITICO', 'FALLECIDO', 'RETIRADO'
            => 'enf-badge enf-badge-risk',
        'TRASLADADO', 'SEGUIMIENTO_ESPECIAL'
            => 'enf-badge enf-badge-info',
        'RESTAURADO'
            => 'enf-badge enf-badge-coral',
        default
            => 'enf-badge enf-badge-neutral',
    };
@endphp

<span class="{{ $clase }}">{{ $valor ?: '—' }}</span>
