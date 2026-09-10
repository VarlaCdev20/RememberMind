<div class="space-y-6">

    {{-- Encabezado del paciente --}}
    <div class="flex flex-col gap-4 rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm md:flex-row md:items-center">
        <div class="flex items-center gap-4 flex-1">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-estado-infoBg text-estado-info font-black text-2xl">
                {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
            </div>
            <div>
                <h2 class="text-xl font-black tracking-tight text-titulo">
                    {{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}
                </h2>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs font-semibold text-apoyo">
                    <span><i class="ph-bold ph-calendar mr-1"></i>{{ $edadPaciente }} años</span>
                    <span><i class="ph-bold ph-identification-card mr-1"></i>CI: {{ $adulto->ci }}</span>
                    @if($adulto->genero)
                    <span><i class="ph-bold ph-gender-intersex mr-1"></i>{{ $adulto->genero }}</span>
                    @endif
                    @if($fichaMedica && $fichaMedica->grupo_sanguineo)
                    <span class="font-black text-estado-error"><i class="ph-bold ph-drop-half mr-1"></i>{{ $fichaMedica->grupo_sanguineo }}</span>
                    @endif
                </div>
                @if($adulto->estado)
                <span class="mt-1.5 inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider
                             {{ in_array($adulto->estado->estado, ['EN_SEGUIMIENTO_ACTIVO','ACTIVO']) ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-advertenciaBg text-estado-advertencia' }}">
                    {{ str_replace('_', ' ', $adulto->estado->estado) }}
                </span>
                @endif
            </div>
        </div>
        <div class="flex shrink-0 flex-wrap gap-2">
            <button wire:click="nuevosSignos" class="rm-btn-secondary h-9 px-3 flex items-center gap-1.5 text-xs">
                <i class="ph-bold ph-heartbeat text-sm text-boton-acento"></i> Signos vitales
            </button>
            <button wire:click="nuevaNota" class="rm-btn-secondary h-9 px-3 flex items-center gap-1.5 text-xs">
                <i class="ph-bold ph-note-pencil text-sm text-estado-exito"></i> Nueva nota
            </button>
            <button wire:click="nuevaBarthel" class="rm-btn-secondary h-9 px-3 flex items-center gap-1.5 text-xs">
                <i class="ph-bold ph-person text-sm text-estado-info"></i> Barthel
            </button>
            <a href="{{ route('admin.medico.pacientes.observacion') }}"
               class="rm-btn-secondary h-9 px-3 flex items-center gap-1.5 text-xs">
                <i class="ph-bold ph-arrow-left text-sm"></i> Volver
            </a>
        </div>
    </div>

    {{-- Resumen rápido de signos vitales --}}
    @if($ultimosSignos)
    @php
        $sist = $ultimosSignos->presion_sistolica;
        $diast = $ultimosSignos->presion_diastolica;
        $alerta = $sist > 160 || $sist < 90 || ($ultimosSignos->saturacion && $ultimosSignos->saturacion < 92);
    @endphp
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
        @foreach([
            ['PA',''.($sist??'—').'/'.($diast??'—'),'mmHg','ph-heart',$sist>160||$sist<90?'error':'exito'],
            ['FC',$ultimosSignos->frecuencia_cardiaca??'—','bpm','ph-heartbeat',($ultimosSignos->frecuencia_cardiaca>100||($ultimosSignos->frecuencia_cardiaca&&$ultimosSignos->frecuencia_cardiaca<50))?'advertencia':'exito'],
            ['FR',$ultimosSignos->frecuencia_respiratoria??'—','rpm','ph-wind','exito'],
            ['Temp',$ultimosSignos->temperatura??'—','°C','ph-thermometer',($ultimosSignos->temperatura&&$ultimosSignos->temperatura>37.5)?'advertencia':'exito'],
            ['SpO2',$ultimosSignos->saturacion??'—','%','ph-drop',($ultimosSignos->saturacion&&$ultimosSignos->saturacion<92)?'error':($ultimosSignos->saturacion&&$ultimosSignos->saturacion<95?'advertencia':'exito')],
            ['Glucosa',$ultimosSignos->glucosa??'—','mg/dL','ph-drop-half','info'],
            ['IMC',$ultimosSignos->imc??'—','','ph-ruler','info'],
        ] as [$lab,$val,$unit,$icon,$color])
        <div class="flex flex-col items-center justify-center rounded-2xl border border-borde bg-fondo-card p-3 text-center">
            <i class="ph-bold {{ $icon }} text-lg text-estado-{{ $color }}"></i>
            <div class="mt-1 text-lg font-black text-titulo leading-none">{{ $val }}</div>
            <div class="text-[9px] font-bold text-apoyo">{{ $lab }}{{ $unit ? ' ('.$unit.')' : '' }}</div>
        </div>
        @endforeach
    </div>
    @if($alerta)
    <div class="flex items-center gap-2 rounded-xl bg-estado-advertenciaBg px-4 py-2.5 text-sm font-bold text-estado-advertencia">
        <i class="ph-bold ph-warning-circle text-base"></i>
        Alerta: valores fuera de rango normal. Revisar signos vitales del {{ \Carbon\Carbon::parse($ultimosSignos->fecha)->format('d/m/Y') }}.
    </div>
    @endif
    @endif

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-1 rounded-2xl border border-borde bg-fondo-panel p-1">
        @foreach([
            ['resumen',    'ph-layout',        'Resumen'],
            ['notas',      'ph-note-pencil',   'Notas SOAP'],
            ['signos',     'ph-heartbeat',     'Signos Vitales'],
            ['medicacion', 'ph-pill',          'Medicación'],
            ['funcional',  'ph-person',        'Funcional (Barthel)'],
            ['geriatrico', 'ph-brain',         'Evaluaciones Geriátricas'],
        ] as [$t, $icon, $label])
        <button wire:click="setTab('{{ $t }}')"
                class="flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider transition
                       {{ $tab === $t ? 'bg-fondo-card text-boton-acento shadow-sm' : 'text-apoyo hover:text-titulo' }}">
            <i class="ph-bold {{ $icon }} text-sm"></i>
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Tab: Resumen --}}
    @if($tab === 'resumen')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Diagnósticos principales --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-titulo">
                <i class="ph-bold ph-stethoscope text-boton-acento"></i> Diagnósticos Principales
            </h3>
            @if($fichaMedica)
                @if($fichaMedica->diagnostico_principal)
                <div class="mb-2 rounded-xl bg-estado-infoBg px-4 py-2">
                    <div class="text-[10px] font-black uppercase tracking-wider text-estado-info">Principal</div>
                    <div class="text-sm font-bold text-titulo">{{ $fichaMedica->diagnostico_principal }}</div>
                </div>
                @endif
                @if($fichaMedica->diagnosticos_secundarios)
                <div class="rounded-xl bg-fondo-panel px-4 py-2">
                    <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Secundarios</div>
                    <div class="text-sm font-semibold text-titulo">{{ $fichaMedica->diagnosticos_secundarios }}</div>
                </div>
                @endif
                @if($fichaMedica->alergias)
                <div class="mt-2 flex items-start gap-2 rounded-xl bg-estado-errorBg px-4 py-2">
                    <i class="ph-bold ph-warning-circle text-estado-error mt-0.5"></i>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-estado-error">Alergias</div>
                        <div class="text-sm font-bold text-titulo">{{ $fichaMedica->alergias }}</div>
                    </div>
                </div>
                @endif
            @else
            <p class="text-sm text-apoyo italic">Sin ficha médica registrada.</p>
            @endif
        </div>

        {{-- Valoración funcional Barthel --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-titulo">
                <i class="ph-bold ph-person text-estado-exito"></i> Funcionalidad (Barthel)
            </h3>
            @if($valoracionFuncional)
            @php
                $barthel = $valoracionFuncional['indice_barthel'] ?? 0;
                $barthelColor = $barthel >= 91 ? 'exito' : ($barthel >= 61 ? 'info' : ($barthel >= 41 ? 'advertencia' : 'error'));
            @endphp
            <div class="flex items-center gap-4">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border-4 border-estado-{{ $barthelColor }} font-black text-2xl text-estado-{{ $barthelColor }}">
                    {{ $barthel }}
                </div>
                <div>
                    <div class="text-lg font-black text-titulo">{{ $valoracionFuncional['nivel_dependencia'] ?? '—' }}</div>
                    <div class="text-sm text-apoyo">Índice de Barthel: {{ $barthel }}/100</div>
                    <div class="mt-1.5 flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-apoyo">Riesgo caída:</span>
                        <span class="rounded-full px-2 py-0.5 text-[10px] font-black
                                     {{ ($valoracionFuncional['riesgo_caida'] ?? '') === 'ALTO' ? 'bg-estado-errorBg text-estado-error' :
                                        (($valoracionFuncional['riesgo_caida'] ?? '') === 'MODERADO' ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-estado-exitoBg text-estado-exito') }}">
                            {{ $valoracionFuncional['riesgo_caida'] ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>
            <button wire:click="nuevaBarthel" class="mt-3 w-full rounded-xl border border-borde bg-fondo-panel py-2 text-xs font-bold text-apoyo hover:text-titulo transition">
                <i class="ph-bold ph-plus mr-1"></i> Nueva valoración Barthel
            </button>
            @else
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <p class="text-sm text-apoyo italic">Sin valoración funcional registrada.</p>
                <button wire:click="nuevaBarthel" class="mt-3 rm-btn-secondary h-9 px-4 text-xs">
                    <i class="ph-bold ph-plus mr-1"></i> Evaluar ahora
                </button>
            </div>
            @endif
        </div>

        {{-- Evaluaciones cognitiva y afectiva --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <h3 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-titulo">
                <i class="ph-bold ph-brain text-boton-acento"></i> Esfera Cognitiva y Afectiva
            </h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['Cognitiva', $evalCognitiva, 'ARE_COG', 'info'],
                    ['Afectiva',  $evalAfectiva,  'ARE_AFE', 'advertencia'],
                ] as [$nombre, $eval, $area, $color])
                <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                    <div class="mb-1 text-[10px] font-black uppercase tracking-wider text-apoyo">{{ $nombre }}</div>
                    @if($eval)
                    <div class="text-sm font-bold text-titulo">{{ $eval->instrumento->nombre ?? '—' }}</div>
                    @if($eval->puntaje_total !== null)
                    <div class="text-xl font-black text-estado-{{ $color }}">{{ $eval->puntaje_total }}</div>
                    @endif
                    @if($eval->nivel_alerta)
                    <span class="text-[9px] font-black uppercase {{ $eval->nivel_alerta === 'CRITICO' ? 'text-estado-error' : ($eval->nivel_alerta === 'ALTO' ? 'text-estado-advertencia' : 'text-estado-exito') }}">
                        {{ $eval->nivel_alerta }}
                    </span>
                    @endif
                    <div class="text-[9px] text-apoyo">{{ \Carbon\Carbon::parse($eval->fecha_eval)->format('d/m/Y') }}</div>
                    @else
                    <p class="text-xs text-apoyo italic">Sin evaluar</p>
                    @endif
                    <button wire:click="nuevaEvaluacionGeriatrica('{{ $area }}')"
                            class="mt-2 w-full rounded-lg bg-fondo-card border border-borde py-1 text-[10px] font-bold text-apoyo hover:text-titulo transition">
                        + Evaluar
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Medicación activa --}}
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <h3 class="mb-4 flex items-center justify-between text-sm font-black uppercase tracking-wider text-titulo">
                <span class="flex items-center gap-2">
                    <i class="ph-bold ph-pill text-estado-exito"></i> Medicación Activa
                </span>
                <span class="text-xs font-bold text-apoyo">{{ count($medicacionActiva) }} fármacos</span>
            </h3>
            @if(count($medicacionActiva) > 0)
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                @foreach($medicacionActiva as $med)
                <div class="flex items-start gap-3 rounded-xl bg-fondo-panel px-3 py-2">
                    <i class="ph-bold ph-pill text-estado-exito mt-0.5"></i>
                    <div class="flex-1">
                        <div class="text-xs font-bold text-titulo">{{ $med['nombre_medicamento'] ?? '—' }}</div>
                        @if($med['dosis'] ?? null)
                        <div class="text-[10px] text-apoyo">{{ $med['dosis'] }} — {{ $med['frecuencia'] ?? '' }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-apoyo italic">Sin medicación activa registrada.</p>
            @endif
        </div>

    </div>
    @endif

    {{-- Tab: Notas SOAP --}}
    @if($tab === 'notas')
    <div class="space-y-4">
        <div class="flex justify-end">
            <button wire:click="nuevaNota" class="rm-btn-primary h-10 px-5 flex items-center gap-2">
                <i class="ph-bold ph-note-pencil text-sm"></i> Nueva nota
            </button>
        </div>
        @forelse($notasRecientes as $nota)
        @php
            $tipoColor = match($nota['tipo_nota'] ?? 'EVOLUCION') {
                'INGRESO'       => 'bg-estado-infoBg text-estado-info',
                'EGRESO'        => 'bg-fondo-panel text-apoyo',
                'URGENCIA'      => 'bg-estado-errorBg text-estado-error',
                'INTERCONSULTA' => 'bg-boton-acento/10 text-boton-acento',
                'PROCEDIMIENTO' => 'bg-estado-advertenciaBg text-estado-advertencia',
                default         => 'bg-estado-exitoBg text-estado-exito',
            };
        @endphp
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider {{ $tipoColor }}">
                        {{ str_replace('_', ' ', $nota['tipo_nota'] ?? 'EVOLUCION') }}
                    </span>
                    <span class="text-xs font-bold text-titulo">
                        {{ \Carbon\Carbon::parse($nota['fecha'])->format('d/m/Y') }}
                        @if($nota['hora']) {{ substr($nota['hora'], 0, 5) }} @endif
                    </span>
                </div>
                <span class="text-[10px] text-apoyo">
                    Dr. {{ $nota['registrador']['name'] ?? 'Sistema' }}
                </span>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($nota['subjetivo'] ?? null)
                <div class="rounded-xl bg-estado-infoBg/30 p-3">
                    <div class="mb-1 flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-estado-info">
                        <span class="flex h-4 w-4 items-center justify-center rounded bg-estado-info text-white text-[8px]">S</span>
                        Subjetivo
                    </div>
                    <p class="text-xs font-semibold text-titulo">{{ $nota['subjetivo'] }}</p>
                </div>
                @endif
                @if($nota['objetivo'] ?? null)
                <div class="rounded-xl bg-estado-advertenciaBg/30 p-3">
                    <div class="mb-1 flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-estado-advertencia">
                        <span class="flex h-4 w-4 items-center justify-center rounded bg-estado-advertencia text-white text-[8px]">O</span>
                        Objetivo
                    </div>
                    <p class="text-xs font-semibold text-titulo">{{ $nota['objetivo'] }}</p>
                </div>
                @endif
                <div class="rounded-xl bg-boton-acento/5 p-3 sm:col-span-{{ ($nota['subjetivo']??null) && ($nota['objetivo']??null) ? '1' : '2' }}">
                    <div class="mb-1 flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-boton-acento">
                        <span class="flex h-4 w-4 items-center justify-center rounded bg-boton-acento text-white text-[8px]">A</span>
                        Valoración / Diagnóstico
                    </div>
                    <p class="text-xs font-semibold text-titulo">{{ $nota['valoracion'] }}</p>
                </div>
                <div class="rounded-xl bg-estado-exitoBg/30 p-3">
                    <div class="mb-1 flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-estado-exito">
                        <span class="flex h-4 w-4 items-center justify-center rounded bg-estado-exito text-white text-[8px]">P</span>
                        Plan
                    </div>
                    <p class="text-xs font-semibold text-titulo">{{ $nota['plan'] }}</p>
                </div>
            </div>
            @if($nota['observaciones'] ?? null)
            <div class="mt-2 rounded-xl bg-fondo-panel px-3 py-2 text-xs text-apoyo">
                <i class="ph-bold ph-note mr-1"></i>{{ $nota['observaciones'] }}
            </div>
            @endif
        </div>
        @empty
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <i class="ph-bold ph-note-pencil text-3xl text-apoyo"></i>
            <h3 class="mt-3 text-base font-bold text-titulo">Sin notas de evolución</h3>
            <p class="mt-1 text-sm text-apoyo">Crea la primera nota SOAP para este paciente.</p>
        </div>
        @endforelse
    </div>
    @endif

    {{-- Tab: Signos Vitales --}}
    @if($tab === 'signos')
    <div class="space-y-4">
        <div class="flex justify-end">
            <button wire:click="nuevosSignos" class="rm-btn-primary h-10 px-5 flex items-center gap-2">
                <i class="ph-bold ph-heartbeat text-sm"></i> Registrar signos
            </button>
        </div>
        @if($ultimosSignos)
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-black text-titulo">Último registro de signos vitales</h3>
                <span class="text-xs text-apoyo">{{ \Carbon\Carbon::parse($ultimosSignos->fecha)->format('d/m/Y') }} {{ $ultimosSignos->hora ? substr($ultimosSignos->hora, 0, 5) : '' }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach([
                    ['Presión Arterial', $ultimosSignos->presion_sistolica ? $ultimosSignos->presion_sistolica.'/'.$ultimosSignos->presion_diastolica.' mmHg' : '—', 'ph-heart'],
                    ['Frec. Cardíaca', $ultimosSignos->frecuencia_cardiaca ? $ultimosSignos->frecuencia_cardiaca.' bpm' : '—', 'ph-heartbeat'],
                    ['Frec. Respiratoria', $ultimosSignos->frecuencia_respiratoria ? $ultimosSignos->frecuencia_respiratoria.' rpm' : '—', 'ph-wind'],
                    ['Temperatura', $ultimosSignos->temperatura ? $ultimosSignos->temperatura.' °C' : '—', 'ph-thermometer'],
                    ['Saturación O2', $ultimosSignos->saturacion ? $ultimosSignos->saturacion.'%' : '—', 'ph-drop'],
                    ['Glucosa', $ultimosSignos->glucosa ? $ultimosSignos->glucosa.' mg/dL' : '—', 'ph-drop-half'],
                    ['Peso', $ultimosSignos->peso ? $ultimosSignos->peso.' kg' : '—', 'ph-scales'],
                    ['IMC', $ultimosSignos->imc ?? '—', 'ph-ruler'],
                ] as [$lab, $val, $icon])
                <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                    <div class="flex items-center gap-1.5 text-[10px] font-bold text-apoyo">
                        <i class="ph-bold {{ $icon }} text-xs"></i> {{ $lab }}
                    </div>
                    <div class="mt-1 text-base font-black text-titulo">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <i class="ph-bold ph-heartbeat text-3xl text-apoyo"></i>
            <h3 class="mt-3 text-base font-bold text-titulo">Sin signos vitales</h3>
            <p class="mt-1 text-sm text-apoyo">Registra el primer control de signos vitales.</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Tab: Medicación --}}
    @if($tab === 'medicacion')
    <div class="rounded-[24px] border border-borde bg-fondo-card shadow-sm overflow-hidden">
        @if(count($medicacionActiva) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                    <tr>
                        <th class="px-5 py-3">Medicamento</th>
                        <th class="px-5 py-3">Dosis</th>
                        <th class="px-5 py-3">Frecuencia</th>
                        <th class="px-5 py-3">Vía</th>
                        <th class="px-5 py-3">Indicación</th>
                        <th class="px-5 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @foreach($medicacionActiva as $med)
                    <tr class="hover:bg-fondo-panel/50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <i class="ph-bold ph-pill text-estado-exito"></i>
                                <span class="font-bold text-titulo">{{ $med['nombre_medicamento'] ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 font-semibold text-titulo">{{ $med['dosis'] ?? '—' }}</td>
                        <td class="px-5 py-3 text-apoyo">{{ $med['frecuencia'] ?? '—' }}</td>
                        <td class="px-5 py-3 text-apoyo">{{ $med['via_administracion'] ?? '—' }}</td>
                        <td class="px-5 py-3 text-apoyo max-w-[200px] truncate">{{ $med['indicacion'] ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full bg-estado-exitoBg px-2 py-0.5 text-[10px] font-black text-estado-exito">ACTIVO</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <i class="ph-bold ph-pill text-3xl text-apoyo"></i>
            <h3 class="mt-3 text-base font-bold text-titulo">Sin medicación activa</h3>
            <p class="mt-1 text-sm text-apoyo">No hay fármacos registrados para este paciente.</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Tab: Funcional --}}
    @if($tab === 'funcional')
    <div class="space-y-4">
        <div class="flex justify-end">
            <button wire:click="nuevaBarthel" class="rm-btn-primary h-10 px-5 flex items-center gap-2">
                <i class="ph-bold ph-person text-sm"></i> Nueva valoración Barthel
            </button>
        </div>
        @if($valoracionFuncional)
        @php
            $barthel = $valoracionFuncional['indice_barthel'] ?? 0;
            $barthelColor = $barthel >= 91 ? 'exito' : ($barthel >= 61 ? 'info' : ($barthel >= 41 ? 'advertencia' : 'error'));
        @endphp
        <div class="rounded-[24px] border border-borde bg-fondo-card p-6 shadow-sm">
            <div class="mb-5 flex items-center gap-5">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full border-4 border-estado-{{ $barthelColor }} font-black text-3xl text-estado-{{ $barthelColor }}">
                    {{ $barthel }}
                </div>
                <div>
                    <h3 class="text-xl font-black text-titulo">{{ $valoracionFuncional['nivel_dependencia'] ?? '—' }}</h3>
                    <p class="text-sm text-apoyo">Índice de Barthel: {{ $barthel }}/100</p>
                    <p class="text-xs text-apoyo">Valoración del {{ \Carbon\Carbon::parse($valoracionFuncional['fecha_valoracion'] ?? now())->format('d/m/Y') }}</p>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-xs font-black text-apoyo">Riesgo de caída:</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-black
                                     {{ ($valoracionFuncional['riesgo_caida'] ?? '') === 'ALTO' ? 'bg-estado-errorBg text-estado-error' :
                                        (($valoracionFuncional['riesgo_caida'] ?? '') === 'MODERADO' ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-estado-exitoBg text-estado-exito') }}">
                            {{ $valoracionFuncional['riesgo_caida'] ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach([
                    ['come_solo','Alimentación'],
                    ['se_bana_solo','Baño'],
                    ['se_viste_solo','Vestido'],
                    ['va_bano_solo','Uso del retrete'],
                    ['camina_solo','Deambulación'],
                    ['necesita_supervision','Necesita supervisión'],
                    ['usa_baston','Usa bastón'],
                    ['usa_andador','Usa andador'],
                    ['usa_silla_ruedas','Silla de ruedas'],
                ] as [$campo, $etiqueta])
                @php $valor = $valoracionFuncional[$campo] ?? false; @endphp
                <div class="flex items-center gap-2 rounded-xl px-3 py-2 {{ $valor ? 'bg-estado-exitoBg' : 'bg-fondo-panel' }}">
                    <i class="ph-bold {{ $valor ? 'ph-check-circle text-estado-exito' : 'ph-x-circle text-apoyo' }} text-sm"></i>
                    <span class="text-[11px] font-bold {{ $valor ? 'text-titulo' : 'text-apoyo' }}">{{ $etiqueta }}</span>
                </div>
                @endforeach
            </div>

            @if($valoracionFuncional['observacion'] ?? null)
            <div class="mt-4 rounded-xl bg-fondo-panel px-4 py-3 text-sm text-apoyo">
                <i class="ph-bold ph-note mr-1"></i>{{ $valoracionFuncional['observacion'] }}
            </div>
            @endif
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <i class="ph-bold ph-person text-3xl text-apoyo"></i>
            <h3 class="mt-3 text-base font-bold text-titulo">Sin valoración funcional</h3>
            <p class="mt-1 text-sm text-apoyo">Aplica el Índice de Barthel para conocer el nivel de dependencia.</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Tab: Evaluaciones Geriátricas --}}
    @if($tab === 'geriatrico')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['ARE_COG','Cognitiva',  'ph-brain',    'info',      'MMSE, MoCA, Test del reloj'],
            ['ARE_AFE','Afectiva',   'ph-smiley-sad','advertencia','GDS, Hamilton depresión'],
            ['ARE_FUN','Funcional',  'ph-person',    'exito',     'Barthel, Lawton & Brody'],
            ['ARE_NUT','Nutricional','ph-bowl-food',  'boton-acento','MNA, IMC, circunferencia'],
            ['ARE_SOC','Social',     'ph-users',     'error',     'Red de apoyo, vivienda'],
        ] as [$area, $nombre, $icon, $color, $instrumentos])
        @php
            $eval = null;
            if ($area === 'ARE_COG') $eval = $evalCognitiva;
            if ($area === 'ARE_AFE') $eval = $evalAfectiva;
        @endphp
        <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-{{ $color }}Bg text-estado-{{ $color }}">
                    <i class="ph-bold {{ $icon }} text-lg"></i>
                </div>
                <div>
                    <h3 class="font-black text-titulo">{{ $nombre }}</h3>
                    <p class="text-[10px] text-apoyo">{{ $instrumentos }}</p>
                </div>
            </div>

            @if($eval)
            <div class="rounded-xl border border-borde bg-fondo-panel p-3">
                <div class="text-xs font-bold text-titulo">{{ $eval->instrumento->nombre ?? 'Evaluación' }}</div>
                @if($eval->puntaje_total !== null)
                <div class="text-2xl font-black text-estado-{{ $color }}">{{ $eval->puntaje_total }}</div>
                @endif
                @if($eval->categoria_resultado)
                <div class="text-xs text-apoyo">{{ $eval->categoria_resultado }}</div>
                @endif
                @if($eval->nivel_alerta)
                <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[9px] font-black
                             {{ $eval->nivel_alerta === 'CRITICO' ? 'bg-estado-errorBg text-estado-error' :
                                ($eval->nivel_alerta === 'ALTO' ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-estado-exitoBg text-estado-exito') }}">
                    {{ $eval->nivel_alerta }}
                </span>
                @endif
                <div class="mt-1 text-[10px] text-apoyo">{{ \Carbon\Carbon::parse($eval->fecha_eval)->format('d/m/Y') }}</div>
            </div>
            @else
            <div class="rounded-xl border border-dashed border-borde bg-fondo-panel p-4 text-center">
                <p class="text-xs text-apoyo italic">Sin evaluación registrada</p>
            </div>
            @endif

            <button wire:click="nuevaEvaluacionGeriatrica('{{ $area }}')"
                    class="mt-3 w-full rounded-xl border border-borde bg-fondo-panel py-2 text-xs font-bold text-apoyo hover:bg-estado-{{ $color }}Bg hover:text-estado-{{ $color }} transition">
                <i class="ph-bold ph-plus mr-1"></i> Nueva evaluación
            </button>
        </div>
        @endforeach
    </div>
    @endif

    @livewire('clinica.nota-evolucion-medica-modal')
    @livewire('clinica.registro-signos-vitales-modal')
    @livewire('valoraciones.valoracion-barthel-modal')
    @livewire('valoraciones.evaluacion-geriatrica-area-modal')
</div>
