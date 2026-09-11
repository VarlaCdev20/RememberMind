<div class="space-y-6">
    <!-- Encabezado de Pase de Turno -->
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                <i class="ph-bold ph-handshake text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-titulo">
                    Pase de Turno y Entrega de Guardia
                </h1>
                <p class="text-xs font-semibold text-apoyo">
                    Resumen clínico de actividades realizadas, pendientes, omitidas y alertas activas
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @can('pase_turno.generar')
                <button wire:click="abrirGenerar" class="rm-btn-primary h-10 px-4 text-xs font-bold shadow-sm">
                    <i class="ph-bold ph-plus-circle text-base"></i>
                    <span>Generar Pase de Guardia</span>
                </button>
            @endcan
        </div>
    </div>

    <!-- Stats de Pases de Turno -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Generados Hoy</span>
            <p class="text-2xl font-black text-titulo mt-1">{{ $statsPases['generados'] }}</p>
            <span class="text-[10px] text-apoyo">Pases entregados</span>
        </div>
        <div class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Recibidos Hoy</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $statsPases['recibidos'] }}</p>
            <span class="text-[10px] text-apoyo">Guardias asumidas</span>
        </div>
        <div class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-apoyo">Pendientes de recepción</span>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $statsPases['pendientes'] }}</p>
            <span class="text-[10px] text-apoyo">Esperando confirmación</span>
        </div>
    </div>

    <!-- Filtros de Pases -->
    <div class="flex flex-col gap-3 rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative w-full sm:max-w-xs">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo text-base"></i>
                <input wire:model.live.debounce.300ms="search" type="text"
                    class="rm-input w-full pl-9 text-xs" placeholder="Buscar residente...">
            </div>

            <input type="date" wire:model.live="filtroFecha" class="rm-input w-full sm:w-auto text-xs" />

            <select wire:model.live="filtroEstado" class="rm-select w-full sm:w-auto text-xs">
                <option value="">Todos los Estados</option>
                <option value="GENERADO">Generados (Pendientes)</option>
                <option value="RECIBIDO">Recibidos</option>
            </select>
        </div>
    </div>

    <!-- Lista de Pases de Guardia -->
    @if($pases->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-[24px] border border-dashed border-borde bg-fondo-panel py-16 text-center">
            <i class="ph-bold ph-handshake text-4xl text-apoyo mb-2"></i>
            <h3 class="text-base font-bold text-titulo">No hay pases de turno registrados</h3>
            <p class="text-xs text-apoyo mt-1">Los registros de entrega de guardia generados aparecerán aquí.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($pases as $pase)
                <div class="flex flex-col justify-between rounded-[22px] border border-borde bg-fondo-panel p-5 shadow-panel transition hover:border-boton-acento">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-black text-sm text-titulo">
                                    {{ $pase->adultoMayor?->nombres }} {{ $pase->adultoMayor?->ap_paterno }}
                                </h3>
                                <p class="text-xs text-apoyo">
                                    Hab. {{ $pase->adultoMayor?->habitacion?->codigo ?? 'S/H' }} · {{ $pase->fecha ? $pase->fecha->format('d/m/Y') : '' }}
                                </p>
                            </div>

                            <span class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase border {{ $pase->estado === 'GENERADO' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200' }}">
                                {{ $pase->estado }}
                            </span>
                        </div>

                        <div class="mt-3 rounded-xl bg-fondo-card/50 p-2.5 text-xs text-parrafo border border-borde space-y-1">
                            <p class="flex items-center justify-between">
                                <span class="text-apoyo">De:</span>
                                <strong>{{ $pase->turnoSaliente?->nombre }} ({{ $pase->enfermeroSaliente?->nombres }})</strong>
                            </p>
                            <p class="flex items-center justify-between">
                                <span class="text-apoyo">A:</span>
                                <strong>{{ $pase->turnoEntrante?->nombre }} ({{ $pase->enfermeroEntrante?->nombres }})</strong>
                            </p>
                        </div>

                        <p class="text-xs text-parrafo mt-3 line-clamp-2">
                            {{ $pase->resumen_turno }}
                        </p>

                        @if($pase->requiere_vigilancia_especial)
                            <div class="mt-2 rounded-lg bg-red-50 p-2 text-[10px] font-bold text-red-600 border border-red-200">
                                ⚠ Vigilancia especial: {{ $pase->motivo_vigilancia }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-borde pt-3">
                        <button wire:click="abrirVer('{{ $pase->cod_pase }}')" class="rm-btn-secondary px-3 py-1.5 text-xs">
                            <i class="ph-bold ph-eye"></i> Detalle
                        </button>

                        @if($pase->puedeRecibirse() && ($pase->enfermero_entrante_id === auth()->id() || auth()->user()->hasRole('SUPERADMINISTRADOR')))
                            @can('pase_turno.recibir')
                                <button wire:click="recibirPase('{{ $pase->cod_pase }}')" class="rm-btn-primary px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700">
                                    <i class="ph-bold ph-check"></i> Recibir
                                </button>
                            @endcan
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $pases->links() }}
        </div>
    @endif

    {{-- MODAL GENERAR PASE DE TURNO --}}
    @if($modalGenerar)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                <h3 class="text-base font-black text-titulo">Generar Pase de Turno</h3>
                <p class="text-xs text-apoyo mt-1">Consolide la entrega de guardia y antecedentes clínicos para el enfermero entrante.</p>

                <div class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-parrafo block mb-1">Adulto Mayor *</label>
                            <select wire:model.live="codAm" class="rm-select w-full text-xs">
                                <option value="">Seleccione Residente</option>
                                @foreach($adultos as $ad)
                                    <option value="{{ $ad->cod_am }}">{{ $ad->nombres }} {{ $ad->ap_paterno }}</option>
                                @endforeach
                            </select>
                            @error('codAm') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-bold text-parrafo block mb-1">Estado General al Cierre</label>
                            <input type="text" wire:model="estadoGeneralCierre" placeholder="Ej: Estable, consciente, afebril..." class="rm-input w-full text-xs" />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-bold text-parrafo block mb-1">Turno Saliente *</label>
                            <select wire:model.live="turnoSalienteId" class="rm-select w-full text-xs">
                                <option value="">Seleccione</option>
                                @foreach($turnos as $t)
                                    <option value="{{ $t->cod_turno }}">{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                            @error('turnoSalienteId') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-bold text-parrafo block mb-1">Turno Entrante *</label>
                            <select wire:model="turnoEntranteId" class="rm-select w-full text-xs">
                                <option value="">Seleccione</option>
                                @foreach($turnos as $t)
                                    <option value="{{ $t->cod_turno }}">{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                            @error('turnoEntranteId') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-bold text-parrafo block mb-1">Enfermero Receptor *</label>
                            <select wire:model="enfermeroEntranteId" class="rm-select w-full text-xs">
                                <option value="">Seleccione Profesional</option>
                                @foreach($enfermeros as $u)
                                    <option value="{{ $u->cod_usu }}">{{ $u->nombres }} {{ $u->ap_paterno }}</option>
                                @endforeach
                            </select>
                            @error('enfermeroEntranteId') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-parrafo">Resumen Operativo del Turno *</label>
                            <button type="button" wire:click="autoCompletarResumen" class="text-[10px] font-bold text-boton-acento hover:underline">
                                <i class="ph-bold ph-magic-wand"></i> Regenerar Resumen
                            </button>
                        </div>
                        <textarea wire:model="resumenTurno" rows="3" class="rm-input w-full text-xs" placeholder="Detalles de la guardia, tareas completadas, incidentes ocurridos..."></textarea>
                        @error('resumenTurno') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-bold text-parrafo block mb-1">Recomendaciones para el Siguiente Turno</label>
                        <textarea wire:model="recomendacionSiguienteTurno" rows="2" class="rm-input w-full text-xs" placeholder="Ej.: controlar temperatura a las 18:00 y vigilar la ingesta de líquidos."></textarea>
                    </div>

                    <div class="rounded-2xl border border-borde bg-fondo-card/40 p-4 space-y-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-parrafo cursor-pointer">
                            <input type="checkbox" wire:model.live="requiereVigilanciaEspecial" class="rounded text-red-600" />
                            <span class="text-red-600">Requiere vigilancia especial en la próxima guardia</span>
                        </label>

                        @if($requiereVigilanciaEspecial)
                            <div>
                                <label class="text-xs font-bold text-parrafo block mb-1">Motivo de Vigilancia Especial *</label>
                                <textarea wire:model="motivoVigilancia" rows="2" class="rm-input w-full text-xs" placeholder="Indique la causa clínica de vigilancia (riesgo de caída, desorientación, etc.)."></textarea>
                                @error('motivoVigilancia') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button wire:click="cerrarModales" class="rm-btn-secondary px-4 py-2 text-xs">Cancelar</button>
                    <button wire:click="generarPase" class="rm-btn-primary px-5 py-2 text-xs">Generar Pase</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL VER DETALLE DEL PASE --}}
    @if($modalVer && $detalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
                <div class="flex items-start justify-between border-b border-borde pb-4">
                    <div>
                        <span class="rounded-md px-2 py-0.5 text-[10px] font-black uppercase border {{ $detalle->estado === 'GENERADO' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200' }}">
                            {{ $detalle->estado }}
                        </span>
                        <h3 class="text-base font-black text-titulo mt-1">
                            {{ $detalle->adultoMayor?->nombres }} {{ $detalle->adultoMayor?->ap_paterno }}
                        </h3>
                        <p class="text-xs text-apoyo">Hab. {{ $detalle->adultoMayor?->habitacion?->codigo ?? 'S/H' }} · {{ $detalle->fecha?->format('d/m/Y') }}</p>
                    </div>
                    <button wire:click="cerrarModales" class="rm-btn-secondary h-8 w-8 p-0 justify-center">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 rounded-2xl bg-fondo-card/50 p-3 border border-borde">
                        <div>
                            <span class="text-apoyo block text-[10px] uppercase font-bold">Entrega:</span>
                            <strong class="text-titulo">{{ $detalle->enfermeroSaliente?->nombres }} {{ $detalle->enfermeroSaliente?->ap_paterno }}</strong>
                            <span class="text-apoyo block">Turno: {{ $detalle->turnoSaliente?->nombre }}</span>
                        </div>
                        <div>
                            <span class="text-apoyo block text-[10px] uppercase font-bold">Recibe:</span>
                            <strong class="text-titulo">{{ $detalle->enfermeroEntrante?->nombres }} {{ $detalle->enfermeroEntrante?->ap_paterno }}</strong>
                            <span class="text-apoyo block">Turno: {{ $detalle->turnoEntrante?->nombre }}</span>
                        </div>
                    </div>

                    <div>
                        <strong class="text-parrafo uppercase text-[10px] tracking-wider block mb-1">Resumen del Turno:</strong>
                        <p class="rounded-xl border border-borde bg-fondo-panel p-3 text-parrafo">{{ $detalle->resumen_turno }}</p>
                    </div>

                    @if($detalle->recomendacion_siguiente_turno)
                        <div>
                            <strong class="text-parrafo uppercase text-[10px] tracking-wider block mb-1">Recomendaciones para el siguiente turno:</strong>
                            <p class="rounded-xl border border-borde bg-fondo-panel p-3 text-parrafo">{{ $detalle->recomendacion_siguiente_turno }}</p>
                        </div>
                    @endif

                    @if($detalle->requiere_vigilancia_especial)
                        <div class="rounded-xl bg-red-50 p-3 text-red-700 border border-red-200">
                            <strong class="block text-[10px] uppercase font-bold">Vigilancia Especial Activada:</strong>
                            <p>{{ $detalle->motivo_vigilancia }}</p>
                        </div>
                    @endif

                    {{-- Desglose de tareas y alertas snapshot --}}
                    @foreach(['tareas_realizadas_json' => 'Tareas Realizadas', 'tareas_pendientes_json' => 'Tareas Pendientes', 'alertas_activas_json' => 'Alertas Activas Durante el Turno'] as $campo => $titulo)
                        <div class="border-t border-borde pt-3">
                            <strong class="text-parrafo uppercase text-[10px] tracking-wider block mb-2">{{ $titulo }}</strong>
                            <div class="space-y-1">
                                @forelse($detalle->$campo ?? [] as $fila)
                                    <div class="rounded-lg border border-borde bg-fondo-card/30 p-2 text-xs flex items-center justify-between">
                                        <span>{{ $fila['titulo'] ?? $fila['tipo_alerta'] ?? 'Registro' }}</span>
                                        <span class="text-apoyo font-semibold">{{ $fila['resultado'] ?? $fila['nivel'] ?? $fila['prioridad'] ?? '' }}</span>
                                    </div>
                                @empty
                                    <p class="text-apoyo text-xs">Sin registros adjuntos.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end">
                    <button wire:click="cerrarModales" class="rm-btn-secondary px-4 py-2 text-xs">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
