@props(['saludo' => []])

@php
$nombre = $saludo['nombre'] ?? 'Usuario';
$rolLegible = $saludo['rolLegible'] ?? 'Usuario del sistema';
$saludoTexto = $saludo['saludo'] ?? 'Bienvenido';
$fecha = $saludo['fecha'] ?? '';

$acciones = [];
if (auth()->user()?->can('residentes.gestionar')) {
    $acciones[] = [
        'href' => route('admin.admisiones.preadmision'),
        'icono' => 'ph-plus-circle',
        'label' => 'Nuevo registro',
        'estilo' => 'primary',
    ];
}
if (auth()->user()?->can('usuarios.ver')) {
    $acciones[] = [
        'href' => route('admin.usuarios.index'),
        'icono' => 'ph-users-three',
        'label' => 'Gestionar usuarios',
        'estilo' => 'secondary',
    ];
}
if (auth()->user()?->can('reportes.institucional')) {
    $acciones[] = [
        'href' => route('admin.reportes.institucional.preview'),
        'icono' => 'ph-file-text',
        'label' => 'Reporte institucional',
        'estilo' => 'secondary',
    ];
}
$acciones = array_slice($acciones, 0, 3);
@endphp

<x-ui.role-dashboard-hero
    personal-greeting
    eyebrow="CENTRO GERIÁTRICO LOS ALMENDROS"
    :title="$saludoTexto . ', ' . $nombre"
    highlight="Gestionar bien también es cuidar"
    description="Supervisa la operación del centro, prioriza lo importante y acompaña al equipo desde una lectura clara y humana."
    :image="asset('images/FOTOS CENTRO DE ADULTOS MAYORES/595693419_1366687118802540_7877864884520394638_n.jpg')"
    rotation-context="administracion"
    image-alt="Residentes y equipo durante una experiencia cultural del centro"
    quote="Cada decisión protege una historia"
    :meta="[
        ['icon' => 'ph-calendar-blank', 'label' => $fecha],
        ['icon' => 'ph-shield-check', 'label' => $rolLegible],
        ['icon' => 'ph-buildings', 'label' => 'Operación institucional'],
    ]"
>
    @foreach($acciones as $accion)
        <a href="{{ $accion['href'] }}" class="{{ $accion['estilo'] === 'primary' ? 'rm-btn-primary' : 'rm-btn-secondary' }} min-h-9 px-3.5 text-xs">
            <i class="ph-bold {{ $accion['icono'] }}"></i>
            <span>{{ $accion['label'] }}</span>
        </a>
    @endforeach
</x-ui.role-dashboard-hero>
