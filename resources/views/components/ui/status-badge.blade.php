{{--
 Componente: ui/status-badge
 Resuelve automáticamente la clase de color según el valor del estado.
 Canónico RememberMind basado en Alertas y Cuidados.
--}}
@props(['estado' => '', 'label' => null])

@php
    $valor = strtoupper(trim((string) $estado));
    $textoMostrar = $label ?? match($valor) {
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
        'ALERTA' => 'Alerta',
        'ERROR' => 'Error',
        'FALLECIDO' => 'Fallecido',
        'INACTIVO', 'INACTIVA' => 'Inactivo',
        'CERRADA', 'CERRADO' => 'Cerrada',
        'ARCHIVADO', 'ARCHIVADA' => 'Archivado',
        default => ucfirst(strtolower(str_replace('_', ' ', $valor))),
    };

    $estilo = match($valor) {
        'ACTIVO', 'ACTIVA', 'ESTABLE', 'VIGENTE', 'DISPONIBLE', 'EXITO', 'RESUELTA', 'RESUELTO'
            => 'bg-[#E8F1E5] dark:bg-[#63775B]/25 text-[#63775B] dark:text-[#9DB491] border-[#B8CDAE] dark:border-[#63775B]',
        'VIGILANCIA', 'PENDIENTE', 'SUSPENDIDO', 'SUSPENDIDA', 'EN_REVISION', 'ABIERTA', 'ABIERTO'
            => 'bg-[#FFF1D6] dark:bg-[#D2A45E]/20 text-[#966B24] dark:text-[#E0B36D] border-[#E8C178] dark:border-[#D2A45E]/50',
        'CRITICO', 'CRÍTICO', 'ALTO', 'ALERTA', 'ERROR', 'FALLECIDO', 'RETIRADO'
            => 'bg-[#FFF0F0] dark:bg-[#A7443B]/20 text-[#A7443B] dark:text-[#F07A70] border-[#EFA3A3] dark:border-[#A7443B]/50',
        'EN_ATENCION', 'TRASLADADO', 'SEGUIMIENTO_ESPECIAL', 'INFORMATIVO'
            => 'bg-[#E9EEF6] dark:bg-[#5F7899]/20 text-[#3D5A7E] dark:text-[#8AA4C4] border-[#B8CBD8] dark:border-[#5F7899]/50',
        default
            => 'bg-[#E0D5C9] dark:bg-[#34302C] text-[#677084] dark:text-[#B8ADA2] border-[#D5CABE] dark:border-[#4A443E]',
    };
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold uppercase tracking-wider border shadow-2xs {{ $estilo }}">
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-80"></span>
    <span>{{ $textoMostrar ?: '—' }}</span>
</span>
