{{-- TAB EVALUACIONES GERIÁTRICAS INTEGRALES --}}
<section
    x-show="tab === 'evaluaciones'"
    x-transition.opacity.duration.250ms
    class="space-y-6"
    x-data="{
        areaFiltro: 'TODAS',
        verModal: false,
        evalDetalle: {}
    }"
>
    {{-- Livewire Modal de Registro --}}
    <livewire:admin.adultos-mayores.evaluaciones.evaluacion-geriatrica-modal :cod_am="$idAdulto" />

    {{-- Encabezado --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 border-b border-[#D5C7B9] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#5B5F97]">
                    Suite Geriátrica Integral
                </span>
                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                    Evaluaciones Geriátricas Integrales
                </h2>
                <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/55">
                    Historial y registro de escalas e instrumentos clínicos para el diagnóstico geriátrico multidimensional.
                </p>
            </div>

            <button type="button"
                    @click="$dispatch('evaluacion-geriatrica-abrir', { cod_am: '{{ $idAdulto }}' })"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#5B5F97] px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(91,95,151,0.18)] transition hover:-translate-y-0.5 hover:bg-[#4A4E80] active:scale-[0.98]">
                <i class="ph-bold ph-plus-circle text-lg"></i>
                Registrar Evaluación
            </button>
        </div>

        {{-- Métricas Reales --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Total Evaluaciones</p>
                <p class="mt-2 text-2xl font-black text-[#5B5F97]">{{ $evaluacionesGeriatricasActivas->count() }}</p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Alertas Críticas</p>
                <p class="mt-2 text-2xl font-black text-red-600">
                    {{ $evaluacionesGeriatricasActivas->where('nivel_alerta', 'CRITICO')->count() }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Alertas Preventivas</p>
                <p class="mt-2 text-2xl font-black text-[#D9A27C]">
                    {{ $evaluacionesGeriatricasActivas->where('nivel_alerta', 'PREVENTIVO')->count() }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">Último Registro</p>
                <p class="mt-2 text-xs font-black text-[#2F3E5C] truncate">
                    @if($evaluacionesGeriatricasActivas->count() > 0)
                        {{ $evaluacionesGeriatricasActivas->first()->instrumento->nombre }} 
                        <span class="block text-[10px] font-bold text-[#2F3E5C]/50">{{ $evaluacionesGeriatricasActivas->first()->fecha_eval->format('d/m/Y') }}</span>
                    @else
                        Ninguna registrada
                    @endif
                </p>
            </div>
        </div>
    </section>

    {{-- Filtros Rápidos por Área (Alpine.js) --}}
    <div class="flex flex-wrap gap-2">
        <button type="button" @click="areaFiltro = 'TODAS'"
                :class="areaFiltro === 'TODAS' ? 'bg-[#5B5F97] text-white' : 'bg-white/80 text-azul-profundo hover:bg-[#F2EBE3]'"
                class="rounded-xl px-4 py-2 text-xs font-black border border-[#C7B5A3]/40 shadow-sm transition">
            Todas las Áreas
        </button>
        @foreach($areasGeriatricas as $area)
            <button type="button" @click="areaFiltro = '{{ $area->cod_area }}'"
                    :class="areaFiltro === '{{ $area->cod_area }}' ? 'bg-[#5B5F97] text-white' : 'bg-white/80 text-azul-profundo hover:bg-[#F2EBE3]'"
                    class="rounded-xl px-4 py-2 text-xs font-black border border-[#C7B5A3]/40 shadow-sm transition">
                {{ $area->nombre }}
            </button>
        @endforeach
    </div>

    {{-- Listado Principal --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
        <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#5B5F97]/15 text-[#5B5F97]">
                    <i class="ph-bold ph-folder-user text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-[#2F3E5C]">Suite de Instrumentos Clínicos</h3>
                    <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Historial acumulado del residente</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2F3E5C]">
                <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60 border-b border-[#D5C7B9]/40">
                    <tr>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Área</th>
                        <th class="px-6 py-3">Instrumento</th>
                        <th class="px-6 py-3">Resultado</th>
                        <th class="px-6 py-3">Nivel de Alerta</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/30">
                    @forelse($evaluacionesGeriatricasActivas as $eg)
                        @php
                            $alerta = strtoupper($eg->nivel_alerta);
                            $colorAlerta = match($alerta) {
                                'CRITICO' => 'bg-red-600/15 text-red-600 border border-red-600/30',
                                'PREVENTIVO' => 'bg-[#D9A27C]/15 text-[#9B6D4C] border border-[#D9A27C]/30',
                                default => 'bg-[#8EA17D]/15 text-[#617453] border border-[#8EA17D]/30',
                            };
                            $alertaLabel = match($alerta) {
                                'CRITICO' => 'Crítico',
                                'PREVENTIVO' => 'Preventivo',
                                default => 'Normal',
                            };
                        @endphp
                        <tr class="group transition hover:bg-[#F2EBE3]/40" 
                            x-show="areaFiltro === 'TODAS' || '{{ $eg->instrumento->cod_area }}' === areaFiltro">
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-black">
                                {{ $eg->fecha_eval->format('d/m/Y') }}
                                @if($eg->hora_eval)
                                    <span class="block text-[10px] font-bold text-[#2F3E5C]/40">{{ \Carbon\Carbon::parse($eg->hora_eval)->format('H:i') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-black text-azul-profundo/70 bg-[#D5C7B9]/30 px-2 py-0.5 rounded-lg border border-[#C7B5A3]/25">
                                    {{ $eg->instrumento->area->nombre }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-black">{{ $eg->instrumento->nombre }}</p>
                                <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-wider">{{ $eg->instrumento->siglas }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-xs font-black">
                                        @if($eg->instrumento->tipo_resultado === 'TIEMPO')
                                            {{ $eg->puntaje_total }} seg.
                                        @elseif($eg->puntaje_total !== null)
                                            {{ $eg->puntaje_total }} 
                                            @if($eg->instrumento->puntaje_maximo)
                                                <span class="text-[10px] font-bold text-azul-profundo/40">/ {{ number_format($eg->instrumento->puntaje_maximo, 0) }} pts</span>
                                            @else
                                                pts
                                            @endif
                                        @else
                                            --
                                        @endif
                                    </span>
                                    @if($eg->categoria_resultado)
                                        <span class="text-[10px] font-bold text-azul-profundo/60 italic">{{ $eg->categoria_resultado }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $colorAlerta }}">
                                    {{ $alertaLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    {{-- Ver Detalles (Alpine.js Modal trigger) --}}
                                    <button type="button" 
                                            @click="verModal = true; evalDetalle = {{ $eg->toJson() }}; evalDetalle.instrumento = {{ $eg->instrumento->toJson() }}; evalDetalle.registrador = {{ $eg->registrador->toJson() }}; evalDetalle.fecha_formateada = '{{ $eg->fecha_eval->format('d/m/Y') }}';" 
                                            title="Ver detalles" 
                                            class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                        <i class="ph-bold ph-eye"></i>
                                    </button>

                                    {{-- PDF --}}
                                    <a href="{{ route('admin.adultos-mayores.evaluaciones-geriatricas.pdf', [$idAdulto, $eg->cod_eval_ger]) }}" 
                                       target="_blank" 
                                       title="Imprimir PDF" 
                                       class="rounded-lg bg-[#5B5F97]/5 p-2 text-[#5B5F97] hover:bg-[#5B5F97] hover:text-white transition">
                                        <i class="ph-bold ph-file-pdf"></i>
                                    </a>

                                    {{-- Anular con SweetAlert2 --}}
                                    @can('adulto_mayor.eliminar')
                                        <button type="button" 
                                                @click="confirmarAnulacion('{{ $eg->cod_eval_ger }}')" 
                                                title="Anular registro" 
                                                class="rounded-lg bg-terracota/5 p-2 text-terracota hover:bg-terracota hover:text-white transition">
                                            <i class="ph-bold ph-trash"></i>
                                        </button>
                                        <form id="form-anular-{{ $eg->cod_eval_ger }}" 
                                              action="{{ route('admin.adultos-mayores.evaluaciones-geriatricas.anular', [$idAdulto, $eg->cod_eval_ger]) }}" 
                                              method="POST" 
                                              style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="motivo_anulacion" id="motivo-{{ $eg->cod_eval_ger }}">
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-xs font-bold text-[#2F3E5C]/40">No se encontraron evaluaciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Evaluaciones Anuladas Lógicamente --}}
    @if($evaluacionesGeriatricasAnuladas->count() > 0)
        <div class="mt-8 border-t border-[#D5C7B9]/30 pt-6">
            <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-terracota/60 flex items-center gap-2">
                <i class="ph-bold ph-x-circle"></i> Historial de Evaluaciones Anuladas
            </h4>
            <div class="overflow-hidden rounded-[24px] border border-[#D5C7B9]/50 opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-[#E7DDD2]/40 backdrop-blur-sm shadow-sm">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/30 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                        <tr>
                            <th class="px-6 py-3">Fecha Anul.</th>
                            <th class="px-6 py-3">Instrumento / Resultado</th>
                            <th class="px-6 py-3">Motivo de Anulación</th>
                            <th class="px-6 py-3 text-right">Responsable</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/20">
                        @foreach($evaluacionesGeriatricasAnuladas as $ega)
                            <tr class="hover:bg-white/10 transition">
                                <td class="px-6 py-3 text-xs font-black text-[#2F3E5C]/60 whitespace-nowrap">
                                    {{ $ega->anulado_en ? $ega->anulado_en->format('d/m/Y H:i') : '--' }}
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-[11px] font-black text-[#2F3E5C]/70 uppercase">{{ $ega->instrumento->nombre }}</p>
                                    <p class="text-[10px] font-semibold text-[#2F3E5C]/50">Puntaje: {{ $ega->puntaje_total ?? '--' }} pts</p>
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-[11px] font-bold text-red-700/80 italic leading-relaxed">{{ $ega->motivo_anulacion }}</p>
                                </td>
                                <td class="px-6 py-3 text-right whitespace-nowrap">
                                    <span class="text-[10px] font-black bg-[#D5C7B9]/40 text-azul-profundo/80 px-2 py-0.5 rounded-md">
                                        {{ $ega->anulador->nombre ?? 'Sistema' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TABLA LEGADO DE EVALUACIONES COGNITIVAS (MOCA / MMSE ANTIGUOS) --}}
    <div class="mt-8 pt-6 border-t border-[#D5C7B9]/30" x-data="{ legacyOpen: false }">
        <button type="button" @click="legacyOpen = !legacyOpen" class="w-full flex items-center justify-between bg-[#D5C7B9]/15 hover:bg-[#D5C7B9]/30 border border-[#C7B5A3]/40 px-5 py-3 rounded-2xl transition">
            <span class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]/70 flex items-center gap-2">
                <i class="ph-bold ph-brain text-base"></i> Historial de Evaluaciones Cognitivas Previas (Legado)
            </span>
            <i class="ph-bold text-base transition-transform duration-300" :class="legacyOpen ? 'ph-caret-up rotate-180' : 'ph-caret-down'"></i>
        </button>

        <div x-cloak x-show="legacyOpen" x-collapse class="mt-4 space-y-4">
            <div class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/70 shadow-inner">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/40 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50 border-b border-[#D5C7B9]/40">
                        <tr>
                            <th class="px-6 py-2">Fecha</th>
                            <th class="px-6 py-2">Tipo</th>
                            <th class="px-6 py-2">Resultado / Riesgo</th>
                            <th class="px-6 py-2">Interpretación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/20">
                        @forelse($evaluacionesActivas as $eval)
                            @php
                                $riesgo = strtoupper($eval->nivel_riesgo ?? 'NORMAL');
                                $colorRiesgo = match($riesgo) {
                                    'ALTO' => 'bg-terracota/10 text-terracota',
                                    'MODERADO' => 'bg-[#D9A27C]/10 text-[#9B6D4C]',
                                    default => 'bg-[#8EA17D]/10 text-[#617453]',
                                };
                            @endphp
                            <tr class="hover:bg-[#F2EBE3]/20 text-xs">
                                <td class="px-6 py-2.5 font-black">{{ $eval->fecha_eval->format('d/m/Y') }}</td>
                                <td class="px-6 py-2.5 font-bold">{{ $eval->tipoEvaluacion->nombre }}</td>
                                <td class="px-6 py-2.5">
                                    <span class="font-black mr-2">{{ $eval->puntaje_total }} pts</span>
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black {{ $colorRiesgo }}">RIESGO {{ $riesgo }}</span>
                                </td>
                                <td class="px-6 py-2.5 text-[#2F3E5C]/60 truncate max-w-[200px]">{{ $eval->resultado_interpretacion }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-xs font-bold text-[#2F3E5C]/40">Sin evaluaciones heredadas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL DETALLE DE EVALUACIÓN (Alpine.js) --}}
    <div x-cloak x-show="verModal" class="fixed inset-0 z-[2147483647] flex items-center justify-center overflow-y-auto bg-slate-900/40 p-4 backdrop-blur-sm"
         x-transition.opacity>
        <div class="relative w-full max-w-lg rounded-[24px] border border-[#C7B5A3] bg-[#E6DDD3] shadow-2xl p-6 sm:p-8 space-y-4"
             @click.away="verModal = false">
            <div class="flex items-center justify-between border-b border-[#C7B5A3]/30 pb-3">
                <div class="flex items-center gap-2.5">
                    <i class="ph-fill ph-brain text-2xl text-terracota"></i>
                    <div>
                        <span class="text-[9px] font-black uppercase text-terracota/80">Detalles Clínicos</span>
                        <h3 class="text-sm font-black text-azul-profundo" x-text="evalDetalle.instrumento?.nombre"></h3>
                    </div>
                </div>
                <button type="button" @click="verModal = false" class="text-azul-profundo/60 hover:text-terracota transition">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <div class="space-y-3.5 text-xs font-semibold text-azul-profundo/85">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/50 p-2.5 rounded-xl border border-[#C7B5A3]/20 shadow-sm">
                        <span class="text-[10px] font-black text-azul-profundo/40 uppercase block mb-0.5">Fecha y Hora</span>
                        <span class="font-black text-azul-profundo" x-text="evalDetalle.fecha_formateada"></span>
                        <span class="text-[11px]" x-show="evalDetalle.hora_eval" x-text="' (' + evalDetalle.hora_eval?.substring(0, 5) + ')'"></span>
                    </div>
                    <div class="bg-white/50 p-2.5 rounded-xl border border-[#C7B5A3]/20 shadow-sm">
                        <span class="text-[10px] font-black text-azul-profundo/40 uppercase block mb-0.5">Nivel de Alerta</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider inline-block mt-0.5"
                              :class="evalDetalle.nivel_alerta === 'CRITICO' ? 'bg-red-600/10 text-red-600 border border-red-600/20' : 
                                      (evalDetalle.nivel_alerta === 'PREVENTIVO' ? 'bg-[#D9A27C]/10 text-[#9B6D4C] border border-[#D9A27C]/20' : 
                                                                                   'bg-[#8EA17D]/10 text-[#617453] border border-[#8EA17D]/20')"
                              x-text="evalDetalle.nivel_alerta === 'CRITICO' ? 'Crítico' : (evalDetalle.nivel_alerta === 'PREVENTIVO' ? 'Preventivo' : 'Normal')">
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/50 p-2.5 rounded-xl border border-[#C7B5A3]/20 shadow-sm">
                        <span class="text-[10px] font-black text-azul-profundo/40 uppercase block mb-0.5">Resultado Métrico</span>
                        <span class="font-black text-azul-profundo" x-text="evalDetalle.puntaje_total !== null ? (evalDetalle.instrumento?.tipo_resultado === 'TIEMPO' ? evalDetalle.puntaje_total + ' seg.' : evalDetalle.puntaje_total + ' pts') : 'No aplica'"></span>
                    </div>
                    <div class="bg-white/50 p-2.5 rounded-xl border border-[#C7B5A3]/20 shadow-sm">
                        <span class="text-[10px] font-black text-azul-profundo/40 uppercase block mb-0.5">Resultado Clínico</span>
                        <span class="font-black text-azul-profundo" x-text="evalDetalle.categoria_resultado || 'Sin clasificar'"></span>
                    </div>
                </div>

                <div class="bg-white/50 p-3 rounded-xl border border-[#C7B5A3]/20 shadow-sm" x-show="evalDetalle.nivel_riesgo">
                    <span class="text-[10px] font-black text-azul-profundo/40 uppercase block mb-0.5">Nivel de Riesgo</span>
                    <span class="font-black text-azul-profundo" x-text="evalDetalle.nivel_riesgo"></span>
                </div>

                <div class="bg-white/50 p-3 rounded-xl border border-[#C7B5A3]/20 shadow-sm">
                    <span class="text-[10px] font-black text-azul-profundo/40 uppercase block mb-1">Observaciones Clínicas</span>
                    <p class="text-azul-profundo/70 leading-relaxed italic text-[11px]" x-text="evalDetalle.observaciones || 'Sin observaciones registradas.'"></p>
                </div>

                <div class="flex items-center justify-between text-[11px] font-bold text-[#2F3E5C]/60 pt-2 border-t border-[#C7B5A3]/30">
                    <span x-text="'Registrado por: ' + (evalDetalle.registrador?.nombre || 'Especialista')"></span>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="verModal = false" class="rounded-xl bg-[#2F3E5C] px-5 py-2 text-xs font-black uppercase text-white shadow-md hover:bg-[#1F2D4A] active:scale-95 transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

</section>

{{-- SweetAlert2 JS logic for logique deletion --}}
<script>
    function confirmarAnulacion(cod_eval_ger) {
        Swal.fire({
            title: '¿Anular esta evaluación?',
            text: 'Esta acción desactivará el registro clínicamente pero lo conservará en el historial de auditoría de Casa Amandita.',
            input: 'text',
            inputPlaceholder: 'Escribe el motivo de la anulación (mínimo 10 caracteres)...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E27D60',
            cancelButtonColor: '#2F3E5C',
            confirmButtonText: 'Sí, anular evaluación',
            cancelButtonText: 'Cancelar',
            preConfirm: (value) => {
                if (!value || value.trim().length < 10) {
                    Swal.showValidationMessage('Debe ingresar un motivo válido de al menos 10 caracteres.');
                    return false;
                }
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Set value of motivo to the hidden field and submit form
                document.getElementById('motivo-' + cod_eval_ger).value = result.value;
                document.getElementById('form-anular-' + cod_eval_ger).submit();
            }
        });
    }

    // Capture standard Livewire events for SweetAlert
    document.addEventListener('livewire:init', () => {
        Livewire.on('swal:alert', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                icon: data.type,
                title: data.title,
                text: data.message,
                confirmButtonColor: '#2F3E5C'
            });
        });
    });
</script>