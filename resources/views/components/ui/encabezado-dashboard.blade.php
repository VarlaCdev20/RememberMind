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

<section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-[var(--rm-shadow-md)] backdrop-blur-xl">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold uppercase tracking-wider bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/40">
                    CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10.5px] font-bold uppercase tracking-wider bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] border border-[var(--rm-border)]">
                    {{ $rolLegible }}
                </span>
            </div>

            <h1 class="text-xl font-black leading-tight text-[var(--rm-text-primary)] md:text-2xl tracking-tight">
                {{ $saludoTexto }}, <span class="text-[var(--rm-action-primary)]">{{ $nombre }}</span>
            </h1>

            @if($fecha)
                <p class="mt-1 text-xs font-semibold text-[var(--rm-text-muted)]">{{ $fecha }}</p>
            @endif
        </div>

        @if(!empty($acciones))
            <div class="flex flex-wrap gap-2.5">
                @foreach($acciones as $accion)
                    @if($accion['estilo'] === 'primary')
                        <a href="{{ $accion['href'] }}" class="rm-btn rm-btn-primary text-xs">
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
