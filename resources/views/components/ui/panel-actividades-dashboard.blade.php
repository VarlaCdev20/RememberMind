@props(['actividades' => []])

<div class="rm-card rounded-[2rem] p-5">
    <h2 class="text-lg font-black text-titulo">Actividades registradas</h2>
    <p class="mb-4 text-xs font-bold text-meta">Últimas operaciones del sistema</p>

    <div class="space-y-2">
        @forelse($actividades as $actividad)
            <div class="rounded-[1.4rem] bg-fondo-hover p-3 transition hover:-translate-y-0.5 hover:bg-fondo-card-calido">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-estado-advertencia-bg text-boton-acento">
                        <i class="ph-fill {{ $actividad['icono'] ?? 'ph-calendar-check' }} text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-titulo">
                            {{ $actividad['titulo'] ?? 'Actividad registrada' }}
                        </p>
                        <p class="truncate text-xs font-bold text-meta">
                            {{ $actividad['detalle'] ?? 'Sin detalle' }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-[1.4rem] bg-fondo-hover p-5 text-center">
                <i class="ph-fill ph-calendar-blank text-2xl text-meta"></i>
                <p class="mt-2 text-sm font-bold text-meta">Sin actividades recientes</p>
            </div>
        @endforelse
    </div>
</div>
