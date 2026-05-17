{{-- TAB EVALUACIONES COGNITIVAS --}}
<section
    x-show="tab === 'evaluaciones'"
    x-transition.opacity.duration.250ms
    class="space-y-4"
>
    {{-- Encabezado --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 border-b border-[#D5C7B9] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#5B5F97]">
                    Salud Mental y Cognitiva
                </span>

                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                    Evaluaciones Cognitivas (MoCA / MMSE)
                </h2>

                <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/55">
                    Historial de evaluaciones de tamizaje para la detección temprana de deterioro cognitivo.
                </p>
            </div>

            <button type="button"
                    @click="abrir('evaluacion')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#5B5F97] px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(91,95,151,0.18)] transition hover:-translate-y-0.5 hover:bg-[#4A4E80] active:scale-[0.98]">
                <i class="ph-bold ph-brain"></i>
                Nueva evaluación
            </button>
        </div>

        {{-- Métricas Evaluaciones --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Evaluaciones Realizadas
                </p>
                <p class="mt-2 text-2xl font-black text-[#5B5F97]">
                    {{ $evaluacionesLista->count() }}
                </p>
            </div>

            @php
                $ultimaEval = $evaluacionesLista->first();
            @endphp
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Último Puntaje
                </p>
                <p class="mt-2 text-2xl font-black {{ optional($ultimaEval)->nivel_riesgo === 'BAJO' ? 'text-[#617453]' : 'text-[#D96F58]' }}">
                    {{ optional($ultimaEval)->puntaje_total ?? '--' }} / {{ optional($ultimaEval)->puntaje_maximo ?? '30' }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Estado Cognitivo
                </p>
                <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                    {{ optional($ultimaEval)->resultado_interpretacion ?? 'Sin datos' }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Riesgo Detectado
                </p>
                <p class="mt-2 text-sm font-black {{ optional($ultimaEval)->nivel_riesgo === 'BAJO' ? 'text-[#617453]' : (optional($ultimaEval)->nivel_riesgo === 'MEDIO' ? 'text-[#D9A27C]' : 'text-[#D96F58]') }}">
                    {{ optional($ultimaEval)->nivel_riesgo ?? 'No evaluado' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Lista de Evaluaciones --}}
    <section class="grid gap-4">
        <div class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
    {{-- Tabla de Evaluaciones --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
        <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#5B5F97]/15 text-[#5B5F97]">
                    <i class="ph-bold ph-brain text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-[#2F3E5C]">Evaluaciones Cognitivas</h3>
                    <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Estado mental y seguimiento cognitivo</p>
                </div>
            </div>
            <button type="button" @click="abrir('evaluacion')" class="rounded-xl bg-[#5B5F97] px-4 py-2 text-xs font-black text-white hover:bg-[#4A4E80] transition">
                <i class="ph-bold ph-plus mr-1"></i> Nueva Evaluación
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2F3E5C]">
                <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60">
                    <tr>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Tipo Evaluación</th>
                        <th class="px-6 py-3">Resultado / Riesgo</th>
                        <th class="px-6 py-3">Interpretación</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/30">
                    @forelse($evaluacionesActivas as $eval)
                        @php
                            $riesgo = strtoupper($eval->nivel_riesgo ?? 'NORMAL');
                            $colorRiesgo = match($riesgo) {
                                'ALTO' => 'bg-terracota/15 text-terracota',
                                'MODERADO' => 'bg-[#D9A27C]/15 text-[#9B6D4C]',
                                default => 'bg-[#8EA17D]/15 text-[#617453]',
                            };
                        @endphp
                        <tr class="group transition hover:bg-[#F2EBE3]/40">
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-black">
                                {{ $eval->fecha_eval->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-black">{{ $eval->tipoEvaluacion->nombre }}</p>
                                <p class="text-[10px] font-bold text-[#2F3E5C]/50">{{ $eval->personalSalud->nombre ?? 'Especialista' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[11px] font-black">{{ $eval->puntaje_total }} / {{ $eval->puntaje_maximo }} pts</span>
                                    <span class="inline-flex w-fit items-center rounded-full px-2 py-0.5 text-[9px] font-black {{ $colorRiesgo }}">
                                        RIESGO {{ $riesgo }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-semibold text-[#2F3E5C]/65 line-clamp-1" title="{{ $eval->resultado_interpretacion }}">
                                    {{ $eval->resultado_interpretacion }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    <button type="button" @click="abrir('evaluacion', @js($eval), false, true)" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                        <i class="ph-bold ph-eye"></i>
                                    </button>
                                    @can('adulto_mayor.eliminar')
                                    <form action="{{ route('admin.adultos-mayores.evaluaciones.destroy', [$idAdulto, $eval->cod_eval_cog]) }}" method="POST" onsubmit="confirmarAccion(event, 'Anular evaluación cognitiva', 'La evaluación dejará de ser parte del seguimiento activo, pero el puntaje histórico se conserva.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Anular evaluación" class="rounded-lg bg-terracota/5 p-2 text-terracota hover:bg-terracota hover:text-white transition">
                                            <i class="ph-bold ph-x-circle"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs font-bold text-[#2F3E5C]/40">Sin evaluaciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Evaluaciones Anuladas --}}
    @if(count($evaluacionesAnuladas) > 0)
    <div class="mt-8 border-t border-[#D5C7B9]/30 pt-6">
        <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-terracota/60 flex items-center gap-2">
            <i class="ph-bold ph-x-circle"></i> Historial de Evaluaciones Anuladas
        </h4>
        <div class="overflow-hidden rounded-[24px] border border-[#D5C7B9]/50 opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-[#E7DDD2]/40 backdrop-blur-sm shadow-sm">
            <table class="w-full text-left text-sm text-[#2F3E5C]">
                <thead class="bg-[#D5C7B9]/30 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                    <tr>
                        <th class="px-6 py-3">Fecha Anul.</th>
                        <th class="px-6 py-3">Evaluación / Puntaje</th>
                        <th class="px-6 py-3 text-right">Trazabilidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/20">
                    @foreach($evaluacionesAnuladas as $evalAnu)
                        <tr class="hover:bg-white/10 transition">
                            <td class="px-6 py-3 text-xs font-black text-[#2F3E5C]/60">
                                {{ $evalAnu->deleted_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-[11px] font-black text-[#2F3E5C]/70 uppercase">{{ $evalAnu->tipoEvaluacion->nombre }}</p>
                                <p class="text-[10px] font-semibold text-[#2F3E5C]/50">Puntaje: {{ $evalAnu->puntaje_total }} pts</p>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <form action="{{ route('admin.adultos-mayores.evaluaciones.restore', [$adulto->cod_am, $evalAnu->cod_eval_cog]) }}" method="POST" onsubmit="confirmarAccion(event, 'Restaurar evaluación', 'La evaluación cognitiva volverá a formar parte de las métricas activas.')">
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
        <p class="mt-2 text-[10px] font-bold text-[#2F3E5C]/40 italic">* Estos registros no se consideran en las métricas activas del paciente.</p>
    </div>
    @endif
    </div>
</section>
</section>

            