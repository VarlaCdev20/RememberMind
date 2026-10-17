<div class="rm-incidents-workspace font-sans">
    @include('livewire.cuidados.partials.incidentes-listado')
    {{-- ======================================================== --}}
    {{-- MODAL 1: REGISTRAR INCIDENCIA (CENTRADO, OVERLAY SUAVE)  --}}
    {{-- ======================================================== --}}
    @if($modalRegistrar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
            <div class="relative w-full max-w-3xl max-h-[90vh] flex flex-col rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] shadow-2xl overflow-hidden my-auto"
                @click.outside="$wire.cerrarModalRegistro()">

                {{-- Cabecera del Modal --}}
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] px-5 py-4">
                    <div class="flex items-center gap-2.5">
                        <i class="ph-bold ph-warning-octagon text-xl text-[var(--rm-warning)]"></i>
                        <div>
                            <h2 class="text-base font-black text-[var(--rm-text-primary)]">
                                Registrar incidencia
                            </h2>
                            <p class="text-[11px] font-medium text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                Documente objetivamente el evento ocurrido durante el cuidado
                            </p>
                        </div>
                    </div>
                    <button wire:click="cerrarModalRegistro"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--rm-text-secondary)] hover:bg-[var(--rm-border)]/50 dark:hover:bg-[var(--rm-surface)] transition">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                {{-- Cuerpo con scroll --}}
                <div class="p-5 sm:p-6 space-y-6 overflow-y-auto flex-1 text-xs text-[var(--rm-text-primary)] dark:text-[var(--rm-surface-soft)]">

                    {{-- SECCIÓN 1: RESIDENTE --}}
                    <div class="space-y-3 rounded-xl bg-[var(--rm-surface-soft)]/50 dark:bg-[var(--rm-surface)]/50 p-4 border border-[var(--rm-border)]/60 dark:border-[var(--rm-text-body)]">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs uppercase tracking-wider text-[var(--rm-warning)] dark:text-[var(--rm-warning)] flex items-center gap-1.5">
                                <i class="ph-bold ph-user-circle text-sm"></i> Sección 1 — Residente
                            </span>
                            @if($residenteSeleccionado)
                                <button type="button" wire:click="quitarResidente"
                                    class="text-[11px] font-bold text-[var(--rm-warning)] hover:underline">
                                    Cambiar residente
                                </button>
                            @endif
                        </div>

                        @if(!$residenteSeleccionado)
                            {{-- Selector / Buscador de Residente --}}
                            <div class="space-y-2">
                                <label class="font-semibold block text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                    Buscar / seleccionar residente *
                                </label>
                                <div class="relative">
                                    <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[var(--rm-text-secondary)]"></i>
                                    <input wire:model.live.debounce.300ms="searchResidente"
                                        type="text"
                                        placeholder="Escriba nombre o cédula para filtrar..."
                                        class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 pl-9 pr-3 text-xs text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none">
                                </div>

                                {{-- Lista de coincidencias --}}
                                <div class="max-h-40 overflow-y-auto rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] divide-y divide-[var(--rm-border)]/40">
                                    @forelse($residentesDisponibles as $res)
                                        <button type="button" wire:click="seleccionarResidente('{{ $res->cod_residente }}')"
                                            class="w-full flex items-center justify-between p-2.5 text-left hover:bg-[var(--rm-surface-soft)] dark:hover:bg-[var(--rm-surface)] transition">
                                            <div>
                                                <span class="font-bold block text-[var(--rm-text-primary)]">
                                                    {{ $res->nombres }} {{ $res->apellido_paterno }} {{ $res->apellido_materno }}
                                                </span>
                                                <span class="text-[11px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                                    CI: {{ $res->documento_identidad ?? 'S/D' }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-semibold text-[var(--rm-warning)] dark:text-[var(--rm-warning)]">
                                                {{ $res->ubicacion_formateada }}
                                            </span>
                                        </button>
                                    @empty
                                        <div class="p-3 text-center text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                            No se encontraron residentes con ese criterio.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            {{-- Residente Seleccionado (Solo lectura) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] p-3 rounded-xl border border-[var(--rm-border)]">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] block">Nombre Completo:</span>
                                    <span class="font-bold text-sm text-[var(--rm-text-primary)]">
                                        {{ $residenteSeleccionado->nombres }} {{ $residenteSeleccionado->apellido_paterno }} {{ $residenteSeleccionado->apellido_materno }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] block">Habitación / Cama actual:</span>
                                    <span class="font-bold text-xs text-[var(--rm-warning)] dark:text-[var(--rm-warning)]">
                                        {{ $residenteSeleccionado->ubicacion_formateada }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] block">Profesional autenticado:</span>
                                    <span class="font-semibold text-xs text-[var(--rm-text-primary)]">
                                        {{ auth()->user()->name ?? 'Personal de guardia' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-[var(--rm-text-secondary)] block">Contexto de Jornada:</span>
                                    <span class="font-semibold text-xs text-[var(--rm-action-primary)] dark:text-[var(--rm-success)]">
                                        Turno activo registrado
                                    </span>
                                </div>
                            </div>
                        @endif

                        @error('cod_residente')
                            <span class="text-[11px] font-bold text-[var(--rm-danger)] block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- SECCIÓN 2: DATOS DEL INCIDENTE --}}
                    <div class="space-y-4">
                        <span class="font-bold text-xs uppercase tracking-wider text-[var(--rm-warning)] dark:text-[var(--rm-warning)] flex items-center gap-1.5">
                            <i class="ph-bold ph-notepad text-sm"></i> Sección 2 — Datos del Incidente
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Tipo de incidente --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Tipo de incidente *</label>
                                <select wire:model.live="tipo_incidente"
                                    class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs font-semibold text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none">
                                    <option value="">Seleccione el tipo...</option>
                                    @foreach($tiposFrecuentes as $tf)
                                        <option value="{{ $tf }}">{{ $tf }}</option>
                                    @endforeach
                                </select>
                                @error('tipo_incidente')
                                    <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Gravedad --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Gravedad *</label>
                                <select wire:model.live="gravedad"
                                    class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs font-semibold text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none">
                                    @foreach($gravedadesDisponibles as $keyG => $labelG)
                                        <option value="{{ $keyG }}">{{ $labelG }}</option>
                                    @endforeach
                                </select>
                                @error('gravedad')
                                    <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Especificar si es "OTRO" --}}
                        @if($tipo_incidente === 'OTRO')
                            <div class="space-y-1 bg-[var(--rm-surface)] dark:bg-[var(--rm-surface-soft)] p-3 rounded-xl border border-[var(--rm-warning)]/40">
                                <label class="font-bold text-[var(--rm-warning)] dark:text-[var(--rm-warning)]">Especifique el tipo de incidente * (Máximo 60 caracteres)</label>
                                <input wire:model="tipo_incidente_otro"
                                    type="text"
                                    maxlength="60"
                                    placeholder="Ej: Desorientación súbita en comedor..."
                                    class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none">
                                @error('tipo_incidente_otro')
                                    <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            {{-- Lugar --}}
                            <div class="space-y-1 sm:col-span-1">
                                <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Lugar del evento</label>
                                <input wire:model="lugar"
                                    type="text"
                                    maxlength="120"
                                    placeholder="Ej: Pasillo norte, baño, comedor..."
                                    class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none">
                                @error('lugar')
                                    <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Fecha incidente --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Fecha del incidente *</label>
                                <input wire:model="fecha_incidente"
                                    type="date"
                                    max="{{ date('Y-m-d') }}"
                                    class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none">
                                @error('fecha_incidente')
                                    <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Hora incidente --}}
                            <div class="space-y-1">
                                <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Hora del incidente *</label>
                                <input wire:model="hora_incidente"
                                    type="time"
                                    class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none">
                                @error('hora_incidente')
                                    <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: DESCRIPCIÓN DEL EVENTO --}}
                    <div class="space-y-4">
                        <span class="font-bold text-xs uppercase tracking-wider text-[var(--rm-warning)] dark:text-[var(--rm-warning)] flex items-center gap-1.5">
                            <i class="ph-bold ph-chat-text text-sm"></i> Sección 3 — Descripción del Evento
                        </span>

                        <div class="space-y-1">
                            <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                Descripción objetiva de lo ocurrido *
                            </label>
                            <textarea wire:model="descripcion"
                                rows="3"
                                placeholder="Relate con precisión objetiva los hechos observados, estado del paciente y circunstancias..."
                                class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] p-3 text-xs text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none"></textarea>
                            @error('descripcion')
                                <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                Medida inmediata realizada
                            </label>
                            <textarea wire:model="medida_inmediata"
                                rows="2"
                                placeholder="Indique qué acción realizó inmediatamente (ej: toma de constantes, curación, reposo en cama, aviso a supervisión)..."
                                class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] p-3 text-xs text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none"></textarea>
                            @error('medida_inmediata')
                                <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- SECCIÓN 4: ESCALAMIENTO --}}
                    <div class="space-y-4 rounded-xl bg-[var(--rm-surface-soft)]/50 dark:bg-[var(--rm-surface)]/50 p-4 border border-[var(--rm-border)]/60 dark:border-[var(--rm-text-body)]">
                        <span class="font-bold text-xs uppercase tracking-wider text-[var(--rm-warning)] dark:text-[var(--rm-warning)] flex items-center gap-1.5">
                            <i class="ph-bold ph-shield-warning text-sm"></i> Sección 4 — Escalamiento
                        </span>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- ¿Requiere valoración médica? --}}
                            <div class="space-y-2">
                                <label class="font-bold text-[var(--rm-text-primary)] block">
                                    ¿Requiere valoración médica? *
                                </label>
                                <div class="flex items-center gap-4">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="requiere_medico" :value="false" class="text-[var(--rm-warning)] focus:ring-[var(--rm-warning)]">
                                        <span class="font-medium">No</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="requiere_medico" :value="true" class="text-[var(--rm-warning)] focus:ring-[var(--rm-warning)]">
                                        <span class="font-bold text-[var(--rm-danger)]">Sí</span>
                                    </label>
                                </div>
                            </div>

                            {{-- ¿Requiere derivación? --}}
                            <div class="space-y-2">
                                <label class="font-bold text-[var(--rm-text-primary)] block">
                                    ¿Requiere derivación a otra área/especialidad? *
                                </label>
                                <div class="flex items-center gap-4">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="requiere_derivacion" :value="false" class="text-[var(--rm-warning)] focus:ring-[var(--rm-warning)]">
                                        <span class="font-medium">No</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="radio" wire:model.live="requiere_derivacion" :value="true" class="text-[var(--rm-warning)] focus:ring-[var(--rm-warning)]">
                                        <span class="font-bold text-[var(--rm-warning)]">Sí</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Banner condicional suave si requiere seguimiento --}}
                        @if(in_array($gravedad, ['ALTA', 'CRITICA']) || $requiere_medico || $requiere_derivacion)
                            <div class="rounded-xl bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger)] border border-[var(--rm-danger)]/40 p-3 flex items-start gap-2.5 text-[var(--rm-danger)] dark:text-[var(--rm-danger)]">
                                <i class="ph-bold ph-warning-circle text-lg shrink-0 mt-0.5"></i>
                                <div>
                                    <div class="font-bold text-xs">Requiere seguimiento adicional</div>
                                    <div class="text-[11px] font-medium opacity-90">
                                        Después de registrar se habilitarán las acciones correspondientes para emitir la alerta médica o derivación formal.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- SECCIÓN 5: OBSERVACIONES --}}
                    <div class="space-y-3">
                        <span class="font-bold text-xs uppercase tracking-wider text-[var(--rm-warning)] dark:text-[var(--rm-warning)] flex items-center gap-1.5">
                            <i class="ph-bold ph-info text-sm"></i> Sección 5 — Observaciones
                        </span>

                        <div class="space-y-1">
                            <label class="font-semibold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                                Observación complementaria (opcional)
                            </label>
                            <textarea wire:model="observacion"
                                rows="2"
                                placeholder="Notas adicionales, antecedentes inmediatos relevantes..."
                                class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] p-3 text-xs text-[var(--rm-text-primary)] focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)] focus:outline-none"></textarea>
                        </div>
                        <p class="text-[10px] text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] italic">
                            * El estado inicial del incidente se registrará como ABIERTO para seguimiento asistencial.
                        </p>
                    </div>

                    @error('error_general')
                        <div class="rounded-xl bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)] p-3 text-xs font-bold text-[var(--rm-danger)]">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Footer del Modal --}}
                <div class="flex items-center justify-end gap-3 border-t border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] px-5 py-4">
                    <button type="button" wire:click="cerrarModalRegistro"
                        class="rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-border)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] px-4 py-2.5 text-xs font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] dark:hover:text-[var(--rm-surface)] transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="registrarIncidente" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-xl bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition active:scale-[0.98] disabled:opacity-50">
                        <span wire:loading.remove wire:target="registrarIncidente">Registrar incidencia</span>
                        <span wire:loading wire:target="registrarIncidente">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 2: VER INCIDENTE (LECTURA COMPLETA + CONTACTOS)    --}}
    {{-- ======================================================== --}}
    @if($modalVer && $incidenteDetalle)
        @include('livewire.cuidados.partials.incidente-detalle')
    @endif
    {{-- ======================================================== --}}
    {{-- MODAL 3: ACTUALIZAR ESTADO (CAMBIO TRAZABLE)             --}}
    {{-- ======================================================== --}}
    @if($modalEstado)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] shadow-2xl overflow-hidden"
                @click.outside="$wire.cerrarModalEstado()">

                <div class="flex items-center justify-between border-b border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] px-5 py-4">
                    <h3 class="text-sm font-black text-[var(--rm-text-primary)]">
                        Actualizar estado del incidente
                    </h3>
                    <button wire:click="cerrarModalEstado" class="text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <div class="space-y-1">
                        <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Nuevo estado *</label>
                        <select wire:model="nuevo_estado"
                            class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs font-bold text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)]">
                            <option value="ABIERTO">ABIERTO</option>
                            <option value="EN_SEGUIMIENTO">EN SEGUIMIENTO</option>
                            <option value="CERRADO">CERRADO (Resuelto)</option>
                            <option value="ANULADO">ANULADO (Error / Corrección)</option>
                        </select>
                        @error('nuevo_estado')
                            <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                            Justificación clínica del cambio / Nota de evolución *
                        </label>
                        <textarea wire:model="nota_estado"
                            rows="3"
                            placeholder="Detalle los motivos clínicos o resolución del incidente..."
                            class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] p-3 text-xs text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)]"></textarea>
                        @error('nota_estado')
                            <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] px-5 py-3">
                    <button wire:click="cerrarModalEstado"
                        class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-4 py-2 text-xs font-bold text-[var(--rm-text-secondary)]">
                        Cancelar
                    </button>
                    <button wire:click="actualizarEstado"
                        class="rounded-xl bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] px-4 py-2 text-xs font-bold text-white transition">
                        Guardar cambio
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 4: CREAR DERIVACIÓN CLÍNICA                        --}}
    {{-- ======================================================== --}}
    @if($modalDerivacion)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] shadow-2xl overflow-hidden"
                @click.outside="$wire.cerrarModalDerivacion()">

                <div class="flex items-center justify-between border-b border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] px-5 py-4">
                    <h3 class="text-sm font-black text-[var(--rm-text-primary)] flex items-center gap-2">
                        <i class="ph-bold ph-arrow-square-out text-base text-[var(--rm-warning)]"></i> Crear derivación formal
                    </h3>
                    <button wire:click="cerrarModalDerivacion" class="text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <div class="space-y-1">
                        <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Área receptora *</label>
                        <select wire:model="derivacion_area_receptora"
                            class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs font-semibold text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)]">
                            <option value="">Seleccione el área...</option>
                            @foreach($areasDisponibles as $ar)
                                <option value="{{ $ar->cod_area }}">{{ $ar->nombre }}</option>
                            @endforeach
                        </select>
                        @error('derivacion_area_receptora')
                            <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Prioridad *</label>
                        <select wire:model="derivacion_prioridad"
                            class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] py-2 px-3 text-xs font-semibold text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)]">
                            <option value="BAJA">Baja</option>
                            <option value="MEDIA">Media</option>
                            <option value="ALTA">Alta</option>
                            <option value="URGENTE">Urgente</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">Motivo clínico *</label>
                        <textarea wire:model="derivacion_motivo"
                            rows="3"
                            placeholder="Motivo detallado de la derivación..."
                            class="w-full rounded-xl border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface)] dark:bg-[var(--rm-text-primary)] p-3 text-xs text-[var(--rm-text-primary)] focus:outline-none focus:border-[var(--rm-action-primary)] focus:ring-1 focus:ring-[var(--rm-action-primary)]"></textarea>
                        @error('derivacion_motivo')
                            <span class="text-[11px] font-bold text-[var(--rm-danger)]">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-[var(--rm-border)] dark:border-[var(--rm-text-body)] bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] px-5 py-3">
                    <button wire:click="cerrarModalDerivacion"
                        class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-4 py-2 text-xs font-bold text-[var(--rm-text-secondary)]">
                        Cancelar
                    </button>
                    <button wire:click="crearDerivacion"
                        class="rounded-xl bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] px-4 py-2 text-xs font-bold text-white transition">
                        Emitir derivación
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ======================================================== --}}
    {{-- MODAL 5: CONFIRMACIÓN POST-REGISTRO Y ACCIONES           --}}
    {{-- ======================================================== --}}
    @if($modalExito)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm">
            <div class="relative w-full max-w-md rounded-2xl bg-[var(--rm-surface)] dark:bg-[var(--rm-surface)] border border-[var(--rm-border)] dark:border-[var(--rm-text-body)] shadow-2xl p-6 text-center space-y-4">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)]">
                    <i class="ph-bold ph-check-circle text-3xl"></i>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-black text-[var(--rm-text-primary)]">
                        ✓ Incidencia registrada
                    </h3>
                    <p class="text-xs text-[var(--rm-text-secondary)] dark:text-[var(--rm-text-muted)]">
                        Disponible en el historial del residente.
                    </p>
                </div>

                @if($ultimoRequiereMedico || $ultimoRequiereDerivacion)
                    <div class="p-3 bg-[var(--rm-surface-soft)]/50 dark:bg-[var(--rm-surface)] rounded-xl space-y-2 text-left">
                        <span class="text-[11px] font-bold text-[var(--rm-warning)] dark:text-[var(--rm-warning)] block">
                            Acciones de seguimiento recomendadas:
                        </span>

                        @if($ultimoRequiereMedico)
                            <button wire:click="solicitarValoracionMedica('{{ $ultimoCodIncidente }}')"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[var(--rm-danger)] hover:bg-[var(--rm-danger)] text-white py-2 px-3 text-xs font-bold transition">
                                <i class="ph-bold ph-first-aid text-sm"></i> Solicitar valoración médica
                            </button>
                        @endif

                        @if($ultimoRequiereDerivacion)
                            <button wire:click="abrirModalDerivacion('{{ $ultimoCodIncidente }}')"
                                class="w-full flex items-center justify-center gap-2 rounded-xl bg-[var(--rm-warning)] hover:bg-[var(--rm-warning)] text-white py-2 px-3 text-xs font-bold transition">
                                <i class="ph-bold ph-arrow-square-out text-sm"></i> Crear derivación formal
                            </button>
                        @endif
                    </div>
                @endif

                <div class="pt-2">
                    <button wire:click="cerrarModalExito"
                        class="w-full rounded-xl bg-[var(--rm-surface-soft)] dark:bg-[var(--rm-surface)] hover:bg-[var(--rm-border)] dark:hover:bg-[var(--rm-border)] py-2.5 text-xs font-bold text-[var(--rm-text-primary)] transition">
                        Finalizar y volver al listado
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
