{{-- Tabla de Atenciones --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
            <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#9A7B60]/15 text-[#7A5C49]">
                        <i class="ph-bold ph-stethoscope text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2F3E5C]">Atenciones / Citas Médicas</h3>
                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Historial médico e intervenciones</p>
                    </div>
                </div>
                <button type="button" @click="abrir('atencion')" class="rounded-xl bg-[#9A7B60] px-4 py-2 text-xs font-black text-white hover:bg-[#7A5C49] transition">
                    <i class="ph-bold ph-plus mr-1"></i> Nueva
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60">
                        <tr>
                            <th class="px-6 py-3">Fecha / Hora</th>
                            <th class="px-6 py-3">Tipo Atención</th>
                            <th class="px-6 py-3">Observación / Motivo</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/30">
                        @forelse($atencionesActivas as $atencion)
                            @php
                                $atencionObj = is_object($atencion) ? $atencion : null;
                                $estadoAten = strtoupper(optional($atencionObj)->estado ?? 'PENDIENTE');
                                $colorEstado = match($estadoAten) {
                                    'FINALIZADA', 'REALIZADA' => 'bg-[#8EA17D]/15 text-[#617453]',
                                    'CANCELADA' => 'bg-terracota/15 text-terracota',
                                    default => 'bg-[#D9A27C]/15 text-[#9B6D4C]',
                                };
                            @endphp
                            <tr class="group transition hover:bg-[#F2EBE3]/40">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-xs font-black text-[#2F3E5C]">{{ optional($atencionObj)->fecha ? \Carbon\Carbon::parse($atencionObj->fecha)->format('d/m/Y') : 'N/D' }}</p>
                                    <p class="text-[10px] font-bold text-[#2F3E5C]/50">{{ optional($atencionObj)->hora ? substr($atencionObj->hora, 0, 5) : '--:--' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-black text-[#2F3E5C]">{{ optional($atencionObj)->tipoAtencion->tipo ?? 'Atención' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[11px] font-semibold text-[#2F3E5C]/65 line-clamp-1">{{ optional($atencionObj)->obs ?? 'Sin detalle' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase {{ $colorEstado }}">
                                        {{ $estadoAten }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <button type="button" @click="abrir('atencion', @js($atencion), false, true)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-eye"></i>
                                        </button>
                                        <button type="button" @click="abrir('atencion', @js($atencion), true, false)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </button>
                                        <form action="{{ route('admin.adultos-mayores.atenciones.destroy', [$idAdulto, $atencionObj->cod_aten_adul ?? '0']) }}" method="POST" onsubmit="confirmarAccion(event, 'Anular atención médica', 'La atención se marcará como anulada para fines de trazabilidad y auditoría.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Anular atención" class="rounded-lg bg-terracota/5 p-2 text-terracota hover:bg-terracota hover:text-white transition">
                                                <i class="ph-bold ph-x-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-xs font-bold text-[#2F3E5C]/40">Sin atenciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Atenciones Anuladas --}}
        @if(count($atencionesAnuladas) > 0)
        <div class="mt-8 border-t border-[#D5C7B9]/30 pt-6">
            <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-terracota/60 flex items-center gap-2">
                <i class="ph-bold ph-x-circle"></i> Historial de Atenciones Anuladas
            </h4>
            <div class="overflow-hidden rounded-[24px] border border-[#D5C7B9]/50 opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-[#E7DDD2]/40 backdrop-blur-sm shadow-sm">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/30 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                        <tr>
                            <th class="px-6 py-3">Fecha Anul.</th>
                            <th class="px-6 py-3">Tipo / Motivo Original</th>
                            <th class="px-6 py-3 text-right">Trazabilidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/20">
                        @foreach($atencionesAnuladas as $ateAnu)
                            <tr class="hover:bg-white/10 transition">
                                <td class="px-6 py-3 text-xs font-black text-[#2F3E5C]/60">
                                    {{ $ateAnu->deleted_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-[11px] font-black text-[#2F3E5C]/70 uppercase">{{ $ateAnu->tipoAtencion->nombre ?? 'Atención' }}</p>
                                    <p class="text-[10px] font-semibold text-[#2F3E5C]/50 line-clamp-1">{{ $ateAnu->motivo_consulta }}</p>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form action="{{ route('admin.adultos-mayores.atenciones.restore', [$adulto->cod_am, $ateAnu->cod_aten_adul]) }}" method="POST" onsubmit="confirmarAccion(event, 'Restaurar atención', 'El registro de atención volverá al historial activo del paciente.')">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="Restaurar" class="rounded-lg bg-[#8EA17D]/10 p-1.5 text-[#8EA17D] hover:bg-[#8EA17D] hover:text-white transition">
                                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</section>

            