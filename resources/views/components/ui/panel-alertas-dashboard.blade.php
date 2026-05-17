@props(['alertas' => []])

<div class="rounded-[2rem] border border-terracota/25 bg-terracota/10 p-5 shadow-[0_16px_38px_rgba(233,122,95,0.10)]">
    <h2 class="text-lg font-black text-azul-profundo">
        Alertas administrativas
    </h2>

    <p class="mb-4 text-xs font-bold text-azul-profundo/55">
        Pendientes de revisión
    </p>

    <ul class="space-y-2">
        @forelse($alertas as $alerta)
            <li class="flex gap-2 rounded-[1.2rem] bg-[#E6DDD3]/65 p-3 text-xs font-bold leading-5 text-azul-profundo/70 transition hover:-translate-y-0.5">
                <i class="ph-bold ph-warning-circle mt-0.5 text-terracota"></i>
                {{ $alerta }}
            </li>
        @empty
            <li class="rounded-[1.2rem] bg-[#E6DDD3]/65 p-3 text-xs font-bold text-azul-profundo/55">
                Sin alertas pendientes.
            </li>
        @endforelse
    </ul>
</div>