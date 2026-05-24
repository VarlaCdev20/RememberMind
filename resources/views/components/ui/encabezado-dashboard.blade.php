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

<section class="rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)] backdrop-blur-xl">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-terracota/10 px-3 py-1 text-[11px] font-black uppercase tracking-widest text-terracota">
                    Casa Amandita
                </span>
                <span class="rounded-full bg-azul-profundo/10 px-3 py-1 text-[11px] font-black text-azul-profundo">
                    {{ $rolLegible }}
                </span>
            </div>

            <h1 class="text-xl font-black leading-tight text-azul-profundo md:text-2xl">
                {{ $saludoTexto }}, <span class="text-terracota">{{ $nombre }}</span>
            </h1>

            @if($fecha)
                <p class="mt-1 text-xs font-bold text-azul-profundo/55">{{ $fecha }}</p>
            @endif
        </div>

        @if(!empty($acciones))
            <div class="flex flex-wrap gap-3">
                @foreach($acciones as $accion)
                    @if($accion['estilo'] === 'terracota')
                        <a href="{{ $accion['href'] }}" class="rm-btn-terracota text-xs">
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
