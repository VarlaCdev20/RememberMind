@props(['actividades' => []])

<div class="rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)]">
    <h2 class="text-lg font-black text-azul-profundo">
        Actividades registradas
    </h2>

    <p class="mb-4 text-xs font-bold text-azul-profundo/55">
        Resumen operativo reciente
    </p>

    <div class="space-y-3">
        @forelse($actividades as $actividad)
            <div class="rounded-[1.4rem] bg-[#D5C7B9]/75 p-3 shadow-sm transition hover:-translate-y-0.5 hover:bg-[#D5C7B9]">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-terracota/10 text-terracota">
                        <i class="ph-fill {{ $actividad['icono'] ?? 'ph-calendar-check' }} text-lg"></i>
                    </div>

                    <div>
                        <p class="text-sm font-black">
                            {{ $actividad['titulo'] ?? 'Actividad registrada' }}
                        </p>

                        <p class="text-xs font-bold text-azul-profundo/55">
                            {{ $actividad['detalle'] ?? 'Sin detalle' }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <p class="rounded-[1.4rem] bg-[#D5C7B9]/70 p-4 text-sm font-bold text-azul-profundo/55">
                No hay actividades registradas.
            </p>
        @endforelse
    </div>
</div>