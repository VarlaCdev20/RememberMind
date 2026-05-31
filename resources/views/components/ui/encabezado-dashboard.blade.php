@props(['saludo' => []])

@php
$nombre      = $saludo['nombre']     ?? 'Usuario';
$rolLegible  = $saludo['rolLegible'] ?? 'Usuario del sistema';
$saludoTexto = $saludo['saludo']     ?? 'Bienvenido';
$fecha       = $saludo['fecha']      ?? '';

$acciones = [];
if (auth()->user()?->can('adultos-mayores.crear')) {
    $acciones[] = [
        'href'   => route('admin.adultos-mayores.create'),
        'icono'  => 'ph-plus-circle',
        'label'  => 'Nuevo registro',
        'estilo' => 'terracota',
    ];
}
if (auth()->user()?->can('usuarios.ver')) {
    $acciones[] = [
        'href'   => route('admin.usuarios.index'),
        'icono'  => 'ph-users-three',
        'label'  => 'Gestionar usuarios',
        'estilo' => 'secondary',
    ];
}
if (auth()->user()?->can('reportes.institucional')) {
    $acciones[] = [
        'href'   => route('admin.reportes.institucional.preview'),
        'icono'  => 'ph-file-text',
        'label'  => 'Reporte institucional',
        'estilo' => 'secondary',
    ];
}
$acciones = array_slice($acciones, 0, 3);
@endphp

<section class="card-interactiva borde-verde-suave rounded-[2rem] border p-5 backdrop-blur-xl">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="badge-mint">
                    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                </span>
                <span class="rm-badge-neutral">
                    {{ $rolLegible }}
                </span>
            </div>

            <h1 class="text-xl font-black leading-tight text-titulo md:text-2xl">
                {{ $saludoTexto }}, <span class="text-boton-acento">{{ $nombre }}</span>
            </h1>

            @if($fecha)
                <p class="mt-1 text-xs font-bold text-meta">{{ $fecha }}</p>
            @endif
        </div>

        @if(!empty($acciones))
            <div class="flex flex-wrap gap-3">
                @foreach($acciones as $accion)
                    @if($accion['estilo'] === 'terracota')
                        <a href="{{ $accion['href'] }}" class="rm-btn-accent text-xs">
                            <i class="ph-bold {{ $accion['icono'] }} mr-1"></i>{{ $accion['label'] }}
                        </a>
                    @else
                        <a href="{{ $accion['href'] }}" class="rm-btn-secondary text-xs">
                            <i class="ph-bold {{ $accion['icono'] }} mr-1"></i>{{ $accion['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        @endif

    </div>
</section>
