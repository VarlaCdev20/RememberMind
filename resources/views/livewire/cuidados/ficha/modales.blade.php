{{-- MODALES CLÍNICOS OPERATIVOS (CENTRALIZADOS EN LA FICHA 360°) --}}

{{-- 1. MODAL REGISTRO DE SIGNOS VITALES --}}
@if($modalSignos)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
            <h3 class="text-base font-bold text-titulo">Registrar Control de Signos Vitales</h3>
            <p class="text-xs text-apoyo mt-1">Parámetros fisiológicos y hemodinámicos del residente.</p>

            <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="font-bold text-parrafo block mb-1">Presión Arterial (PA) *</label>
                    <input type="text" wire:model="signoPA" placeholder="120/80" class="rm-input w-full text-xs" />
                    @error('signoPA') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-parrafo block mb-1">Frecuencia Cardaca (FC)</label>
                    <input type="number" wire:model="signoFC" placeholder="75" class="rm-input w-full text-xs" />
                    @error('signoFC') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-parrafo block mb-1">Frecuencia Resp. (FR)</label>
                    <input type="number" wire:model="signoFR" placeholder="18" class="rm-input w-full text-xs" />
                    @error('signoFR') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-parrafo block mb-1">Temperatura (°C)</label>
                    <input type="number" step="0.1" wire:model="signoTemp" placeholder="36.5" class="rm-input w-full text-xs" />
                    @error('signoTemp') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-parrafo block mb-1">Saturación SpO2 (%)</label>
                    <input type="number" wire:model="signoSat" placeholder="98" class="rm-input w-full text-xs" />
                    @error('signoSat') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-parrafo block mb-1">Glucosa (mg/dL)</label>
                    <input type="number" wire:model="signoGlucosa" placeholder="105" class="rm-input w-full text-xs" />
                    @error('signoGlucosa') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="font-bold text-parrafo block mb-1">Nivel de Dolor (Escala EVA 0 a 10)</label>
                    <div class="flex items-center gap-3">
                        <input type="range" min="0" max="10" wire:model.live="signoDolor" class="w-full accent-blue-600" />
                        <span class="font-black text-sm px-2.5 py-1 rounded-lg border border-borde bg-fondo-card text-titulo min-w-[3rem] text-center">
                            {{ $signoDolor ?? 0 }}/10
                        </span>
                    </div>
                </div>
                <div class="col-span-2">
                    <label class="font-bold text-parrafo block mb-1">Observaciones</label>
                    <textarea wire:model="signoObs" rows="2" placeholder="Notas clínicas adicionales..." class="rm-input w-full text-xs"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalSignos" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarSignos" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    <span wire:loading.remove wire:target="guardarSignos">Guardar signos</span>
                    <span wire:loading wire:target="guardarSignos">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 2. MODAL ADMINISTRACIÓN DE MEDICACIÓN --}}
@if($modalMed)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
            <h3 class="text-base font-bold text-titulo">Administración de Medicación</h3>
            <div class="mt-2 rounded-xl bg-fondo-card p-3 border border-borde text-xs space-y-1">
                <p class="font-bold text-titulo text-sm">{{ $medNombre }}</p>
                <p class="text-parrafo">Dosis: <strong>{{ $medDosis }}</strong> · Vía: <strong>{{ $medVia }}</strong></p>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-parrafo block mb-1">Acción Asistencial *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                wire:click="$set('medAccion', 'ADMINISTRAR')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'ADMINISTRAR' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-fondo-card text-apoyo border-borde' }}">
                            <i class="ph-bold ph-check mr-1"></i> Administrada
                        </button>
                        <button type="button"
                                wire:click="$set('medAccion', 'OMITIR')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'OMITIR' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-fondo-card text-apoyo border-borde' }}">
                            <i class="ph-bold ph-warning mr-1"></i> Omitida / Rechazo
                        </button>
                    </div>
                </div>

                @if($medAccion === 'OMITIR')
                    <div>
                        <label class="font-bold text-rose-700 block mb-1">Motivo de Omisión *</label>
                        <textarea wire:model="medMotivoOmision" rows="2" placeholder="Rechazo del paciente, náuseas, etc." class="rm-input w-full text-xs"></textarea>
                        @error('medMotivoOmision') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div>
                    <label class="font-bold text-parrafo block mb-1">Observaciones / Efectos Observados</label>
                    <textarea wire:model="medEfectoObs" rows="2" placeholder="Tolerancia adecuada..." class="rm-input w-full text-xs"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalMedicacion" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarMedicacion" wire:loading.attr="disabled" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition">
                    <span wire:loading.remove wire:target="guardarMedicacion">Confirmar registro</span>
                    <span wire:loading wire:target="guardarMedicacion">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 3. MODAL REGISTRAR EJECUCIÓN DE TAREA DE CUIDADO --}}
@if($modalTarea)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
            <h3 class="text-base font-bold text-titulo">Registrar Actividad de Cuidado</h3>
            <p class="text-xs text-parrafo font-bold mt-1.5">{{ $tareaTitulo }}</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-parrafo block mb-1">Estado de Ejecución *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                wire:click="$set('tareaEstadoAccion', 'REALIZADA')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $tareaEstadoAccion === 'REALIZADA' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-fondo-card text-apoyo border-borde' }}">
                            <i class="ph-bold ph-check-circle mr-1"></i> Realizada
                        </button>
                        <button type="button"
                                wire:click="$set('tareaEstadoAccion', 'OMITIDA')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $tareaEstadoAccion === 'OMITIDA' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-fondo-card text-apoyo border-borde' }}">
                            <i class="ph-bold ph-prohibit mr-1"></i> Omitida
                        </button>
                    </div>
                </div>

                @if($tareaEstadoAccion === 'REALIZADA')
                    <div>
                        <label class="font-bold text-parrafo block mb-1">Resultado / Observación de la Tarea</label>
                        <textarea wire:model="tareaResultado" rows="2" placeholder="Ej: Realizado sin inconvenientes, residente colaborador..." class="rm-input w-full text-xs"></textarea>
                    </div>
                @else
                    <div>
                        <label class="font-bold text-rose-700 block mb-1">Motivo de Omisión *</label>
                        <textarea wire:model="tareaMotivoOmision" rows="2" placeholder="Motivo de no realización..." class="rm-input w-full text-xs"></textarea>
                        @error('tareaMotivoOmision') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalTarea" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarTarea" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    <span wire:loading.remove wire:target="guardarTarea">Guardar registro</span>
                    <span wire:loading wire:target="guardarTarea">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 4. MODAL REGISTRAR SEGUIMIENTO DIARIO --}}
@if($modalSeguimiento)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
            <h3 class="text-base font-bold text-titulo">Registrar Seguimiento Diario</h3>
            <p class="text-xs text-apoyo mt-1">Evolución, ingesta, confort y estado cognitivo del residente.</p>

            <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="font-bold text-parrafo block mb-1">Estado General *</label>
                    <select wire:model="segEstado" class="rm-input w-full text-xs">
                        <option value="ESTABLE">Estable</option>
                        <option value="VIGILANCIA">En Vigilancia</option>
                        <option value="DELICADO">Delicado</option>
                        <option value="CRITICO">Crítico</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-parrafo block mb-1">Alimentación / Apetito</label>
                    <select wire:model="segAlimentacion" class="rm-input w-full text-xs">
                        <option value="COMPLETA">Completa / Buena ingesta</option>
                        <option value="PARCIAL">Parcial / Regular</option>
                        <option value="RECHAZADA">Rechazada</option>
                        <option value="AYUNO">Ayuno indicado</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-parrafo block mb-1">Movilidad</label>
                    <select wire:model="segMovilidad" class="rm-input w-full text-xs">
                        <option value="INDEPENDIENTE">Independiente</option>
                        <option value="ASISTIDA">Asistida</option>
                        <option value="SILLA_RUEDAS">Silla de ruedas</option>
                        <option value="ENCAMADO">Encamado</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-parrafo block mb-1">Patrón de Sueño</label>
                    <select wire:model="segSueno" class="rm-input w-full text-xs">
                        <option value="NORMAL">Normal / Reparador</option>
                        <option value="INTERRUMPIDO">Interrumpido</option>
                        <option value="INSOMNIO">Insomnio</option>
                        <option value="SOMNOLENCIA">Somnolencia / Sedación</option>
                    </select>
                </div>

                <div class="col-span-2 space-y-2 py-1">
                    <label class="flex items-center gap-2 font-bold text-rose-700 cursor-pointer">
                        <input type="checkbox" wire:model="segIncidente" class="rounded text-rose-600 focus:ring-rose-500" />
                        <span>Ocurrió un incidente durante este seguimiento</span>
                    </label>
                    <label class="flex items-center gap-2 font-bold text-amber-700 cursor-pointer">
                        <input type="checkbox" wire:model="segRequiereMedico" class="rounded text-amber-600 focus:ring-amber-500" />
                        <span>Requiere revisión o valoración médica</span>
                    </label>
                </div>

                <div class="col-span-2">
                    <label class="font-bold text-parrafo block mb-1">Observaciones Asistenciales</label>
                    <textarea wire:model="segObs" rows="3" placeholder="Detalle de conducta, hidratación o incidencias..." class="rm-input w-full text-xs"></textarea>
                    @error('segObs') <span class="text-estado-peligro text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalSeguimiento" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarSeguimiento" wire:loading.attr="disabled" class="rounded-xl bg-sky-600 px-4 py-2 text-xs font-bold text-white hover:bg-sky-700 transition">
                    <span wire:loading.remove wire:target="guardarSeguimiento">Guardar seguimiento</span>
                    <span wire:loading wire:target="guardarSeguimiento">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 5. MODAL REPORTAR INCIDENTE --}}
@if($modalIncidente)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
            <h3 class="text-base font-bold text-rose-700 flex items-center gap-2">
                <i class="ph-bold ph-warning-octagon text-lg"></i>
                <span>Reportar Incidente Asistencial</span>
            </h3>
            <p class="text-xs text-apoyo mt-1">Generación de alerta clínica inmediata para el equipo.</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-parrafo block mb-1">Tipo de Incidente *</label>
                    <select wire:model="incidenteTipo" class="rm-input w-full text-xs">
                        <option value="INCIDENTE">Incidente General</option>
                        <option value="CAIDA">Caída o Tropiezo</option>
                        <option value="CONDUCTA">Agitación / Desorientación</option>
                        <option value="DOLOR_AGUDO">Dolor Agudo / Malestar Súbito</option>
                        <option value="DESVIACION_CLINICA">Desviación Clínica / Síntoma Inesperado</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-parrafo block mb-1">Nivel de Severidad *</label>
                    <select wire:model="incidenteNivel" class="rm-input w-full text-xs">
                        <option value="ALTO">Alto (Atención Inmediata)</option>
                        <option value="MEDIO">Medio (Vigilancia Estricta)</option>
                        <option value="BAJO">Bajo (Reporte de Rutina)</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-parrafo block mb-1">Descripción de lo Sucedido *</label>
                    <textarea wire:model="incidenteMotivo" rows="3" placeholder="Circunstancias, estado físico y medidas inmediatas adoptadas..." class="rm-input w-full text-xs"></textarea>
                    @error('incidenteMotivo') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalIncidente" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarIncidente" wire:loading.attr="disabled" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition">
                    <span wire:loading.remove wire:target="guardarIncidente">Emitir reporte de alerta</span>
                    <span wire:loading wire:target="guardarIncidente">Emitiendo...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 6. MODAL ATENDER ALERTA --}}
@if($modalAtenderAlerta)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
            <h3 class="text-base font-bold text-titulo">Atender Alerta Clínica</h3>
            <p class="text-xs text-apoyo mt-1">Registrar primera intervención y pasar alerta a estado EN ATENCIÓN.</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-parrafo block mb-1">Acción Inmediata Realizada *</label>
                    <textarea wire:model="accionTomadaAlerta" rows="3" placeholder="Ej: Se acomodó en cama, se verificó vía aérea, se administró medicación indicada..." class="rm-input w-full text-xs"></textarea>
                    @error('accionTomadaAlerta') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalAtenderAlerta" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarAtenderAlerta" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    <span wire:loading.remove wire:target="guardarAtenderAlerta">Registrar atención</span>
                    <span wire:loading wire:target="guardarAtenderAlerta">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 7. MODAL CERRAR ALERTA --}}
@if($modalCerrarAlerta)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-borde bg-fondo-panel p-6 shadow-panel">
            <h3 class="text-base font-bold text-titulo">Cerrar Alerta Clínica</h3>
            <p class="text-xs text-apoyo mt-1">Confirme la estabilización del residente y el cierre del evento.</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-parrafo block mb-1">Observación de Cierre / Resolución *</label>
                    <textarea wire:model="observacionCierreAlerta" rows="3" placeholder="Parámetros normalizados, residente estable..." class="rm-input w-full text-xs"></textarea>
                    @error('observacionCierreAlerta') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalCerrarAlerta" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarCerrarAlerta" wire:loading.attr="disabled" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition">
                    <span wire:loading.remove wire:target="guardarCerrarAlerta">Cerrar alerta</span>
                    <span wire:loading wire:target="guardarCerrarAlerta">Cerrando...</span>
                </button>
            </div>
        </div>
    </div>
@endif
