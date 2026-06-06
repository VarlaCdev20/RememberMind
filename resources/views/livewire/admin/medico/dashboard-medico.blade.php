<div class="space-y-6">
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                <i class="ph-fill ph-heartbeat text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">
                    Panel Médico - Valoraciones Pendientes
                </h2>
                <p class="text-sm font-semibold text-apoyo">
                    Casos derivados desde Triage de Enfermería
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="$refresh" class="rm-btn-secondary h-10 px-4">
                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                <span class="hidden sm:inline">Actualizar</span>
            </button>
        </div>
    </div>

    @if(count($valoracionesPendientes) > 0)
        <div class="rounded-[24px] border border-estado-advertenciaBorde bg-estado-advertenciaBg/30 p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-estado-advertencia/5 rounded-bl-[100px] -z-10 pointer-events-none"></div>
            
            <div class="flex items-center gap-3 mb-5 border-b border-estado-advertenciaBorde/50 pb-3">
                <div class="bg-white p-2 rounded-xl shadow-sm text-estado-advertencia">
                    <i class="ph-bold ph-first-aid text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-estado-advertencia">Pendientes de Valoración Médica</h3>
                    <p class="text-xs text-estado-advertencia/80 font-medium">Hay {{ count($valoracionesPendientes) }} paciente(s) esperando atención del médico general.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-estado-advertenciaBorde bg-white">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-fondo-tabla text-apoyo uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Paciente</th>
                            <th class="px-5 py-4">Procedencia</th>
                            <th class="px-5 py-4">Motivo / Prioridad</th>
                            <th class="px-5 py-4 max-w-[200px]">Resumen Enfermería</th>
                            <th class="px-5 py-4">Fecha Triage</th>
                            <th class="px-5 py-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-estado-advertenciaBorde/50">
                        @foreach($valoracionesPendientes as $paciente)
                            @php
                                $actividadEnfermeria = Spatie\Activitylog\Models\Activity::where('subject_type', App\Models\AdultoMayor::class)
                                    ->where('subject_id', $paciente->cod_am)
                                    ->where('log_name', 'Enfermeria')
                                    ->latest()->first();
                                $propiedades = $actividadEnfermeria ? $actividadEnfermeria->properties : collect([]);
                                $recomendacion = $propiedades['recomendacion'] ?? 'No registrada';
                                $estadoGeneral = $propiedades['estado_general'] ?? 'No registrado';
                            @endphp
                            <tr class="hover:bg-estado-advertenciaBg/20 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-full bg-estado-advertencia/10 flex items-center justify-center text-estado-advertencia font-bold">
                                            {{ substr($paciente->nombres, 0, 1) }}{{ substr($paciente->apellidos ?? $paciente->ap_paterno, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-titulo">{{ $paciente->nombres }} {{ $paciente->apellidos ?? $paciente->ap_paterno }}</div>
                                            <div class="text-[10px] text-apoyo uppercase">
                                                {{ \Carbon\Carbon::parse($paciente->fecha_nac)->age }} años | CI: {{ $paciente->ci }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="text-xs font-semibold text-titulo">{{ $paciente->procedencia ?? 'NO ESPECIFICADO' }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="text-xs font-semibold text-titulo">{{ $paciente->motivo_ingreso ?? 'NO ESPECIFICADO' }}</div>
                                    @php
                                        $prioridadClass = match($estadoGeneral) {
                                            'CRITICO' => 'bg-estado-peligro text-white',
                                            'DELICADO' => 'bg-estado-advertencia text-white',
                                            default => 'bg-borde text-titulo'
                                        };
                                    @endphp
                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $prioridadClass }}">
                                        Estado: {{ $estadoGeneral }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 max-w-[200px] truncate" title="{{ $recomendacion }}">
                                    <div class="text-xs text-parrafo italic border-l-2 border-estado-advertencia pl-2">
                                        "{{ $recomendacion }}"
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="text-xs font-medium text-titulo">{{ $actividadEnfermeria ? $actividadEnfermeria->created_at->format('d/m/Y H:i') : 'Sin registro' }}</div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button class="h-8 w-8 rounded-lg bg-borde/50 text-parrafo hover:bg-boton-acento hover:text-white transition-colors flex items-center justify-center tooltip-btn" title="Ver resumen de preadmisión">
                                            <i class="ph-bold ph-file-text text-sm"></i>
                                        </button>
                                        <button class="h-8 w-8 rounded-lg bg-estado-infoBg text-estado-info hover:bg-estado-info hover:text-white transition-colors flex items-center justify-center tooltip-btn" title="Ver valoración inicial de enfermería">
                                            <i class="ph-bold ph-stethoscope text-sm"></i>
                                        </button>
                                        
                                        @if($paciente->estado->estado === 'VALORACION_MEDICA')
                                            <button wire:click="iniciarValoracionMedica('{{ $paciente->cod_am }}')" class="h-8 px-3 rounded-lg bg-estado-advertencia text-white font-bold text-xs hover:bg-estado-advertencia/80 transition-colors shadow-sm whitespace-nowrap">
                                                Iniciar Médico
                                            </button>
                                        @elseif($paciente->estado->estado === 'DECISION_ADMISION')
                                            <button wire:click="abrirDecisionAdmision('{{ $paciente->cod_am }}')" class="h-8 px-3 rounded-lg bg-estado-exito text-white font-bold text-xs hover:bg-estado-exito/80 transition-colors shadow-sm whitespace-nowrap">
                                                Dictamen Final
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-borde p-12 text-center flex flex-col items-center justify-center bg-fondo-card/50">
            <div class="h-16 w-16 bg-estado-exitoBg text-estado-exito rounded-full flex items-center justify-center mb-4">
                <i class="ph-bold ph-check text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-titulo mb-1">Sin valoraciones médicas pendientes</h3>
            <p class="text-sm text-apoyo max-w-sm">No hay pacientes esperando evaluación médica en este momento.</p>
        </div>
    @endif

    @livewire('admin.medico.valoracion-medica-modal')
    @livewire('admin.medico.decision-admision-modal')
</div>
