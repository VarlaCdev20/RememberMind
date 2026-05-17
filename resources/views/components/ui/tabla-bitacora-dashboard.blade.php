@props(['registros' => []])

<section class="dash-anim rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)] backdrop-blur-xl">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="text-[11px] font-black uppercase tracking-widest text-terracota">
                Auditoría
            </span>

            <h2 class="text-xl font-black text-azul-profundo">
                Bitácora completa del sistema
            </h2>

            <p class="text-xs font-bold text-azul-profundo/55">
                Últimas acciones registradas mediante trazabilidad institucional.
            </p>
        </div>

        <button disabled class="rounded-full bg-[#D5C7B9] px-4 py-2 text-xs font-black text-[#967B66] opacity-50 cursor-not-allowed shadow-sm"
                title="Próximamente">
            Ver bitácora completa
        </button>
    </div>

    <div class="overflow-x-auto rounded-[1.4rem] border border-[#C7B5A3]/70">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-[#D5C7B9] text-[11px] uppercase tracking-widest text-azul-profundo/55">
                <tr>
                    <th class="px-4 py-3 font-black">Fecha</th>
                    <th class="px-4 py-3 font-black">Usuario</th>
                    <th class="px-4 py-3 font-black">Acción</th>
                    <th class="px-4 py-3 font-black">Módulo</th>
                    <th class="px-4 py-3 font-black">Detalle</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[#C7B5A3]/60 bg-[#E6DDD3]/60">
                @forelse($registros as $log)
                    <tr class="transition hover:bg-[#D5C7B9]/75">
                        <td class="px-4 py-3 text-xs font-bold text-azul-profundo/60">
                            {{ $log['fecha'] ?? '-' }}
                        </td>

                        <td class="px-4 py-3 font-black">
                            {{ $log['usuario'] ?? 'Sistema' }}
                        </td>

                        <td class="px-4 py-3">
                            @php
                                $color = match($log['accion']) {
                                    'Registro creado' => 'bg-[#8DA280]/15 text-[#63775B]',
                                    'Registro actualizado' => 'bg-azul-profundo/10 text-azul-profundo',
                                    'Registro eliminado' => 'bg-terracota/10 text-terracota',
                                    'Acceso al sistema', 'Inicio de sesión' => 'bg-[#967B66]/15 text-[#967B66]',
                                    default => 'bg-[#8DA280]/15 text-[#63775B]'
                                };
                            @endphp
                            <span class="rounded-full {{ $color }} px-3 py-1 text-[10px] font-black uppercase">
                                {{ $log['accion'] ?? 'Acción' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-xs font-bold text-azul-profundo/65">
                            {{ $log['modulo'] ?? 'General' }}
                        </td>

                        <td class="px-4 py-3 text-xs font-bold text-azul-profundo/55">
                            {{ $log['detalle'] ?? 'Sin detalle' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm font-bold text-azul-profundo/55">
                            No hay registros recientes en la bitácora.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>