{{-- MODAL CANÓNICO LIVEWIRE: ADMINISTRAR MEDICACIÓN CON SEGURIDAD CLÍNICA --}}
@if($modalAdministrarAbierto)
<x-ui.modal-livewire id="modal-administrar-medicacion" wire:model="modalAdministrarAbierto" maxWidth="lg" closeMethod="cerrarModalAdministrar">
    <x-slot:icon>
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-white shadow-2xs">
            <i class="ph-bold ph-pill text-xl"></i>
        </span>
    </x-slot:icon>

    <x-slot:title>
        <div>
            <span class="block text-base font-extrabold text-[var(--rm-text-primary)]">Administrar medicación</span>
            <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-muted)]">Confirmación asistencial de la dosis y registro de la toma</span>
        </div>
    </x-slot:title>

    <form wire:submit.prevent="guardarAdministracion" id="form-administrar-medicacion">
        <div class="space-y-4 text-xs">

            {{-- 1. SECCIÓN: DATOS CLÍNICOS SOLO LECTURA (NO EDITABLES / INMUTABLES) --}}
            <div class="rounded-xl bg-[var(--rm-surface-raised)] border border-[var(--rm-border-soft)] p-3.5 space-y-3">
                <div class="flex items-center justify-between border-b border-[var(--rm-border-soft)]/70 pb-2">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">Residente Asignado</span>
                        <h4 class="text-sm font-bold text-[var(--rm-text-primary)] mt-0.5">
                            {{ $residenteModal->nombre_completo ?? 'Residente no identificado' }}
                        </h4>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">Ubicación</span>
                        <span class="text-xs font-semibold text-[var(--rm-text-primary)] block mt-0.5">
                            {{ $residenteModal->ubicacion_cama_habitacion ?? 'Sin ubicación asignada' }}
                        </span>
                    </div>
                </div>

                {{-- Fármaco y Detalles de Prescripción --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">Medicamento</span>
                        <span class="text-xs font-bold text-[var(--rm-text-primary)] block mt-0.5 truncate" title="{{ $medicamentoModal->nombre_comercial ?? '' }}">
                            {{ $medicamentoModal->nombre_comercial ?? 'Fármaco' }}
                        </span>
                        @if(!empty($medicamentoModal->principio_activo))
                            <span class="text-[10px] text-[var(--rm-text-muted)] truncate block">({{ $medicamentoModal->principio_activo }})</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">Dosis Prescrita</span>
                        <span class="text-xs font-bold text-[var(--rm-action-primary)] block mt-0.5">
                            {{ ($formDosisPrescritaValor ?? $this->formDosisPrescritaValor ?? '1') }} {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'mg') }}
                        </span>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">Vía y Horario</span>
                        <span class="text-xs font-semibold text-[var(--rm-text-primary)] block mt-0.5">
                            {{ $prescripcionModal->via_administracion ?? 'Oral' }} · <strong class="font-mono text-[var(--rm-action-primary)]">{{ substr((string)($programacionModal->hora_programada ?? '08:00'), 0, 5) }}</strong>
                        </span>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">Médico Prescriptor</span>
                        <span class="text-xs font-semibold text-[var(--rm-text-primary)] block mt-0.5 truncate" title="{{ $prescripcionModal->medico->nombre_completo ?? '' }}">
                            {{ $prescripcionModal->medico->nombre_completo ?? 'Médico Tratante' }}
                        </span>
                    </div>
                </div>

                @if(!empty($prescripcionModal->indicaciones_extra))
                    <div class="mt-2 pt-2 border-t border-[var(--rm-border-soft)]/50 text-[11px] text-[var(--rm-text-muted)]">
                        <strong>Indicaciones especiales:</strong> {{ $prescripcionModal->indicaciones_extra }}
                    </div>
                @endif
            </div>

            {{-- 2. SECCIÓN: FECHA Y HORA OFICIAL DEL SERVIDOR --}}
            <div class="rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] p-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-shield-check text-base text-[var(--rm-action-primary)]"></i>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">Trazabilidad Asistencial</span>
                        <span class="text-xs font-semibold text-[var(--rm-text-primary)]">
                            Registrando como: <strong class="text-[var(--rm-action-primary)]">{{ auth()->user()->nombre_completo ?? auth()->user()->name }}</strong>
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block">Hora Oficial</span>
                    <span class="text-xs font-mono font-bold text-[var(--rm-text-primary)]">
                        {{ now()->format('d/m/Y H:i') }}
                    </span>
                </div>
            </div>

            {{-- 3. SECCIÓN: CAMPOS EDITABLES DEL ENFERMERO CON VALIDACIÓN --}}
            <div class="space-y-3.5 pt-1">
                <h4 class="text-[11px] font-[800] text-[var(--rm-action-primary)] uppercase tracking-wider flex items-center gap-1.5">
                    <i class="ph-bold ph-pencil-simple text-[var(--rm-action-primary)]"></i>
                    Registro Asistencial del Enfermero
                </h4>

                {{-- 1. Dosis Administrada --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="formDosisAdministrada" class="text-xs font-[700] text-[var(--rm-text-primary)]">
                            Dosis Administrada <span class="text-[var(--rm-danger)]">*</span>
                        </label>
                        <span class="text-[11px] text-[var(--rm-text-muted)]">
                            Prescrita: <strong class="text-[var(--rm-text-primary)]">{{ ($formDosisPrescritaValor ?? $this->formDosisPrescritaValor ?? '1') }} {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'mg') }}</strong>
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input
                                type="number"
                                step="any"
                                min="0.001"
                                id="formDosisAdministrada"
                                wire:model.live.debounce.300ms="formDosisAdministrada"
                                required
                                placeholder="Ej. 50"
                                class="w-full h-10 px-3 text-xs font-bold rounded-xl border {{ $errors->has('formDosisAdministrada') ? 'border-[var(--rm-danger)] focus:ring-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)] focus:ring-[var(--rm-focus)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] focus:outline-none focus:ring-1 shadow-2xs font-mono" />
                        </div>
                        <span class="h-10 px-3 inline-flex items-center text-xs font-bold bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] rounded-xl text-[var(--rm-text-primary)]">
                            {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'unidad') }}
                        </span>
                    </div>

                    {{-- Alerta si la dosis difiere de la prescrita --}}
                    @if($dosisDifiere)
                        <div class="mt-1.5 p-2.5 rounded-lg bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40 flex items-start gap-2 text-[11px] text-[var(--rm-danger)]">
                            <i class="ph-bold ph-warning text-sm shrink-0 mt-0.5"></i>
                            <span>
                                <strong>Dosis modificada asistencialmente:</strong> La dosis administrada ({{ $dAdmin }}) difiere de la dosis prescrita ({{ $dPresc }}). Se exige <strong>observación/justificación clínica obligatoria</strong> en el campo inferior.
                            </span>
                        </div>
                    @endif

                    @error('formDosisAdministrada')
                        <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- 2. Efecto Observado (Nullable) --}}
                <div>
                    <label for="formEfectoObservado" class="text-xs font-[700] text-[var(--rm-text-primary)] block mb-1">
                        Efecto Observado <span class="text-[11px] font-normal text-[var(--rm-text-muted)]">(Opcional)</span>
                    </label>
                    <input
                        type="text"
                        id="formEfectoObservado"
                        wire:model="formEfectoObservado"
                        maxlength="500"
                        placeholder="Ej. Buena tolerancia oral, sin disfagia inmediata, sedación leve esperada..."
                        class="w-full h-10 px-3 text-xs rounded-xl border {{ $errors->has('formEfectoObservado') ? 'border-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] focus:outline-none focus:ring-1 focus:ring-[var(--rm-focus)] shadow-2xs" />
                    @error('formEfectoObservado')
                        <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- 3. Reacción Adversa (Nullable) --}}
                <div>
                    <label for="formReaccionAdversa" class="text-xs font-[700] text-[var(--rm-text-primary)] block mb-1">
                        Reacción Adversa <span class="text-[11px] font-normal text-[var(--rm-text-muted)]">(Opcional)</span>
                    </label>
                    <input
                        type="text"
                        id="formReaccionAdversa"
                        wire:model="formReaccionAdversa"
                        maxlength="500"
                        placeholder="Ej. Ninguna observada, náusea leve transitoria, prurito cutáneo..."
                        class="w-full h-10 px-3 text-xs rounded-xl border {{ $errors->has('formReaccionAdversa') ? 'border-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] focus:outline-none focus:ring-1 focus:ring-[var(--rm-focus)] shadow-2xs" />
                    @error('formReaccionAdversa')
                        <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- 4. Observación / Justificación de Enfermería --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="formObservacionAdmin" class="text-xs font-[700] text-[var(--rm-text-primary)]">
                            Observación de Enfermería
                            @if($dosisDifiere)
                                <span class="text-[var(--rm-danger)] font-bold">* (Obligatoria por variación de dosis)</span>
                            @else
                                <span class="text-[11px] font-normal text-[var(--rm-text-muted)]">(Opcional)</span>
                            @endif
                        </label>
                        <span class="text-[10.5px] text-[var(--rm-text-muted)] font-mono">
                            {{ strlen((string)($formObservacionAdmin ?? $this->formObservacionAdmin ?? '')) }}/1000
                        </span>
                    </div>

                    <textarea
                        id="formObservacionAdmin"
                        wire:model="formObservacionAdmin"
                        maxlength="1000"
                        rows="3"
                        placeholder="{{ $dosisDifiere ? 'Registre la justificación clínica obligatoria por la que se modificó la dosis prescrita...' : 'Detalles asistenciales relevantes, hidratación suministrada, etc.' }}"
                        class="w-full text-xs rounded-xl p-3 border {{ $errors->has('formObservacionAdmin') ? 'border-[var(--rm-danger)] focus:ring-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)] focus:ring-[var(--rm-focus)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] focus:outline-none focus:ring-1 resize-none shadow-2xs"></textarea>

                    @error('formObservacionAdmin')
                        <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                @error('administracion_error')
                    <div class="p-2.5 rounded-lg bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40 text-xs text-[var(--rm-danger)] font-bold">
                        {{ $message }}
                    </div>
                @enderror
            </div>

        </div>
    </form>

    <x-slot:footer>
        <button
            type="button"
            wire:click="cerrarModalAdministrar"
            class="rm-btn rm-btn-secondary">
            Cancelar
        </button>

        <button
            type="submit"
            form="form-administrar-medicacion"
            wire:loading.attr="disabled"
            class="rm-btn rm-btn-primary">
            <i class="ph-bold ph-check text-sm" wire:loading.remove wire:target="guardarAdministracion"></i>
            <span wire:loading.remove wire:target="guardarAdministracion">Confirmar administración</span>
            <span wire:loading wire:target="guardarAdministracion">Registrando...</span>
        </button>
    </x-slot:footer>
</x-ui.modal-livewire>
@endif
