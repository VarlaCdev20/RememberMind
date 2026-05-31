{{-- TAB SALUD Y CUIDADOS --}}
<section
    x-show="tab === 'salud'"
    style="display: none;"
    x-transition.opacity.duration.250ms
    class="space-y-6"
>
    <!-- AESTHETICS & CUSTOM STYLES -->
    <style>
        .livewire-modals-only > div > div.rounded-\[24px\],
        .livewire-modals-only > div > div.rounded-24px {
            display: none !important;
        }
    </style>

    <!-- HEADER BLOCK -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#CBBBAA]/30 pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#C45F4B]/10 text-[#C45F4B]">
                <i class="ph-fill ph-heartbeat text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C]">Salud y Cuidados</h2>
                <p class="text-sm font-semibold text-[#2F3E5C]/60">Información de salud básica, cuidados registrados y seguimiento funcional del adulto mayor.</p>
            </div>
        </div>
        
        <!-- Botón de Historial de Estados -->
        <button 
            @click="$dispatch('abrirModalHistorialEstado', '{{ $adulto->cod_am }}')" 
            class="inline-flex items-center gap-2 rounded-xl border border-[#2F3E5C]/20 bg-white px-4 py-2.5 text-sm font-bold text-[#2F3E5C] shadow-sm hover:bg-[#2F3E5C]/5 active:scale-95 transition shrink-0"
        >
            <i class="ph-bold ph-clock-counter-clockwise text-md"></i>
            <span>Historial de Estados</span>
        </button>
    </div>

    <!-- ALERTS CENTER (SALUD) -->
    @php
        $alertasSalud = [];
        if ($fichasMedicas->isEmpty()) {
            $alertasSalud[] = [
                'tipo' => 'ficha',
                'mensaje' => 'Sin ficha médica básica registrada.',
                'icono' => 'ph-bold ph-heartbeat',
                'color' => 'bg-[#C45F4B]/10 text-[#C45F4B] border-[#C45F4B]/20',
                'accion' => "abrirModalFichaMedica"
            ];
        }
        if ($medicaciones->where('estado', 'ACTIVO')->isEmpty()) {
            $alertasSalud[] = [
                'tipo' => 'medicacion',
                'mensaje' => 'Sin medicación registrada.',
                'icono' => 'ph-bold ph-pill',
                'color' => 'bg-[#CBBBAA]/20 text-[#2F3E5C]/70 border-[#CBBBAA]/30',
                'accion' => "abrirModalMedicacion"
            ];
        }
        if ($signosVitales->isEmpty()) {
            $alertasSalud[] = [
                'tipo' => 'signos',
                'mensaje' => 'Sin registro de signos vitales.',
                'icono' => 'ph-bold ph-activity',
                'color' => 'bg-amber-50 text-amber-800 border-amber-200/50',
                'accion' => "abrirModalSignos"
            ];
        }
        if ($valoracionesFuncionales->isEmpty()) {
            $alertasSalud[] = [
                'tipo' => 'valoracion',
                'mensaje' => 'Sin valoración funcional registrada.',
                'icono' => 'ph-bold ph-wheelchair',
                'color' => 'bg-[#8EA17D]/10 text-[#617453] border-[#8EA17D]/20',
                'accion' => "abrirModalValoracion"
            ];
        }
    @endphp

    @if(count($alertasSalud) > 0)
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($alertasSalud as $alerta)
                <div class="flex items-center justify-between rounded-xl border p-3.5 shadow-sm transition bg-white {{ $alerta['color'] }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <i class="{{ $alerta['icono'] }} text-lg shrink-0"></i>
                        <span class="text-xs font-bold truncate leading-tight">{{ $alerta['mensaje'] }}</span>
                    </div>
                    <button 
                        @click="$dispatch('{{ $alerta['accion'] }}', '{{ $adulto->cod_am }}')" 
                        class="ml-2 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white/70 hover:bg-white text-xs font-black shadow-sm transition active:scale-95 shrink-0" 
                        title="Registrar ahora"
                    >
                        <i class="ph-bold ph-plus"></i>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <!-- INTERACTIVE PANEL GRID -->
    <div class="grid gap-6 lg:grid-cols-2">
        
        <!-- CARD A: FICHA MÉDICA BÁSICA -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#C45F4B]/10 text-[#C45F4B]">
                            <i class="ph-bold ph-heartbeat text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#2F3E5C]">Ficha Médica Básica</h3>
                            <p class="text-xs text-[#2F3E5C]/60">Antecedentes y condiciones generales</p>
                        </div>
                    </div>
                    <button 
                        @click="$dispatch('abrirModalFichaMedica', '{{ $adulto->cod_am }}')" 
                        class="inline-flex items-center gap-1.5 rounded-lg bg-[#C45F4B] px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-[#A34B38] active:scale-95 transition shrink-0"
                    >
                        <i class="ph-bold ph-pencil-simple"></i>
                        <span>Gestionar</span>
                    </button>
                </div>

                @if($fichasMedicas->isNotEmpty())
                    @php
                        $latestFicha = $fichasMedicas->first();
                        
                        $cronicas = [];
                        if($latestFicha->hipertension) $cronicas[] = 'Hipertensión Arterial';
                        if($latestFicha->diabetes) $cronicas[] = 'Diabetes';
                        if($latestFicha->problemas_cardiacos) $cronicas[] = 'Problemas Cardíacos';

                        $neurologicos = [];
                        if($latestFicha->acv) $neurologicos[] = 'ACV';
                        if($latestFicha->parkinson) $neurologicos[] = 'Parkinson';
                        if($latestFicha->epilepsia) $neurologicos[] = 'Epilepsia';
                        if($latestFicha->alzheimer_diagnosticado) $neurologicos[] = 'Alzheimer';

                        $emocionales = [];
                        if($latestFicha->depresion) $emocionales[] = 'Depresión';
                        if($latestFicha->ansiedad) $emocionales[] = 'Ansiedad';
                        if($latestFicha->problemas_sueno) $emocionales[] = 'Problemas de Sueño';

                        $sensoriales = [];
                        if($latestFicha->problemas_visuales) $sensoriales[] = 'Baja Visión';
                        if($latestFicha->problemas_auditivos) $sensoriales[] = 'Baja Audición';
                        if($latestFicha->dolor_cronico) $sensoriales[] = 'Dolor Crónico';
                    @endphp

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4 rounded-xl border border-[#C7B5A3]/30 bg-[#F8F2EC]/40 p-4">
                            <div>
                                <h5 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1.5">Enfermedades de Base</h5>
                                @if(count($cronicas) > 0)
                                    <div class="space-y-1.5">
                                        @foreach($cronicas as $item)
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-[#2F3E5C]">
                                                <i class="ph-bold ph-check-circle text-emerald-600 text-sm"></i>
                                                <span>{{ $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-[#2F3E5C]/40 italic">Ninguna registrada</span>
                                @endif
                            </div>

                            <div>
                                <h5 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1.5">Neurológicos</h5>
                                @if(count($neurologicos) > 0)
                                    <div class="space-y-1.5">
                                        @foreach($neurologicos as $item)
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-[#2F3E5C]">
                                                <i class="ph-bold ph-check-circle text-[#C45F4B] text-sm"></i>
                                                <span>{{ $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-[#2F3E5C]/40 italic">Ninguno registrado</span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h5 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1.5">Sensorial y Sueño</h5>
                                <div class="space-y-1">
                                    @foreach(array_merge($sensoriales, $emocionales) as $item)
                                        <span class="inline-block bg-[#2F3E5C]/5 text-[#2F3E5C] text-[10px] font-bold px-2 py-0.5 rounded-md mr-1 mb-1">
                                            {{ $item }}
                                        </span>
                                    @endforeach
                                    @if(count($sensoriales) == 0 && count($emocionales) == 0)
                                        <span class="text-xs text-[#2F3E5C]/40 italic">Sin observaciones especiales</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <h5 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1.5">Alergias y Alimentos</h5>
                                <div class="space-y-1">
                                    @if($latestFicha->alergias)
                                        <div class="text-xs font-bold text-[#2F3E5C] flex items-start gap-1">
                                            <span class="text-red-600 font-extrabold text-[10px] bg-red-50 border border-red-100 px-1 rounded shrink-0 uppercase">Alergia</span>
                                            <span class="leading-normal">{{ $latestFicha->alergias }}</span>
                                        </div>
                                    @endif
                                    @if($latestFicha->restricciones_alimentarias)
                                        <div class="text-xs font-bold text-[#2F3E5C] flex items-start gap-1">
                                            <span class="text-amber-800 font-extrabold text-[10px] bg-amber-50 border border-amber-100 px-1 rounded shrink-0 uppercase">Dieta</span>
                                            <span class="leading-normal">{{ $latestFicha->restricciones_alimentarias }}</span>
                                        </div>
                                    @endif
                                    @if(!$latestFicha->alergias && !$latestFicha->restricciones_alimentarias)
                                        <span class="text-xs text-[#2F3E5C]/40 italic">Ninguna registrada</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($latestFicha->observacion_medica)
                            <div class="rounded-xl border border-[#CBBBAA]/20 bg-[#F8F2EC]/50 p-3">
                                <h5 class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/55 mb-1">Observaciones de Salud</h5>
                                <p class="text-xs text-[#2F3E5C]/80 italic line-clamp-2 leading-relaxed">"{{ $latestFicha->observacion_medica }}"</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="my-8 text-center border-2 border-dashed border-[#C7B5A3]/40 rounded-2xl p-6 bg-[#F8F2EC]/30">
                        <i class="ph-fill ph-heartbeat text-4xl text-[#C7B5A3] mb-2"></i>
                        <p class="text-sm font-bold text-[#2F3E5C]/60">Sin ficha médica básica registrada.</p>
                        <p class="text-xs text-[#2F3E5C]/40 mt-1">Registre los antecedentes crónicos y alergias para completar la ficha.</p>
                    </div>
                @endif
            </div>

            @if($fichasMedicas->isNotEmpty())
                <div class="mt-4 border-t border-[#C7B5A3]/20 pt-3 flex justify-between items-center text-[10px] font-bold text-[#2F3E5C]/50">
                    <span>Actualizada el {{ $latestFicha->updated_at->format('d/m/Y') }}</span>
                    <span>Registrado por: {{ $latestFicha->registrador?->name ?? 'Sistema' }}</span>
                </div>
            @endif
        </div>

        <!-- CARD B: MEDICACIÓN REGISTRADA -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                            <i class="ph-bold ph-pill text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#2F3E5C]">Medicación Registrada</h3>
                            <p class="text-xs text-[#2F3E5C]/60">Administración de tratamientos indicados</p>
                        </div>
                    </div>
                    <button 
                        @click="$dispatch('abrirModalMedicacion', '{{ $adulto->cod_am }}')" 
                        class="inline-flex items-center gap-1.5 rounded-lg border border-[#C45F4B] bg-white px-3 py-1.5 text-xs font-bold text-[#C45F4B] shadow-sm hover:bg-[#C45F4B]/5 active:scale-95 transition shrink-0"
                    >
                        <i class="ph-bold ph-plus"></i>
                        <span>Registrar Medicación</span>
                    </button>
                </div>

                @php
                    $activas = $medicaciones->where('estado', 'ACTIVO');
                @endphp

                @if($activas->isNotEmpty())
                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                        @foreach($activas as $med)
                            <div class="rounded-xl border border-[#C7B5A3]/40 bg-[#F8F2EC]/30 p-3 shadow-2xs flex justify-between items-center gap-3 hover:bg-[#F8F2EC]/55 transition">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <h4 class="font-bold text-sm text-[#2F3E5C] truncate">{{ $med->nombre_medicamento }}</h4>
                                        <span class="text-[9px] font-black uppercase bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.2 rounded-full shrink-0">
                                            Activo
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#2F3E5C]/75">
                                        <span class="font-semibold">{{ $med->dosis }}</span> &middot; {{ $med->via_administracion }} &middot; {{ $med->frecuencia }}
                                    </p>
                                    @if($med->medico_indica)
                                        <p class="text-[10px] text-[#2F3E5C]/50 mt-0.5">
                                            <i class="ph-bold ph-user-md mr-1"></i>Profesional responsable: {{ $med->medico_indica }}
                                        </p>
                                    @endif
                                </div>
                                <div class="shrink-0">
                                    <button 
                                        @click="$dispatch('abrirModalAdministracion', { cod_am: '{{ $adulto->cod_am }}', cod_med_adulto: {{ $med->cod_med_adulto }} })"
                                        class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-black text-white shadow-2xs transition active:scale-95 shrink-0"
                                    >
                                        <i class="ph-bold ph-check-square"></i>
                                        <span>Toma</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="my-8 text-center border-2 border-dashed border-[#C7B5A3]/40 rounded-2xl p-6 bg-[#F8F2EC]/30">
                        <i class="ph-fill ph-pill text-4xl text-[#C7B5A3] mb-2"></i>
                        <p class="text-sm font-bold text-[#2F3E5C]/60">Sin medicación registrada.</p>
                        <p class="text-xs text-[#2F3E5C]/40 mt-1">Registre los medicamentos indicados por el profesional responsable para realizar el seguimiento.</p>
                    </div>
                @endif
            </div>

            @if($medicaciones->isNotEmpty())
                <div class="mt-4 border-t border-[#C7B5A3]/20 pt-3 flex justify-between items-center text-[10px] font-bold text-[#2F3E5C]/50">
                    <span>Total histórico: {{ $medicaciones->count() }} indicados</span>
                    <button 
                        @click="$dispatch('abrirModalMedicacion', '{{ $adulto->cod_am }}')" 
                        class="text-[#C45F4B] hover:underline"
                    >
                        Ver historial completo &rarr;
                    </button>
                </div>
            @endif
        </div>

        <!-- CARD C: REGISTRO DE SIGNOS VITALES -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800 border border-teal-100">
                            <i class="ph-bold ph-activity text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#2F3E5C]">Signos Vitales</h3>
                            <p class="text-xs text-[#2F3E5C]/60">Último registro de signos vitales y antropometría</p>
                        </div>
                    </div>
                    <button 
                        @click="$dispatch('abrirModalSignos', '{{ $adulto->cod_am }}')" 
                        class="inline-flex items-center gap-1.5 rounded-lg bg-[#2F3E5C] px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-[#1f2937] active:scale-95 transition shrink-0"
                    >
                        <i class="ph-bold ph-plus"></i>
                        <span>Registrar Signos</span>
                    </button>
                </div>

                @if($signosVitales->isNotEmpty())
                    @php
                        $latestSigno = $signosVitales->first();
                    @endphp

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <!-- Presión Arterial -->
                            <div class="bg-blue-50/70 text-blue-900 rounded-2xl p-3 text-center border border-blue-100/50 shadow-2xs">
                                <p class="text-[9px] font-black uppercase text-blue-700/70 tracking-widest mb-1">Presión Art.</p>
                                <p class="text-base font-black">{{ $latestSigno->presion_arterial ?: 'N/A' }}</p>
                                <p class="text-[9px] font-bold text-blue-700/60 mt-0.5">mmHg</p>
                            </div>

                            <!-- Frecuencia Cardíaca -->
                            <div class="bg-red-50/70 text-red-950 rounded-2xl p-3 text-center border border-red-100/50 shadow-2xs">
                                <p class="text-[9px] font-black uppercase text-red-700/75 tracking-widest mb-1">Frec. Cardíaca</p>
                                <p class="text-base font-black">{{ $latestSigno->frecuencia_cardiaca ?: 'N/A' }}</p>
                                <p class="text-[9px] font-bold text-red-700/60 mt-0.5">lpm</p>
                            </div>

                            <!-- Temperatura -->
                            <div class="bg-orange-50/70 text-orange-950 rounded-2xl p-3 text-center border border-orange-100/50 shadow-2xs">
                                <p class="text-[9px] font-black uppercase text-orange-800/80 tracking-widest mb-1">Temperatura</p>
                                <p class="text-base font-black">{{ $latestSigno->temperatura ? $latestSigno->temperatura.'°' : 'N/A' }}</p>
                                <p class="text-[9px] font-bold text-orange-800/60 mt-0.5">Celcius</p>
                            </div>

                            <!-- Saturación -->
                            <div class="bg-teal-50/70 text-teal-950 rounded-2xl p-3 text-center border border-teal-100/50 shadow-2xs">
                                <p class="text-[9px] font-black uppercase text-teal-800/80 tracking-widest mb-1">Saturación</p>
                                <p class="text-base font-black">{{ $latestSigno->saturacion ? $latestSigno->saturacion.'%' : 'N/A' }}</p>
                                <p class="text-[9px] font-bold text-teal-800/60 mt-0.5">SpO2</p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-[#C7B5A3]/30 bg-[#F8F2EC]/40 p-3.5 space-y-2">
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <span class="block text-[9px] font-black uppercase text-[#2F3E5C]/55">Peso</span>
                                    <span class="text-xs font-black text-[#2F3E5C]">{{ $latestSigno->peso ? $latestSigno->peso.' kg' : 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-black uppercase text-[#2F3E5C]/55">Talla</span>
                                    <span class="text-xs font-black text-[#2F3E5C]">{{ $latestSigno->talla ? $latestSigno->talla.' m' : 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-black uppercase text-[#2F3E5C]/55">Glucosa</span>
                                    <span class="text-xs font-black text-[#2F3E5C]">{{ $latestSigno->glucosa ? $latestSigno->glucosa.' mg/dL' : 'N/A' }}</span>
                                </div>
                            </div>
                            
                            @if($latestSigno->imc)
                                <div class="pt-2 border-t border-[#CBBBAA]/20 flex justify-between items-center text-xs font-bold text-[#2F3E5C]">
                                    <span>Índice de Masa Corporal (IMC):</span>
                                    <span class="px-2 py-0.5 rounded-md font-black {{ $latestSigno->imc >= 25 ? 'bg-orange-100 text-orange-850' : 'bg-emerald-100 text-emerald-850' }}">
                                        {{ $latestSigno->imc }}
                                    </span>
                                </div>
                            @endif

                            @if($latestSigno->dolor !== null)
                                <div class="flex justify-between items-center text-xs font-bold text-[#2F3E5C]">
                                    <span>Escala de dolor:</span>
                                    <span class="px-2 py-0.5 rounded-md font-black {{ $latestSigno->dolor > 3 ? 'bg-red-100 text-red-850' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $latestSigno->dolor }} / 10
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($latestSigno->observacion)
                            <p class="text-xs text-[#2F3E5C]/70 italic mt-2"><i class="ph-bold ph-info mr-1"></i>{{ $latestSigno->observacion }}</p>
                        @endif
                    </div>
                @else
                    <div class="my-8 text-center border-2 border-dashed border-[#C7B5A3]/40 rounded-2xl p-6 bg-[#F8F2EC]/30">
                        <i class="ph-fill ph-activity text-4xl text-[#C7B5A3] mb-2"></i>
                        <p class="text-sm font-bold text-[#2F3E5C]/60">Sin signos vitales registrados.</p>
                        <p class="text-xs text-[#2F3E5C]/40 mt-1">Registre los signos vitales periódicos para evaluar el estado general de bienestar.</p>
                    </div>
                @endif
            </div>

            @if($signosVitales->isNotEmpty())
                <div class="mt-4 border-t border-[#C7B5A3]/20 pt-3 flex justify-between items-center text-[10px] font-bold text-[#2F3E5C]/50">
                    <span>Control del {{ $latestSigno->fecha->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($latestSigno->hora)->format('H:i') }}</span>
                    <span>Registrado por: {{ $latestSigno->registrador?->name ?? 'Sistema' }}</span>
                </div>
            @endif
        </div>

        <!-- CARD D: VALORACIÓN FUNCIONAL -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-100">
                            <i class="ph-bold ph-wheelchair text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#2F3E5C]">Valoración Funcional</h3>
                            <p class="text-xs text-[#2F3E5C]/60">Autonomía y nivel de dependencia registrado</p>
                        </div>
                    </div>
                    <button 
                        @click="$dispatch('abrirModalValoracion', '{{ $adulto->cod_am }}')" 
                        class="inline-flex items-center gap-1.5 rounded-lg bg-[#8EA17D] px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-[#617453] active:scale-95 transition shrink-0"
                    >
                        <i class="ph-bold ph-plus"></i>
                        <span>Nueva Valoración</span>
                    </button>
                </div>

                @if($valoracionesFuncionales->isNotEmpty())
                    @php
                        $latestVal = $valoracionesFuncionales->first();

                        $autonomia = [];
                        if($latestVal->come_solo) $autonomia[] = 'Come solo/a';
                        if($latestVal->se_bana_solo) $autonomia[] = 'Se baña solo/a';
                        if($latestVal->se_viste_solo) $autonomia[] = 'Se viste solo/a';
                        if($latestVal->va_bano_solo) $autonomia[] = 'Usa baño solo/a';
                        if($latestVal->camina_solo) $autonomia[] = 'Camina solo/a';

                        $apoyos = [];
                        if($latestVal->usa_baston) $apoyos[] = 'Bastón';
                        if($latestVal->usa_andador) $apoyos[] = 'Andador';
                        if($latestVal->usa_silla_ruedas) $apoyos[] = 'Silla de ruedas';

                        $sensibilidades = [];
                        if($latestVal->baja_vision) $sensibilidades[] = 'Baja visión';
                        if($latestVal->baja_audicion) $sensibilidades[] = 'Baja audición';
                        if($latestVal->dificultad_hablar) $sensibilidades[] = 'Dif. habla';
                        if($latestVal->necesita_supervision) $sensibilidades[] = 'Supervisión constante';
                    @endphp

                    <div class="space-y-4">
                        <div class="flex items-center justify-between rounded-xl border border-[#C7B5A3]/30 bg-[#F8F2EC]/40 px-4 py-3">
                            <span class="text-xs font-black uppercase tracking-wider text-[#2F3E5C]/60">Grado de Autonomía:</span>
                            <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wide border shadow-2xs
                                {{ $latestVal->nivel_dependencia === 'INDEPENDIENTE' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : '' }}
                                {{ $latestVal->nivel_dependencia === 'LEVE' ? 'bg-amber-50 text-amber-800 border-amber-200' : '' }}
                                {{ $latestVal->nivel_dependencia === 'MODERADO' ? 'bg-orange-50 text-orange-850 border-orange-200' : '' }}
                                {{ $latestVal->nivel_dependencia === 'SEVERO' ? 'bg-rose-50 text-rose-850 border-rose-200' : '' }}">
                                {{ $latestVal->nivel_dependencia }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h5 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1.5">Actividades de Vida Diaria</h5>
                                @if(count($autonomia) > 0)
                                    <div class="space-y-1">
                                        @foreach($autonomia as $item)
                                            <div class="flex items-center gap-1 text-xs font-bold text-[#2F3E5C]">
                                                <i class="ph-bold ph-check text-emerald-600"></i>
                                                <span>{{ $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-[#2F3E5C]/40 italic">Dependencia total registrada</span>
                                @endif
                            </div>

                            <div>
                                <h5 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1.5">Movilidad y Apoyos</h5>
                                @if(count($apoyos) > 0)
                                    <div class="space-y-1">
                                        @foreach($apoyos as $item)
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-[#2F3E5C]">
                                                <i class="ph-bold ph-warning text-amber-600 text-sm"></i>
                                                <span>{{ $item }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center gap-1 text-xs font-bold text-[#2F3E5C]">
                                        <i class="ph-bold ph-check text-emerald-600"></i>
                                        <span>Autónomo sin dispositivos</span>
                                    </div>
                                @endif
                                
                                @if(count($sensibilidades) > 0)
                                    <div class="mt-2.5 pt-2 border-t border-[#C7B5A3]/25">
                                        <h5 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1">Cuidado Sensorial</h5>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($sensibilidades as $item)
                                                <span class="inline-block bg-[#C45F4B]/5 text-[#C45F4B] text-[9px] font-black px-1.5 py-0.2 rounded border border-[#C45F4B]/10">
                                                    {{ $item }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($latestVal->observacion)
                            <div class="rounded-xl border border-[#CBBBAA]/20 bg-[#F8F2EC]/50 p-3">
                                <h5 class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/55 mb-1">Observaciones funcionales</h5>
                                <p class="text-xs text-[#2F3E5C]/80 italic line-clamp-2 leading-relaxed">"{{ $latestVal->observacion }}"</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="my-8 text-center border-2 border-dashed border-[#C7B5A3]/40 rounded-2xl p-6 bg-[#F8F2EC]/30">
                        <i class="ph-fill ph-wheelchair text-4xl text-[#C7B5A3] mb-2"></i>
                        <p class="text-sm font-bold text-[#2F3E5C]/60">Sin valoración funcional registrada.</p>
                        <p class="text-xs text-[#2F3E5C]/40 mt-1">Realice la valoración funcional para determinar el grado de dependencia y requerimiento de apoyo.</p>
                    </div>
                @endif
            </div>

            @if($valoracionesFuncionales->isNotEmpty())
                <div class="mt-4 border-t border-[#C7B5A3]/20 pt-3 flex justify-between items-center text-[10px] font-bold text-[#2F3E5C]/50">
                    <span>Valoración del {{ $latestVal->fecha_valoracion->format('d/m/Y') }}</span>
                    <span>Registrador: {{ $latestVal->registrador?->name ?? 'Sistema' }}</span>
                </div>
            @endif
        </div>

    </div>

    <!-- LIVEWIRE COMPONENTS FOR DIALOGS AND LOGIC -->
    <div class="livewire-modals-only">
        <livewire:admin.adultos-mayores.salud.ficha-medica-adulto-modal :cod_am="$adulto->cod_am" />
        <livewire:admin.adultos-mayores.salud.medicacion-adulto-modal :cod_am="$adulto->cod_am" />
        <livewire:admin.adultos-mayores.salud.signos-vitales-adulto-modal :cod_am="$adulto->cod_am" />
        <livewire:admin.adultos-mayores.salud.valoracion-funcional-adulto-modal :cod_am="$adulto->cod_am" />
        <livewire:admin.adultos-mayores.salud.historial-estado-adulto-panel :cod_am="$adulto->cod_am" />
        
        <!-- Este modal es llamado globalmente -->
        <livewire:admin.adultos-mayores.salud.administracion-medicacion-modal :cod_am="$adulto->cod_am" />
    </div>
</section>