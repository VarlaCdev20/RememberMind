@props(['actividades' => []])

<div class="rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)]">
    <h2 class="text-lg font-black text-azul-profundo">Actividades registradas</h2>
    <p class="mb-4 text-xs font-bold text-azul-profundo/55">Últimas operaciones del sistema</p>

    <div class="space-y-2">
        @forelse($actividades as $actividad)
            <div class="rounded-[1.4rem] bg-[#D5C7B9]/75 p-3 transition hover:-translate-y-0.5 hover:bg-[#D5C7B9]">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-terracota/10 text-terracota">
                        <i class="ph-fill {{ $actividad['icono'] ?? 'ph-calendar-check' }} text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-azul-profundo">
                            {{ $actividad['titulo'] ?? 'Actividad registrada' }}
                        </p>
                        <p class="truncate text-xs font-bold text-azul-profundo/55">
                            {{ $actividad['detalle'] ?? 'Sin detalle' }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-[1.4rem] bg-[#D5C7B9]/60 p-5 text-center">
                <i class="ph-fill ph-calendar-blank text-2xl text-azul-profundo/25"></i>
                <p class="mt-2 text-sm font-bold text-azul-profundo/55">Sin actividades recientes</p>
            </div>
        @endforelse
    </div>
</div>
