@props(['registros' => []])

<section class="rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)] backdrop-blur-xl">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="text-[11px] font-black uppercase tracking-widest text-terracota">
                Auditoría
            </span>
            <h2 class="text-xl font-black text-azul-profundo">Bitácora del sistema</h2>
            <p class="text-xs font-bold text-azul-profundo/55">Últimas acciones registradas mediante trazabilidad institucional.</p>
        </div>

        @can('bitacora.ver')
            <a href="{{ route('admin.bitacora.index') }}" class="rm-btn-secondary text-xs">
                <i class="ph-bold ph-list-magnifying-glass mr-1"></i>
                Ver bitácora completa
            </a>
        @endcan
    </div>

    <div class="overflow-x-auto rounded-[1.4rem] border border-[#C7B5A3]/70">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-[#D5C7B9] text-[11px] uppercase tracking-widest text-azul-profundo/55">
                <tr>
                    <th class="px-4 py-3 font-black">Fecha</th>
                    <th class="px-4 py-3 font-black">Usuario</th>
                    <th class="px-4 py-3 font-black">Acción</th>
                    <th class="px-4 py-3 font-black">Módulo</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[#C7B5A3]/60 bg-[#E6DDD3]/60">
                @forelse(array_slice($registros, 0, 5) as $log)
                    <tr class="transition hover:bg-[#D5C7B9]/75">
                        <td class="px-4 py-3 text-xs font-bold text-azul-profundo/60">
                            {{ $log['fecha'] ?? '-' }}
                        </td>

                        <td class="px-4 py-3 font-black text-sm text-azul-profundo">
                            {{ $log['usuario'] ?? 'Sistema' }}
                        </td>

                        <td class="px-4 py-3">
                            @php
                                $color = match($log['accion'] ?? '') {
                                    'Registro creado'    => 'rm-badge-success',
                                    'Registro actualizado' => 'rm-badge-info',
                                    'Registro eliminado' => 'rm-badge-danger',
                                    'Inicio de sesión', 'Acceso al sistema' => 'rm-badge-neutral',
                                    default              => 'rm-badge-neutral',
                                };
                            @endphp
                            <span class="{{ $color }}">{{ $log['accion'] ?? 'Acción' }}</span>
                        </td>

                        <td class="px-4 py-3 text-xs font-bold text-azul-profundo/65">
                            {{ $log['modulo'] ?? 'General' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm font-bold text-azul-profundo/55">
                            No hay registros recientes en la bitácora.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
