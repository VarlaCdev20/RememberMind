{{-- TAB SEGUIMIENTO MEJORADO --}}
<section
    x-show="tab === 'seguimiento'"
    x-transition.opacity.duration.250ms
    class="space-y-4"
>
    {{-- Encabezado --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 border-b border-[#D5C7B9] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#6873A6]">
                    Seguimiento institucional
                </span>

                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                    Observaciones y atenciones recientes
                </h2>

                <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/55">
                    Registra cambios relevantes, atenciones realizadas y eventos importantes del adulto mayor.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <button type="button"
                        @click="abrir('observacion')"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#6873A6] px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(104,115,166,0.18)] transition hover:-translate-y-0.5 hover:bg-[#586393] active:scale-[0.98]">
                    <i class="ph-bold ph-note-pencil"></i>
                    Nueva observación
                </button>

                <button type="button"
                        @click="abrir('atencion')"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#9A7B60] px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(154,123,96,0.18)] transition hover:-translate-y-0.5 hover:bg-[#7A5C49] active:scale-[0.98]">
                    <i class="ph-bold ph-stethoscope"></i>
                    Nueva atención
                </button>
            </div>
        </div>

        {{-- Métricas --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Observaciones
                </p>
                <p class="mt-2 text-2xl font-black text-[#6873A6]">
                    {{ $totalObservaciones }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Atenciones
                </p>
                <p class="mt-2 text-2xl font-black text-[#7A5C49]">
                    {{ $totalAtenciones }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Estado seguimiento
                </p>
                <p class="mt-2 text-sm font-black {{ ($totalObservaciones + $totalAtenciones) > 0 ? 'text-[#617453]' : 'text-[#D96F58]' }}">
                    {{ ($totalObservaciones + $totalAtenciones) > 0 ? 'Activo' : 'Sin registros' }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Acción sugerida
                </p>
                <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                    {{ ($totalObservaciones + $totalAtenciones) > 0 ? 'Revisar evolución' : 'Registrar observación' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Contenido principal --}}
    <div class="grid gap-6">
        {{-- Tabla de Observaciones --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
            <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#6873A6]/15 text-[#566189]">
                        <i class="ph-bold ph-note-pencil text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-[#2F3E5C]">Historial de Observaciones</h3>
                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Seguimiento diario y cambios relevantes</p>
                    </div>
                </div>
                <button type="button" @click="abrir('observacion')" class="rounded-xl bg-[#6873A6] px-4 py-2 text-xs font-black text-white hover:bg-[#586393] transition">
                    <i class="ph-bold ph-plus mr-1"></i> Nueva
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60">
                        <tr>
                            <th class="px-6 py-3">Fecha</th>
                            <th class="px-6 py-3">Tipo / Descripción</th>
                            <th class="px-6 py-3">Importancia</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/30">
                        @forelse($observacionesActivas as $obs)
                            @php
                                $obsObj = is_object($obs) ? $obs : null;
                                $nivelImp = strtoupper(optional($obsObj)->nivel_importancia ?? 'NORMAL');
                                $colorImp = match($nivelImp) {
                                    'URGENTE' => 'bg-terracota/15 text-terracota',
                                    'ALTA' => 'bg-[#D9A27C]/15 text-[#9B6D4C]',
                                    default => 'bg-[#6873A6]/15 text-[#566189]',
                                };
                            @endphp
                            <tr class="group transition hover:bg-[#F2EBE3]/40">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs font-black text-[#2F3E5C]">{{ optional($obsObj)->fecha ? Carbon::parse($obsObj->fecha)->format('d/m/Y') : 'N/D' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-black text-[#2F3E5C]">{{ optional($obsObj)->tipo_obs }}</p>
                                    <p class="text-[11px] font-semibold text-[#2F3E5C]/60 line-clamp-1">{{ optional($obsObj)->descripcion }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase {{ $colorImp }}">
                                        {{ $nivelImp }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <button type="button" @click="abrir('observacion', @js($obs), false, true)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-eye"></i>
                                        </button>
                                        <button type="button" @click="abrir('observacion', @js($obs), true, false)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-pencil-simple"></i>
                                        </button>
                                        <form action="{{ route('admin.adultos-mayores.observaciones.destroy', [$idAdulto, $obsObj->cod_obs_adul]) }}" method="POST" onsubmit="confirmarAccion(event, 'Anular observación', 'Esta observación será anulada del registro activo. No se eliminará la información original.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Anular observación" class="rounded-lg bg-terracota/5 p-2 text-terracota hover:bg-terracota hover:text-white transition">
                                                <i class="ph-bold ph-x-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-xs font-bold text-[#2F3E5C]/40">Sin observaciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Observaciones Anuladas --}}
        @if(count($observacionesAnuladas) > 0)
        <div class="mt-8 border-t border-[#D5C7B9]/30 pt-6">
            <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-terracota/60 flex items-center gap-2">
                <i class="ph-bold ph-x-circle"></i> Historial de Observaciones Anuladas
            </h4>
            <div class="overflow-hidden rounded-[24px] border border-[#D5C7B9]/50 opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-[#E7DDD2]/40 backdrop-blur-sm shadow-sm">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/30 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                        <tr>
                            <th class="px-6 py-3">Fecha Anul.</th>
                            <th class="px-6 py-3">Tipo / Descripción Original</th>
                            <th class="px-6 py-3 text-right">Trazabilidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/20">
                        @foreach($observacionesAnuladas as $obsAnu)
                            <tr class="hover:bg-white/10 transition">
                                <td class="px-6 py-3 text-xs font-black text-[#2F3E5C]/60">
                                    {{ $obsAnu->deleted_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-[11px] font-black text-[#2F3E5C]/70 uppercase">{{ $obsAnu->tipo_obs }}</p>
                                    <p class="text-[10px] font-semibold text-[#2F3E5C]/50 line-clamp-1">{{ $obsAnu->descripcion }}</p>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <form action="{{ route('admin.adultos-mayores.observaciones.restore', [$adulto->cod_am, $obsAnu->cod_obs_adul]) }}" method="POST" onsubmit="confirmarAccion(event, 'Restaurar observación', 'La observación volverá a estar visible en el expediente activo.')">
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

        