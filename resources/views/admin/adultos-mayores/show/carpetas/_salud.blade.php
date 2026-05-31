<div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between border-b border-[#CBBBAA]/30 pb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#C45F4B]/10 text-[#C45F4B]">
                <i class="ph-bold ph-heartbeat text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-[#2F3E5C]">Salud y Cuidados Resumida</h2>
                <p class="text-xs font-bold text-[#2F3E5C]/50 uppercase tracking-wide">Resumen médico institucional</p>
            </div>
        </div>
    </div>

    @if($fichasMedicas->isNotEmpty() || $signosVitales->count() > 0 || $medicaciones->count() > 0 || $valoracionesFuncionales->count() > 0)
        <div class="space-y-6">
            
            {{-- Ficha Médica --}}
            <div>
                <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-3">Ficha Médica</span>
                @if($fichasMedicas->isNotEmpty())
                    @php $ficha = $fichasMedicas->first(); @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="rounded-xl bg-[#F2EBE3]/50 p-3 border border-[#CBBBAA]/30">
                            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Última Act.</span>
                            <p class="text-xs font-black text-[#2F3E5C]">{{ $ficha->updated_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="rounded-xl bg-[#F2EBE3]/50 p-3 border border-[#CBBBAA]/30">
                            <span class="block text-xs font-black uppercase tracking-wide text-red-600/70 mb-1">Alergias</span>
                            <p class="text-xs font-black text-[#2F3E5C] truncate">{{ $ficha->alergias ?: 'Ninguna' }}</p>
                        </div>
                        <div class="rounded-xl bg-[#F2EBE3]/50 p-3 border border-[#CBBBAA]/30">
                            <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Enfermedades</span>
                            <p class="text-xs font-black text-[#2F3E5C] truncate">{{ $ficha->enfermedades_preexistentes ?: 'Ninguna' }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-xs font-bold text-[#2F3E5C]/40">No hay ficha médica base registrada.</p>
                @endif
            </div>

            {{-- Signos Vitales --}}
            <div>
                <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-3">Último Control de Signos Vitales</span>
                @if($signosVitales->count() > 0)
                    @php $ultimoSigno = $signosVitales->first(); @endphp
                    <div class="flex flex-wrap items-center gap-4 rounded-xl border border-[#D5C7B9]/60 bg-[#F2EBE3]/30 p-4">
                        <div>
                            <span class="block text-xs font-bold text-[#2F3E5C]/40 uppercase">Fecha</span>
                            <p class="text-sm font-black text-[#2F3E5C]">{{ \Carbon\Carbon::parse($ultimoSigno->fecha)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-[#2F3E5C]/40 uppercase">PA</span>
                            <p class="text-sm font-black text-[#2F3E5C]">{{ $ultimoSigno->presion_arterial ?? '--' }}</p>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-[#2F3E5C]/40 uppercase">FC</span>
                            <p class="text-sm font-black text-[#2F3E5C]">{{ $ultimoSigno->frecuencia_cardiaca ?? '--' }} <span class="text-xs font-normal">bpm</span></p>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-[#2F3E5C]/40 uppercase">FR</span>
                            <p class="text-sm font-black text-[#2F3E5C]">{{ $ultimoSigno->frecuencia_respiratoria ?? '--' }} <span class="text-xs font-normal">rpm</span></p>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-[#2F3E5C]/40 uppercase">Temp</span>
                            <p class="text-sm font-black text-[#2F3E5C]">{{ $ultimoSigno->temperatura ?? '--' }} <span class="text-xs font-normal">°C</span></p>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-[#2F3E5C]/40 uppercase">SpO2</span>
                            <p class="text-sm font-black text-[#2F3E5C]">{{ $ultimoSigno->saturacion_oxigeno ?? '--' }} <span class="text-xs font-normal">%</span></p>
                        </div>
                    </div>
                @else
                    <p class="text-xs font-bold text-[#2F3E5C]/40">No hay controles de signos vitales recientes.</p>
                @endif
            </div>

            {{-- Medicación y Valoración --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-3">Medicación Activa</span>
                    @if($medicaciones->count() > 0)
                        <div class="rounded-xl border border-[#D5C7B9]/60 bg-white p-4">
                            <p class="text-2xl font-black text-[#617453]">{{ $medicaciones->count() }} <span class="text-xs font-bold text-[#2F3E5C]/50">prescripciones</span></p>
                            <p class="text-xs font-bold text-[#2F3E5C]/50 mt-1">Suministro activo controlado.</p>
                        </div>
                    @else
                        <p class="text-xs font-bold text-[#2F3E5C]/40">Sin medicación activa.</p>
                    @endif
                </div>

                <div>
                    <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-3">Valoración Funcional (Barthel)</span>
                    @if($valoracionesFuncionales->count() > 0)
                        @php $ultimaVal = $valoracionesFuncionales->first(); @endphp
                        <div class="rounded-xl border border-[#D5C7B9]/60 bg-white p-4">
                            <p class="text-lg font-black text-amber-600">{{ $ultimaVal->resultado_dependencia ?? 'N/D' }}</p>
                            <p class="text-xs font-bold text-[#2F3E5C]/50 mt-1">Puntaje: {{ $ultimaVal->puntaje_total ?? '--' }}/100</p>
                        </div>
                    @else
                        <p class="text-xs font-bold text-[#2F3E5C]/40">Sin valoración funcional.</p>
                    @endif
                </div>
            </div>

        </div>
    @else
        <div class="py-10 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#F2EBE3]/50 text-[#2F3E5C]/30 mb-4">
                <i class="ph-bold ph-heartbeat text-3xl"></i>
            </div>
            <p class="text-sm font-bold text-[#2F3E5C]/60">No existe información de salud registrada.</p>
            <p class="mt-2 text-xs font-bold text-[#2F3E5C]/40 uppercase tracking-wide">Gestione desde el módulo correspondiente.</p>
        </div>
    @endif
</div>
