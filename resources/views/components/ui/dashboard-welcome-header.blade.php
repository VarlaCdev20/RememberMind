<x-ui.dashboard-header
    eyebrow="CENTRO GERIÁTRICO LOS ALMENDROS"
    :title="$compact ? 'Mi turno' : $saludo . ', ' . $nombreCompleto"
    :subtitle="$contextoTurno"
    :role="$rolVisible"
    :image="$compact ? null : asset($image)"
    :compact="$compact"
    :scope="$scope ?? ($modo === 'FUERA_DE_TURNO' ? 'Mis residentes asignados · Solo consulta' : 'Cuidados y seguimiento')"
    data-time-zone="{{ $zonaHoraria }}"
    {{ $attributes }}
>
    <x-slot:date>{{ ucfirst(now()->timezone($zonaHoraria)->locale('es')->translatedFormat('l, d \\d\\e F \\d\\e Y')) }}</x-slot:date>
</x-ui.dashboard-header>
