<div>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <nav class="mb-2 flex text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.salud-seguimiento.index') }}" class="hover:text-[#2F3E5C]">Salud y Seguimiento</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="ph-bold ph-caret-right mx-1"></i>
                            <span class="text-[#2F3E5C]/70">Evaluaciones Geriátricas</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C] flex items-center gap-2">
                <i class="ph-bold ph-list-magnifying-glass text-[#6873A6]"></i> Evaluaciones Geriátricas
            </h2>
            <p class="mt-1 text-sm font-semibold text-[#2F3E5C]/60">
                Valoración integral geriátrica, riesgo funcional, nutricional y seguimiento preventivo.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.adultos-mayores.show', $adulto->cod_am) }}" class="inline-flex items-center gap-2 rounded-xl border border-[#CBBBAA]/50 bg-white px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#F2EBE3]">
                <i class="ph-bold ph-arrow-left"></i> Volver a Expediente
            </a>
            <button type="button" @click="$dispatch('evaluacion-geriatrica-abrir', { cod_am: '{{ $adulto->cod_am }}' })" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#6873A6] px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#566189] active:scale-95">
                <i class="ph-bold ph-plus-circle text-sm"></i> Registrar Evaluación
            </button>
        </div>
    </div>

    <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-[#CBBBAA]/30 bg-[#F2EBE3]/30 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#6873A6]/10 text-[#6873A6]">
                    <i class="ph-bold ph-user-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Adulto Mayor</p>
                    <h3 class="text-sm font-black text-[#2F3E5C]">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</h3>
                </div>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#6873A6]/10 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-[#6873A6]">
                    {{ count($evaluaciones) }} Evaluaciones
                </span>
            </div>
        </div>

        <div class="p-6">
            @if(count($evaluaciones) > 0)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($evaluaciones as $eval)
                        <div class="rounded-xl border border-[#CBBBAA]/40 bg-[#F2EBE3]/20 p-4 transition hover:border-[#6873A6]/30 hover:bg-[#F2EBE3]/40">
                            <div class="mb-3 flex items-start justify-between gap-2 border-b border-[#CBBBAA]/20 pb-3">
                                <div>
                                    <h4 class="text-sm font-black text-[#2F3E5C] leading-tight">{{ $eval->instrumento->nombre ?? 'Evaluación Geriátrica' }}</h4>
                                    <p class="text-[10px] font-bold text-[#2F3E5C]/50 mt-1">{{ \Carbon\Carbon::parse($eval->fecha_eval)->format('d M Y') }}</p>
                                </div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#6873A6]/10 text-[#6873A6] shrink-0">
                                    <i class="ph-bold ph-file-text"></i>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-xs">
                                    <span class="font-bold text-[#2F3E5C]/60">Puntaje Total</span>
                                    <span class="font-black text-[#2F3E5C]">{{ $eval->puntaje_total ?? 'N/D' }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="font-bold text-[#2F3E5C]/60">Nivel de Alerta</span>
                                    <span class="font-black {{ $eval->nivel_alerta === 'CRITICO' ? 'text-red-600' : ($eval->nivel_alerta === 'PRECAUCION' ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $eval->nivel_alerta ?? 'N/D' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-[#CBBBAA]/20 flex justify-end gap-2">
                                <button type="button" @click="$dispatch('evaluacion-geriatrica-abrir', { cod_am: '{{ $adulto->cod_am }}', eval_id: {{ $eval->id }} })" class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-[#CBBBAA]/50 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#F2EBE3]">
                                    <i class="ph-bold ph-pencil-simple"></i> Editar
                                </button>
                                <!-- TODO: Implementar visor detallado si existe -->
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#6873A6]/10 text-[#6873A6]">
                        <i class="ph-bold ph-list-magnifying-glass text-3xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-black text-[#2F3E5C]">No existen evaluaciones geriátricas registradas.</h3>
                    <p class="mt-2 text-sm font-semibold text-[#2F3E5C]/60 max-w-md">
                        Comience registrando la primera valoración multidimensional para llevar el seguimiento preventivo del adulto mayor.
                    </p>
                    <button type="button" @click="$dispatch('evaluacion-geriatrica-abrir', { cod_am: '{{ $adulto->cod_am }}' })" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-[#6873A6] px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-md transition hover:-translate-y-0.5 hover:bg-[#566189] active:scale-95">
                        <i class="ph-bold ph-plus-circle text-sm"></i> Registrar Evaluación
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Component --}}
    <livewire:admin.adultos-mayores.evaluaciones.evaluacion-geriatrica-modal :cod_am="$adulto->cod_am" />
</div>
