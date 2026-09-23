@props(['saludo' => []])

@php
$nombre = $saludo['nombre'] ?? 'Usuario';
$rolLegible = $saludo['rolLegible'] ?? 'Usuario del sistema';
$saludoTexto = $saludo['saludo'] ?? 'Bienvenido';
$fecha = $saludo['fecha'] ?? '';

$acciones = [];
if (auth()->user()?->can('adultos-mayores.crear')) {
    $acciones[] = [
        'href' => route('admin.admisiones.preadmision'),
        'icono' => 'ph-plus-circle',
        'label' => 'Nuevo registro',
        'estilo' => 'terracota',
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

<section class="rounded-2xl border border-[#D5CABE] dark:border-[#4A443E] bg-[#F0E8DE] dark:bg-[#262422] p-5 shadow-[0_6px_18px_rgba(70,55,45,0.05)] backdrop-blur-xl">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold uppercase tracking-wider bg-[#E8F1E5] dark:bg-[#63775B]/25 text-[#63775B] dark:text-[#9DB491] border border-[#B8CDAE] dark:border-[#63775B]">
                    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold uppercase tracking-wider bg-[#E0D5C9] dark:bg-[#34302C] text-[#677084] dark:text-[#B8ADA2] border border-[#D5CABE] dark:border-[#4A443E]">
                    {{ $rolLegible }}
                </span>
            </div>

            <h1 class="text-xl font-black leading-tight text-[#304060] dark:text-[#F8F2EC] md:text-2xl tracking-tight">
                {{ $saludoTexto }}, <span class="text-[#A35A44] dark:text-[#D68A72]">{{ $nombre }}</span>
            </h1>

            @if($fecha)
                <p class="mt-1 text-xs font-semibold text-[#677084] dark:text-[#B8ADA2]">{{ $fecha }}</p>
            @endif
        </div>

        @if(!empty($acciones))
            <div class="flex flex-wrap gap-2.5">
                @foreach($acciones as $accion)
                    @if($accion['estilo'] === 'terracota')
                        <a href="{{ $accion['href'] }}" class="rm-btn rm-btn-accent text-xs">
                            <i class="ph-bold {{ $accion['icono'] }} mr-1"></i>{{ $accion['label'] }}
                        </a>
                    @else
                        <a href="{{ $accion['href'] }}" class="rm-btn rm-btn-secondary text-xs">
                            <i class="ph-bold {{ $accion['icono'] }} mr-1"></i>{{ $accion['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>
