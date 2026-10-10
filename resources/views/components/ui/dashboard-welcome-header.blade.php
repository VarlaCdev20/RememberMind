@props([
    'usuario' => null,
    'image' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/621786801_1404497435021508_7880315777607437580_n.jpg',
    'secondaryImage' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/558487013_1337134818424437_2282337776297854403_n.jpg',
    'estado' => null,
    'modo' => null,
    'compact' => false,
    'scope' => null,
])

@php
    $usuario = $usuario ?? auth()->user();
    $persona = $usuario?->personal ?: $usuario?->contactos()->first();
    $nombreCompleto = trim(implode(' ', array_filter([
        $persona?->nombres,
        $persona?->apellido_paterno,
        $persona?->apellido_materno,
    ])));
    if ($nombreCompleto !== '') {
        $nombreCompleto = mb_convert_case(mb_strtolower($nombreCompleto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        $nombreCompleto = preg_replace_callback(
            '/\b(?:De|Del|La|Las|Los|Y)\b/u',
            static fn ($coincidencia) => mb_strtolower($coincidencia[0], 'UTF-8'),
            $nombreCompleto
        );
    } else {
        $nombreCompleto = 'equipo de Enfermería';
    }
    $nombreLargo = mb_strlen($nombreCompleto, 'UTF-8') > 32;
    $rol = app(\App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService::class)->activeRole($usuario)
        ?? (request()->routeIs('admin.enfermeria.*') && $usuario?->hasRole('ENFERMEROS')
            ? 'ENFERMEROS'
            : $usuario?->getRoleNames()->first());
    $rolVisible = $rol ? mb_convert_case(str_replace('_', ' ', $rol), MB_CASE_TITLE, 'UTF-8') : 'Personal';
    $zonaHoraria = config('app.timezone');
    $horaLocal = now()->timezone($zonaHoraria)->hour;
    $saludo = $horaLocal < 12 ? 'Buenos días' : ($horaLocal < 19 ? 'Buenas tardes' : 'Buenas noches');
    $contextoTurno = $estado === 'SIN_JORNADA_ACTIVA'
        ? 'No tienes residentes asignados para consultar hoy. El registro clínico está deshabilitado fuera de turno.'
        : ($modo === 'FUERA_DE_TURNO' ? 'Fuera de turno: consulta únicamente tus residentes asignados. El registro clínico está deshabilitado.' : 'Cuidados y seguimiento de tu turno. Revisa pendientes y alertas.');
@endphp

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
