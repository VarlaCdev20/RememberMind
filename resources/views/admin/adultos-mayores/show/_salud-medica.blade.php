{{-- TAB SALUD Y SEGUIMIENTO (RESUMEN NAVEGACIONAL) --}}
<section
    x-show="tab === 'salud'"
    style="display: none;"
    x-transition.opacity.duration.250ms
    class="space-y-6"
>
    <!-- HEADER BLOCK -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#CBBBAA]/30 pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E27D60]/10 text-[#E27D60]">
                <i class="ph-fill ph-heartbeat text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C]">Salud y Seguimiento Médico</h2>
                <p class="text-sm font-semibold text-[#2F3E5C]/60">Resumen clínico, controles periódicos, medicación y valoraciones.</p>
            </div>
        </div>
        
        <a 
            href="{{ route('admin.salud-seguimiento.resumen', $adulto->cod_am) }}"
            class="inline-flex items-center gap-2 rounded-xl border border-[#2F3E5C]/20 bg-white px-4 py-2.5 text-sm font-bold text-[#2F3E5C] shadow-sm hover:bg-[#2F3E5C]/5 active:scale-95 transition shrink-0"
        >
            <i class="ph-bold ph-squares-four text-md"></i>
            <span>Ver Resumen Médico Completo</span>
        </a>
    </div>

    <!-- ALERTS CENTER -->
    @php
        $alertasSalud = [];
        if ($fichasMedicas->isEmpty()) {
            $alertasSalud[] = [
                'tipo' => 'ficha',
                'mensaje' => 'Sin ficha médica básica registrada.',
                'icono' => 'ph-bold ph-heartbeat',
                'color' => 'bg-[#C45F4B]/10 text-[#C45F4B] border-[#C45F4B]/20',
                'ruta' => route('admin.salud-seguimiento.ficha', $adulto->cod_am)
            ];
        }
        if ($medicaciones->where('estado', 'ACTIVO')->isEmpty()) {
            $alertasSalud[] = [
                'tipo' => 'medicacion',
                'mensaje' => 'Sin medicación registrada.',
                'icono' => 'ph-bold ph-pill',
                'color' => 'bg-[#CBBBAA]/20 text-[#2F3E5C]/70 border-[#CBBBAA]/30',
                'ruta' => route('admin.salud-seguimiento.medicacion', $adulto->cod_am)
            ];
        }
        if ($signosVitales->isEmpty()) {
            $alertasSalud[] = [
                'tipo' => 'signos',
                'mensaje' => 'Sin registro de signos vitales.',
                'icono' => 'ph-bold ph-activity',
                'color' => 'bg-amber-50 text-amber-800 border-amber-200/50',
                'ruta' => route('admin.salud-seguimiento.signos', $adulto->cod_am)
            ];
        }
        if ($valoracionesFuncionales->isEmpty()) {
            $alertasSalud[] = [
                'tipo' => 'valoracion',
                'mensaje' => 'Sin valoración funcional registrada.',
                'icono' => 'ph-bold ph-wheelchair',
                'color' => 'bg-[#8EA17D]/10 text-[#617453] border-[#8EA17D]/20',
                'ruta' => route('admin.salud-seguimiento.valoracion', $adulto->cod_am)
            ];
        }
    @endphp

    @if(count($alertasSalud) > 0)
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($alertasSalud as $alerta)
                <a href="{{ $alerta['ruta'] }}" class="flex items-center justify-between rounded-xl border p-3.5 shadow-sm transition hover:shadow-md bg-white {{ $alerta['color'] }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <i class="{{ $alerta['icono'] }} text-lg shrink-0"></i>
                        <span class="text-xs font-bold truncate leading-tight">{{ $alerta['mensaje'] }}</span>
                    </div>
                    <span class="ml-2 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white/70 text-xs font-black shadow-sm shrink-0">
                        <i class="ph-bold ph-arrow-right"></i>
                    </span>
                </a>
            @endforeach
        </div>
    @endif

    <!-- INTERACTIVE PANEL GRID -->
    <div class="grid gap-6 lg:grid-cols-2">
        
        <!-- CARD A: FICHA MÉDICA BÁSICA -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between hover:border-[#E27D60]/40 transition group">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#E27D60]/10 text-[#E27D60]">
                            <i class="ph-bold ph-folder-user text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#2F3E5C]">Ficha Médica</h3>
                            <p class="text-xs text-[#2F3E5C]/60">Antecedentes, alergias y condiciones</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider {{ $fichasMedicas->isNotEmpty() ? 'bg-[#8DA280]/15 text-[#63775B]' : 'bg-[#C45F4B]/10 text-[#C45F4B]' }}">
                        {{ $fichasMedicas->isNotEmpty() ? 'Registrada' : 'Incompleta' }}
                    </span>
                </div>

                @if($fichasMedicas->isNotEmpty())
                    @php $latestFicha = $fichasMedicas->first(); @endphp
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-xl bg-[#F8F3ED] p-3 border border-[#C7B5A3]/30">
                                <span class="block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1">Alergias</span>
                                <span class="font-bold text-[#2F3E5C] {{ $latestFicha->alergias ? 'text-[#C45F4B]' : '' }}">
                                    {{ $latestFicha->alergias ? 'Sí, registradas' : 'Ninguna reportada' }}
                                </span>
                            </div>
                            <div class="rounded-xl bg-[#F8F3ED] p-3 border border-[#C7B5A3]/30">
                                <span class="block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1">Dieta Especial</span>
                                <span class="font-bold text-[#2F3E5C] {{ $latestFicha->restricciones_alimentarias ? 'text-[#D9A05B]' : '' }}">
                                    {{ $latestFicha->restricciones_alimentarias ? 'Sí, registradas' : 'Normal' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-xs font-bold text-[#2F3E5C]/60 italic py-2">Falta completar la información clínica base del adulto mayor.</p>
                @endif
            </div>

            <div class="mt-5 border-t border-[#C7B5A3]/20 pt-4">
                <a href="{{ route('admin.salud-seguimiento.ficha', $adulto->cod_am) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#5B5F97] active:scale-95">
                    <i class="ph-bold ph-stethoscope"></i> Ver Ficha Médica Completa
                </a>
            </div>
        </div>

        <!-- CARD B: SIGNOS VITALES -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between hover:border-[#E27D60]/40 transition group">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-800 border border-teal-100">
                            <i class="ph-bold ph-activity text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#2F3E5C]">Signos Vitales</h3>
                            <p class="text-xs text-[#2F3E5C]/60">Monitoreo de parámetros y medidas</p>
                        </div>
                    </div>
                </div>

                @if($signosVitales->isNotEmpty())
                    @php $latestSigno = $signosVitales->first(); @endphp
                    <div class="grid grid-cols-4 gap-2 text-center mt-2">
                        <div class="bg-blue-50/70 text-blue-900 rounded-xl p-2 border border-blue-100/50">
                            <p class="text-[8px] font-black uppercase tracking-widest text-blue-700/70 mb-0.5">PA</p>
                            <p class="text-sm font-black">{{ $latestSigno->presion_arterial ?: '-' }}</p>
                        </div>
                        <div class="bg-red-50/70 text-red-950 rounded-xl p-2 border border-red-100/50">
                            <p class="text-[8px] font-black uppercase tracking-widest text-red-700/75 mb-0.5">FC</p>
                            <p class="text-sm font-black">{{ $latestSigno->frecuencia_cardiaca ?: '-' }}</p>
                        </div>
                        <div class="bg-orange-50/70 text-orange-950 rounded-xl p-2 border border-orange-100/50">
                            <p class="text-[8px] font-black uppercase tracking-widest text-orange-800/80 mb-0.5">TEMP</p>
                            <p class="text-sm font-black">{{ $latestSigno->temperatura ? $latestSigno->temperatura.'°' : '-' }}</p>
                        </div>
                        <div class="bg-teal-50/70 text-teal-950 rounded-xl p-2 border border-teal-100/50">
                            <p class="text-[8px] font-black uppercase tracking-widest text-teal-800/80 mb-0.5">SpO2</p>
                            <p class="text-sm font-black">{{ $latestSigno->saturacion ? $latestSigno->saturacion.'%' : '-' }}</p>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-[#2F3E5C]/50 mt-3 text-right">Último control: {{ $latestSigno->fecha->format('d/m/Y') }}</p>
                @else
                    <p class="text-xs font-bold text-[#2F3E5C]/60 italic py-2">Sin registros de signos vitales recientes.</p>
                @endif
            </div>

            <div class="mt-5 border-t border-[#C7B5A3]/20 pt-4">
                <a href="{{ route('admin.salud-seguimiento.signos', $adulto->cod_am) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-teal-700 active:scale-95">
                    <i class="ph-bold ph-trend-up"></i> Ver Signos Vitales
                </a>
            </div>
        </div>

        <!-- CARD C: MEDICACIÓN Y ADMINISTRACIÓN -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between hover:border-[#E27D60]/40 transition group">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#8DA280]/20 text-[#63775B]">
                            <i class="ph-bold ph-pill text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#2F3E5C]">Tratamiento Farmacológico</h3>
                            <p class="text-xs text-[#2F3E5C]/60">Medicación y tomas diarias</p>
                        </div>
                    </div>
                    @php $medicacionesActivasCount = $medicaciones->where('estado', 'ACTIVO')->count(); @endphp
                    <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider bg-[#8DA280]/15 text-[#63775B]">
                        {{ $medicacionesActivasCount }} Activas
                    </span>
                </div>

                @if($medicacionesActivasCount > 0)
                    <div class="flex items-center justify-between rounded-xl bg-[#F8F3ED] p-4 border border-[#C7B5A3]/30">
                        <div>
                            <span class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-1">Administraciones recientes</span>
                            <span class="text-sm font-black text-[#2F3E5C]">{{ $administracionesMedicacion->take(5)->where('estado_administracion', 'ADMINISTRADO')->count() }} Tomas Completadas</span>
                        </div>
                        <i class="ph-bold ph-check-square text-2xl text-emerald-600/50"></i>
                    </div>
                @else
                    <p class="text-xs font-bold text-[#2F3E5C]/60 italic py-2">No existen tratamientos medicamentosos activos actualmente.</p>
                @endif
            </div>

            <div class="mt-5 border-t border-[#C7B5A3]/20 pt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('admin.salud-seguimiento.medicacion', $adulto->cod_am) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#63775B] px-3 py-2.5 text-[10px] font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#4d5e46] active:scale-95">
                    <i class="ph-bold ph-prescription"></i> Medicación
                </a>
                <a href="{{ route('admin.salud-seguimiento.administracion', $adulto->cod_am) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white border border-[#63775B] px-3 py-2.5 text-[10px] font-black uppercase tracking-wider text-[#63775B] shadow-sm transition hover:bg-[#63775B]/10 active:scale-95">
                    <i class="ph-bold ph-calendar-check"></i> Tomas
                </a>
            </div>
        </div>

        <!-- CARD D: VALORACIÓN FUNCIONAL -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between hover:border-[#E27D60]/40 transition group">
            <div>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-800 border border-amber-100">
                            <i class="ph-bold ph-person-simple-walk text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-[#2F3E5C]">Valoración Funcional</h3>
                            <p class="text-xs text-[#2F3E5C]/60">Nivel de dependencia y autonomía</p>
                        </div>
                    </div>
                </div>

                @if($valoracionesFuncionales->isNotEmpty())
                    @php $latestVal = $valoracionesFuncionales->first(); @endphp
                    <div class="rounded-xl bg-[#F8F3ED] p-4 border border-[#C7B5A3]/30 text-center">
                        <span class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-2">Grado de Autonomía Institucional</span>
                        <span class="inline-block px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wide border shadow-sm
                            {{ $latestVal->nivel_dependencia === 'INDEPENDIENTE' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : '' }}
                            {{ $latestVal->nivel_dependencia === 'LEVE' ? 'bg-amber-50 text-amber-800 border-amber-200' : '' }}
                            {{ $latestVal->nivel_dependencia === 'MODERADO' ? 'bg-orange-50 text-orange-850 border-orange-200' : '' }}
                            {{ $latestVal->nivel_dependencia === 'SEVERO' ? 'bg-rose-50 text-rose-850 border-rose-200' : '' }}">
                            {{ $latestVal->nivel_dependencia }}
                        </span>
                        <p class="text-[9px] font-bold text-[#2F3E5C]/50 mt-3">Riesgo de Caídas: <span class="font-black text-[#C45F4B]">{{ $latestVal->riesgo_caida ?? 'No evaluado' }}</span></p>
                    </div>
                @else
                    <p class="text-xs font-bold text-[#2F3E5C]/60 italic py-2">Evaluación funcional pendiente de registro.</p>
                @endif
            </div>

            <div class="mt-5 border-t border-[#C7B5A3]/20 pt-4">
                <a href="{{ route('admin.salud-seguimiento.valoracion', $adulto->cod_am) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-amber-700 active:scale-95">
                    <i class="ph-bold ph-wheelchair"></i> Ver Valoración Funcional
                </a>
            </div>
        </div>

        <!-- EXTRA: ALERTAS Y REPORTES Y EVALUACIONES -->
        <div class="col-span-1 lg:col-span-2 grid gap-6 md:grid-cols-2">
            <!-- Evaluaciones Geriátricas -->
            <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between hover:border-[#E27D60]/40 transition group">
                <div>
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#6873A6]/10 text-[#566189]">
                                <i class="ph-bold ph-folder-user text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-[#2F3E5C]">Evaluaciones Geriátricas</h3>
                                <p class="text-xs text-[#2F3E5C]/60">Suite geriátrica multidimensional</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($evaluacionesGeriatricasActivas->isNotEmpty())
                        @php $latestGer = $evaluacionesGeriatricasActivas->first(); @endphp
                        <div class="rounded-xl bg-[#F8F3ED] p-4 border border-[#C7B5A3]/30">
                            <div class="flex items-center justify-between">
                                <span class="block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Última Evaluación</span>
                                <span class="text-[10px] font-bold text-[#2F3E5C]/50">{{ $latestGer->fecha_eval->format('d/m/Y') }}</span>
                            </div>
                            <p class="text-sm font-black text-[#2F3E5C] mt-1">{{ $latestGer->instrumento->nombre }}</p>
                            <p class="text-[10px] font-bold text-[#2F3E5C]/50 mt-0.5">{{ $latestGer->instrumento->area->nombre ?? 'Área Geriátrica' }}</p>
                            
                            @if($latestGer->nivel_alerta !== 'NORMAL')
                                <div class="mt-2 inline-flex px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $latestGer->nivel_alerta === 'CRITICO' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700' }}">
                                    Alerta {{ $latestGer->nivel_alerta }}
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-xs font-bold text-[#2F3E5C]/60 italic py-2">Sin evaluaciones geriátricas registradas.</p>
                    @endif
                </div>

                <div class="mt-5 border-t border-[#C7B5A3]/20 pt-4">
                    <button type="button" @click="$dispatch('evaluacion-geriatrica-abrir', { cod_am: '{{ $adulto->cod_am }}' })" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#6873A6] px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-[#566189] active:scale-95">
                        <i class="ph-bold ph-list-magnifying-glass"></i> Ver Evaluaciones Geriátricas
                    </button>
                </div>
            </div>

            <!-- Centro Operativo (Alertas y Reportes) -->
            <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-gradient-to-br from-[#2F3E5C] to-[#455A84] p-6 shadow-sm flex flex-col justify-between text-white hover:shadow-md transition">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <i class="ph-bold ph-warning-circle text-4xl text-white/50"></i>
                        <div>
                            <h3 class="text-lg font-black">Centro Operativo</h3>
                            <p class="text-xs text-white/70 mt-1">Alertas, reportes y seguimiento centralizado del residente.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 w-full">
                    <a href="{{ route('admin.salud-seguimiento.alertas', ['adulto' => $adulto->cod_am]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 border border-white/20 px-3 py-2.5 text-xs font-black uppercase tracking-wider text-white transition hover:bg-white/20">
                        <i class="ph-bold ph-bell-ringing"></i> Alertas
                    </a>
                    <a href="{{ route('admin.salud-seguimiento.reportes', ['adulto' => $adulto->cod_am]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white text-[#2F3E5C] px-3 py-2.5 text-xs font-black uppercase tracking-wider transition hover:bg-white/90">
                        <i class="ph-bold ph-chart-line-up"></i> Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="livewire-modals-only">
        <livewire:admin.adultos-mayores.evaluaciones.evaluacion-geriatrica-modal :cod_am="$adulto->cod_am" />
    </div>
</section>