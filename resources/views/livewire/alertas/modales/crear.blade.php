@if($modalCrear)
<div class="rm-modal-backdrop"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-crear-title"
     x-data
     x-on:keydown.escape.window="$wire.cerrarModales()">
    <!-- Backdrop dismiss -->
    <div class="fixed inset-0" wire:click="cerrarModales"></div>

    <!-- Modal Card Grande / Mediano -->
    <div class="rm-modal rm-modal-lg z-10 font-sans shadow-2xl rounded-2xl overflow-hidden border border-[var(--rm-border)] dark:border-slate-700/80 bg-[var(--rm-surface)] dark:bg-[#18222A]" @click.stop>
        <!-- Header -->
        <div class="rm-modal-header shrink-0 px-5 py-3.5 border-b border-[var(--rm-border)] dark:border-slate-700/80 bg-[var(--rm-surface-soft)] dark:bg-[#131B22] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 shadow-xs">
                    <i class="ph-bold ph-bell-ringing text-xl"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-700/50 shadow-xs flex items-center gap-1">
                            <i class="ph-bold ph-plus"></i> Formulario de Registro de Alerta
                        </span>
                    </div>
                    <h3 id="modal-crear-title" class="text-base font-bold text-[var(--rm-text-title)] dark:text-slate-100">
                        Registrar Nueva Alerta Clínica
                    </h3>
                    <p class="text-xs text-[var(--rm-text-muted)] dark:text-slate-400">
                        Generación de evento asistencial con notificación inmediata al personal de turno
                    </p>
                </div>
            </div>
            <button type="button"
                wire:click="cerrarModales"
                aria-label="Cerrar modal"
                class="h-8 w-8 rounded-lg flex items-center justify-center text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-200/50 dark:hover:bg-slate-800 transition cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="rm-modal-body flex-1 min-h-0 overflow-y-auto p-5 sm:p-6 space-y-4 text-xs bg-[var(--rm-surface)] dark:bg-[#18222A]">
            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <i class="ph-bold ph-warning-octagon text-base"></i>
                        <span>Por favor complete los campos requeridos correctamente:</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-0.5 text-[11.5px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- SECCIÓN 1: RESIDENTE ASIGNADO -->
            <div class="p-4 rounded-xl bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)] space-y-2.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)] dark:text-slate-200 flex items-center gap-1.5">
                    <i class="ph-bold ph-user text-[var(--rm-primary)] dark:text-blue-400"></i>
                    1. Selección del Residente
                </span>
                
                <div class="rm-field">
                    <label for="selectResidente" class="block text-xs font-semibold text-[var(--rm-text-title)] dark:text-slate-200 mb-1">
                        Adulto Mayor Asignado <span class="text-rose-500">*</span>
                    </label>
                    <select id="selectResidente"
                        wire:model.live="codAm"
                        class="rm-select text-xs @error('codAm') border-rose-500 dark:border-rose-500 @enderror">
                        <option value="">-- Seleccione un residente asistido --</option>
                        @foreach($adultos as $ad)
                            <option value="{{ $ad->cod_am }}">
                                {{ $ad->ap_paterno }} {{ $ad->ap_materno }} {{ $ad->nombres }} 
                                ({{ $ad->ubicacion_texto }})
                            </option>
                        @endforeach
                    </select>
                    @error('codAm')
                        <span class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>

            <!-- SECCIÓN 2: CATEGORIZACIÓN Y PRIORIDAD -->
            <div class="p-4 rounded-xl bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)] space-y-3.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)] dark:text-slate-200 flex items-center gap-1.5">
                    <i class="ph-bold ph-sliders-horizontal text-[var(--rm-accent)] dark:text-orange-400"></i>
                    2. Parámetros y Severidad Clínica
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Origen Clínico --}}
                    <div>
                        <label for="selectOrigen" class="block text-xs font-semibold text-[var(--rm-text-title)] dark:text-slate-200 mb-1">
                            Origen del Evento <span class="text-rose-500">*</span>
                        </label>
                        <select id="selectOrigen"
                            wire:model="origen"
                            class="rm-select text-xs @error('origen') border-rose-500 dark:border-rose-500 @enderror">
                            <option value="SIGNOS">Signos Vitales Alterados</option>
                            <option value="MEDICACION">Administración de Fármaco</option>
                            <option value="INCIDENTE">Incidente / Caída Asistencial</option>
                            <option value="MANUAL">Reporte de Turno / Manual</option>
                            <option value="PLAN">Plan de Cuidados Continuos</option>
                            <option value="SEGUIMIENTO">Seguimiento Clínico Especial</option>
                            <option value="SOLICITUD_MEDICA">Solicitud Médica Directa</option>
                            <option value="FICHA">Ficha Clínica Integral</option>
                            <option value="VALORACION">Valoración Funcional</option>
                        </select>
                        @error('origen')
                            <span class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1 flex items-center gap-1">
                                <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Tipo o Diagnóstico --}}
                    <div>
                        <label for="inputTipoAlerta" class="block text-xs font-semibold text-[var(--rm-text-title)] dark:text-slate-200 mb-1">
                            Tipo o Diagnóstico Clínico <span class="text-rose-500">*</span>
                        </label>
                        <input id="inputTipoAlerta"
                            type="text"
                            wire:model="tipoAlerta"
                            placeholder="Ej.: CRISIS_HIPERTENSIVA, PICO_FEBRIL..."
                            class="rm-input text-xs font-mono uppercase @error('tipoAlerta') border-rose-500 dark:border-rose-500 @enderror" />
                        @error('tipoAlerta')
                            <span class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1 flex items-center gap-1">
                                <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>

                {{-- Nivel de Severidad --}}
                <div>
                    <label class="block text-xs font-semibold text-[var(--rm-text-title)] dark:text-slate-200 mb-2">
                        Nivel de Severidad y Prioridad Asistencial <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        {{-- Crítico --}}
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $nivel === 'CRITICO' ? 'border-rose-500 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 shadow-md ring-2 ring-rose-300 dark:ring-rose-800' : 'border-[var(--rm-border)] bg-[var(--rm-surface)] text-[var(--rm-text-title)] hover:border-rose-400' }}">
                            <input type="radio" wire:model.live="nivel" value="CRITICO" class="sr-only" />
                            <i class="ph-bold ph-warning-octagon text-xl mb-1 text-rose-500"></i>
                            <span class="text-xs font-bold">Crítico</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">Riesgo vital</span>
                        </label>

                        {{-- Alto --}}
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $nivel === 'ALTO' ? 'border-orange-500 bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 shadow-md ring-2 ring-orange-300 dark:ring-orange-800' : 'border-[var(--rm-border)] bg-[var(--rm-surface)] text-[var(--rm-text-title)] hover:border-orange-400' }}">
                            <input type="radio" wire:model.live="nivel" value="ALTO" class="sr-only" />
                            <i class="ph-bold ph-warning text-xl mb-1 text-orange-500"></i>
                            <span class="text-xs font-bold">Alto</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">Urgente</span>
                        </label>

                        {{-- Medio --}}
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $nivel === 'MEDIO' ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 shadow-md ring-2 ring-amber-300 dark:ring-amber-800' : 'border-[var(--rm-border)] bg-[var(--rm-surface)] text-[var(--rm-text-title)] hover:border-amber-400' }}">
                            <input type="radio" wire:model.live="nivel" value="MEDIO" class="sr-only" />
                            <i class="ph-bold ph-info text-xl mb-1 text-amber-500"></i>
                            <span class="text-xs font-bold">Medio</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">En turno</span>
                        </label>

                        {{-- Bajo --}}
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $nivel === 'BAJO' ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 shadow-md ring-2 ring-blue-300 dark:ring-blue-800' : 'border-[var(--rm-border)] bg-[var(--rm-surface)] text-[var(--rm-text-title)] hover:border-blue-400' }}">
                            <input type="radio" wire:model.live="nivel" value="BAJO" class="sr-only" />
                            <i class="ph-bold ph-check-circle text-xl mb-1 text-blue-500"></i>
                            <span class="text-xs font-bold">Bajo</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">Preventivo</span>
                        </label>
                    </div>
                    @error('nivel')
                        <span class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>

            <!-- SECCIÓN 3: DESCRIPCIÓN Y MOTIVO CLÍNICO -->
            <div class="p-4 rounded-xl bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)] space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)] dark:text-slate-200 flex items-center gap-1.5">
                    <i class="ph-bold ph-note-pencil text-emerald-600 dark:text-emerald-400"></i>
                    3. Justificación y Motivo Clínico
                </span>
                
                <div class="rm-field">
                    <label for="textareaMotivo" class="block text-xs font-semibold text-[var(--rm-text-title)] dark:text-slate-200 mb-1 flex items-center justify-between">
                        <span>Descripción del Cuadro Clínico o Incidente <span class="text-rose-500">*</span></span>
                        <span class="text-[10.5px] text-[var(--rm-text-muted)] dark:text-slate-400 font-normal">Mín. 10 caracteres</span>
                    </label>
                    <textarea id="textareaMotivo"
                        wire:model="motivo"
                        rows="3"
                        class="rm-textarea text-xs @error('motivo') border-rose-500 dark:border-rose-500 @enderror"
                        placeholder="Detalle los hallazgos observados, constantes vitales alteradas, sintomatología del residente o la causa que motiva esta alerta..."></textarea>
                    @error('motivo')
                        <span class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="rm-modal-footer shrink-0 px-5 py-3 border-t border-[var(--rm-border)] dark:border-slate-700/80 bg-[var(--rm-surface-soft)] dark:bg-[#131B22] flex items-center justify-between">
            <button type="button"
                wire:click="cerrarModales"
                class="px-4 py-2 rounded-xl text-xs font-bold text-[var(--rm-text-muted)] dark:text-slate-400 hover:text-[var(--rm-text-title)] dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-800 transition cursor-pointer">
                Cancelar
            </button>

            <button type="button"
                wire:click="guardarAlerta"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-[#B35E43] hover:bg-[#9E4E36] text-white shadow-md transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                <span wire:loading.remove wire:target="guardarAlerta" class="flex items-center gap-1.5">
                    <i class="ph-bold ph-floppy-disk text-base"></i>
                    <span>Guardar Alerta Clínica</span>
                </span>
                <span wire:loading wire:target="guardarAlerta" class="flex items-center gap-1.5">
                    <i class="ph ph-arrows-clockwise text-base animate-spin"></i>
                    <span>Registrando alerta...</span>
                </span>
            </button>
        </div>
    </div>
</div>
@endif
