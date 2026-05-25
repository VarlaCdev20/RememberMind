{{-- TAB EVALUACIONES COGNITIVAS --}}
<section
    x-show="tab === 'evaluaciones'"
    style="display: none;"
    x-transition.opacity.duration.250ms
    class="space-y-6"
    x-data="{ verModal: false, evalDetalle: {} }"
>
    <!-- HEADER BLOCK -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#CBBBAA]/30 pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#5B5F97]/10 text-[#5B5F97]">
                <i class="ph-fill ph-brain text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C]">Evaluaciones Cognitivas</h2>
                <p class="text-sm font-semibold text-[#2F3E5C]/60">Historial de pruebas MoCA, MMSE y evolución cognitiva institucional.</p>
            </div>
        </div>
        
        <button type="button"
                @click="verModal = true"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#5B5F97] px-4 py-2.5 text-xs font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#4A4E80] active:scale-[0.98]">
            <i class="ph-bold ph-plus-circle text-lg"></i>
            Registrar Prueba
        </button>
    </div>

    <!-- METRICS GRID -->
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Total Pruebas</p>
            <p class="mt-2 text-2xl font-black text-[#5B5F97]">{{ $evaluacionesActivas->count() }}</p>
        </div>

        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Deterioro Detectado</p>
            <p class="mt-2 text-2xl font-black text-amber-600">
                {{ $evaluacionesActivas->whereIn('nivel_riesgo', ['MEDIO', 'ALTO'])->count() }}
            </p>
        </div>

        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Última Prueba</p>
            <p class="mt-2 text-xs font-black text-[#2F3E5C] truncate">
                @if($evaluacionesActivas->count() > 0)
                    {{ $evaluacionesActivas->first()->tipoEvaluacion->nombre }}
                    <span class="block text-[10px] font-bold text-[#2F3E5C]/50">{{ $evaluacionesActivas->first()->fecha_eval->format('d/m/Y') }}</span>
                @else
                    Ninguna registrada
                @endif
            </p>
        </div>

        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Evolución (Promedio)</p>
            <p class="mt-2 text-xl font-black text-[#2F3E5C]">
                @if($evaluacionesActivas->count() > 0)
                    {{ number_format($evaluacionesActivas->avg('puntaje_total'), 1) }} pts
                @else
                    --
                @endif
            </p>
        </div>
    </div>

    {{-- Listado Principal --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-[#CBBBAA]/60 text-[#5B5F97]">
                    <i class="ph-bold ph-activity text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-[#2F3E5C]">Historial Cognitivo</h3>
                    <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Cronología de resultados clínicos</p>
                </div>
            </div>
            
            <a href="{{ route('admin.adultos-mayores.reporte-especifico', ['adulto_mayor' => $idAdulto, 'tipo' => 'cognitivo', 'format' => 'pdf']) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white border border-[#2F3E5C]/20 px-3 py-1.5 text-xs font-black text-[#2F3E5C] hover:bg-[#2F3E5C]/5 transition">
                <i class="ph-bold ph-printer"></i> Reporte PDF
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2F3E5C]">
                <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60 border-b border-[#D5C7B9]/40">
                    <tr>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Tipo de Prueba</th>
                        <th class="px-6 py-3">Resultado</th>
                        <th class="px-6 py-3">Interpretación Clínica</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/30">
                    @forelse($evaluacionesActivas as $eval)
                        @php
                            $riesgo = strtoupper($eval->nivel_riesgo ?? 'NORMAL');
                            $colorRiesgo = match($riesgo) {
                                'ALTO' => 'bg-red-50 text-red-700 border-red-200',
                                'MEDIO', 'MODERADO' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            };
                        @endphp
                        <tr class="group transition hover:bg-white/50">
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-[#2F3E5C]/80">
                                {{ $eval->fecha_eval->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-black text-[#5B5F97] bg-[#5B5F97]/10 px-2 py-0.5 rounded-lg border border-[#5B5F97]/20">
                                    {{ $eval->tipoEvaluacion->nombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-black">{{ $eval->puntaje_total }} <span class="text-[10px] text-[#2F3E5C]/40">/ {{ $eval->tipoEvaluacion->puntaje_maximo ?? 30 }}</span></span>
                                    <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border {{ $colorRiesgo }}">
                                        {{ $riesgo }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-[#2F3E5C]/80">{{ $eval->resultado_interpretacion }}</p>
                                @if($eval->observaciones)
                                    <p class="text-[10px] text-[#2F3E5C]/50 italic mt-0.5 truncate max-w-xs">"{{ $eval->observaciones }}"</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    {{-- Anular --}}
                                    @can('adulto_mayor.eliminar')
                                        <form action="{{ route('admin.adultos-mayores.evaluaciones.destroy', [$idAdulto, $eval->cod_eval_cogni]) }}" method="POST" onsubmit="confirmarAccion(event, 'Anular Prueba', 'El registro será movido a anulados lógicos.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Anular registro" class="rounded-lg bg-rose-50 border border-rose-200/50 p-2 text-rose-700 hover:bg-rose-700 hover:text-white transition">
                                                <i class="ph-bold ph-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm font-bold text-[#2F3E5C]/40">
                                <i class="ph-fill ph-brain text-4xl text-[#C7B5A3] mb-2 block"></i>
                                No se encontraron pruebas cognitivas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Anulados --}}
    @if($evaluacionesAnuladas->count() > 0)
        <div class="mt-8 border-t border-[#D5C7B9]/30 pt-6" x-data="{ open: false }">
            <button @click="open = !open" type="button" class="flex items-center gap-2 text-xs font-black uppercase tracking-widest text-[#2F3E5C]/50 hover:text-[#2F3E5C]/80 transition">
                <i class="ph-bold ph-archive text-sm"></i> Ver Registros Anulados (<span x-text="open ? 'Ocultar' : 'Mostrar'"></span>)
            </button>
            <div x-show="open" x-cloak x-collapse class="mt-4">
                <div class="overflow-hidden rounded-[24px] border border-[#D5C7B9]/50 opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-[#E7DDD2]/40 backdrop-blur-sm shadow-sm">
                    <table class="w-full text-left text-sm text-[#2F3E5C]">
                        <thead class="bg-[#D5C7B9]/30 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                            <tr>
                                <th class="px-6 py-3">Fecha Anul.</th>
                                <th class="px-6 py-3">Prueba y Resultado</th>
                                <th class="px-6 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D5C7B9]/20">
                            @foreach($evaluacionesAnuladas as $ea)
                                <tr class="hover:bg-white/10 transition">
                                    <td class="px-6 py-3 text-xs font-black text-[#2F3E5C]/60 whitespace-nowrap">
                                        {{ $ea->deleted_at ? $ea->deleted_at->format('d/m/Y H:i') : '--' }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <p class="text-[11px] font-black text-[#2F3E5C]/70 uppercase">{{ $ea->tipoEvaluacion->nombre }}</p>
                                        <p class="text-[10px] font-semibold text-[#2F3E5C]/50">Puntaje: {{ $ea->puntaje_total }} pts ({{ $ea->resultado_interpretacion }})</p>
                                    </td>
                                    <td class="px-6 py-3 text-right whitespace-nowrap">
                                        <form action="{{ route('admin.adultos-mayores.evaluaciones.restore', [$idAdulto, $ea->cod_eval_cogni]) }}" method="POST" class="inline-block" onsubmit="confirmarAccion(event, 'Restaurar Evaluación', 'Volverá al historial activo.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="rounded-lg bg-emerald-50 border border-emerald-200/50 p-1.5 text-emerald-700 hover:bg-emerald-700 hover:text-white transition">
                                                <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
    
    {{-- MODAL DE REGISTRO COGNITIVO (Formulario estándar Alpine) --}}
    <div x-cloak x-show="verModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-[#2F3E5C]/40 backdrop-blur-sm p-4" x-transition.opacity>
        <div class="relative w-full max-w-lg rounded-[24px] bg-[#EBE3DB] shadow-2xl p-6" @click.away="verModal = false">
            <div class="flex items-center justify-between mb-4 border-b border-[#CBBBAA]/40 pb-3">
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#5B5F97]/10 text-[#5B5F97]">
                        <i class="ph-fill ph-brain text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-[#2F3E5C]">Registrar Prueba</h3>
                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Evaluación Cognitiva</p>
                    </div>
                </div>
                <button type="button" @click="verModal = false" class="text-[#2F3E5C]/50 hover:text-[#2F3E5C]">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.adultos-mayores.evaluaciones.store', $idAdulto) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Tipo de Prueba</label>
                    <select name="cod_tipo_eval" required class="w-full rounded-xl border border-[#CBBBAA]/60 bg-white px-3 py-2 text-sm font-semibold text-[#2F3E5C] focus:border-[#5B5F97] focus:ring focus:ring-[#5B5F97]/20">
                        <option value="">Seleccione una prueba...</option>
                        @foreach($tiposEvaluaciones ?? [] as $tipo)
                            <option value="{{ $tipo->cod_tipo_eval }}">{{ $tipo->nombre }} (Máx. {{ $tipo->puntaje_maximo ?? 30 }} pts)</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Fecha</label>
                        <input type="date" name="fecha_eval" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-[#CBBBAA]/60 bg-white px-3 py-2 text-sm font-semibold text-[#2F3E5C] focus:border-[#5B5F97] focus:ring focus:ring-[#5B5F97]/20">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Puntaje Total</label>
                        <input type="number" name="puntaje_total" required min="0" step="0.5" class="w-full rounded-xl border border-[#CBBBAA]/60 bg-white px-3 py-2 text-sm font-semibold text-[#2F3E5C] focus:border-[#5B5F97] focus:ring focus:ring-[#5B5F97]/20">
                    </div>
                </div>
                
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60 mb-1">Observaciones / Notas</label>
                    <textarea name="observaciones" rows="3" class="w-full rounded-xl border border-[#CBBBAA]/60 bg-white px-3 py-2 text-sm font-semibold text-[#2F3E5C] focus:border-[#5B5F97] focus:ring focus:ring-[#5B5F97]/20" placeholder="Anotaciones sobre el desempeño, cooperación o condiciones..."></textarea>
                </div>
                
                <div class="flex justify-end gap-2 pt-4 border-t border-[#CBBBAA]/30">
                    <button type="button" @click="verModal = false" class="rounded-xl bg-white px-4 py-2 text-xs font-black text-[#2F3E5C] shadow-sm border border-[#CBBBAA]/50 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-xl bg-[#5B5F97] px-5 py-2 text-xs font-black text-white shadow-sm hover:bg-[#4A4E80]">
                        Guardar Prueba
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>