{{-- MODAL CANÓNICO LIVEWIRE: REGISTRAR OMISIÓN DE MEDICACIÓN --}}
@if($modalOmisionAbierto)
<x-ui.modal-livewire id="modal-omision-medicacion" wire:model="modalOmisionAbierto" maxWidth="lg" closeMethod="cerrarModalOmision">
    <x-slot:icon>
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-white shadow-2xs">
            <i class="ph-bold ph-warning-circle text-xl"></i>
        </span>
    </x-slot:icon>

    <x-slot:title>
        <div>
            <span class="block text-base font-extrabold text-[var(--rm-text-primary)]">Registrar omisión</span>
            <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-muted)]">Justificación clínica y trazabilidad de dosis no suministrada</span>
        </div>
    </x-slot:title>

    <form wire:submit.prevent="guardarOmision" id="form-omision-medicacion">
        <div class="space-y-4 text-xs">

            {{-- Resumen Solo Lectura de la Dosis a Omitir --}}
            <div class="rounded-xl bg-[var(--rm-surface-raised)] border border-[var(--rm-border-soft)] p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-[800] text-[var(--rm-action-primary)] uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-shield-warning"></i>
                        Dosis Objeto de Omisión
                    </span>
                    <span class="text-[10.5px] font-bold text-[var(--rm-action-primary)] font-mono">
                        {{ $selectedHora ?? '08:00' }} hrs
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Residente:</span>
                        <strong class="text-[var(--rm-text-primary)]">
                            {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Residente' }}
                        </strong>
                    </div>

                    <div>
                        <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Medicamento:</span>
                        <strong class="text-[var(--rm-text-primary)]">
                            {{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento' }}
                            ({{ $dosisDetalle['prescripcion']['dosis'] ?? '' }})
                        </strong>
                    </div>
                </div>

                <div class="pt-1.5 border-t border-[var(--rm-border-soft)]/60 text-[10.5px] text-[var(--rm-text-muted)] flex items-center justify-between">
                    <span>Profesional que omite: <strong class="text-[var(--rm-text-primary)]">{{ auth()->user()?->name ?? 'Enfermería en turno' }}</strong></span>
                    <span class="text-[var(--rm-action-primary)] font-semibold">Trazabilidad clínica</span>
                </div>
            </div>

            {{-- Campos Editables de Omisión --}}
            <div class="space-y-3.5">

                {{-- 1. Motivo de Omisión Justificada --}}
                <div>
                    <label for="formMotivoOmision" class="text-xs font-[700] text-[var(--rm-text-primary)] block mb-1">
                        Motivo de Omisión Justificada <span class="text-[var(--rm-danger)]">*</span>
                    </label>

                    <select
                        id="formMotivoOmision"
                        wire:model.live="formMotivoOmision"
                        required
                        class="w-full h-10 px-3 text-xs rounded-xl border {{ $errors->has('formMotivoOmision') ? 'border-[var(--rm-danger)] focus:ring-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)] focus:ring-[var(--rm-focus)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] focus:outline-none focus:ring-1 shadow-2xs cursor-pointer">
                        <option value="">Seleccione el motivo clínico o asistencial...</option>
                        <option value="Rechazo voluntario del residente">Rechazo voluntario del residente</option>
                        <option value="Ayuno médico programado (analítica / procedimiento)">Ayuno médico programado (analítica / procedimiento)</option>
                        <option value="Residente ausente temporalmente / en traslado">Residente ausente temporalmente / en traslado</option>
                        <option value="Suspensión o modificación médica verbal">Suspensión o modificación médica verbal</option>
                        <option value="Fármaco no disponible en farmacia">Fármaco no disponible en farmacia</option>
                        <option value="Intolerancia gástrica o náuseas previas">Intolerancia gástrica o náuseas previas</option>
                        <option value="Parámetro clínico contraindicado (PA/FC/Glucemia)">Parámetro clínico contraindicado (PA/FC/Glucemia)</option>
                        <option value="OTRO">Otro motivo específico (requiere justificación detallada)</option>
                    </select>

                    @error('formMotivoOmision')
                        <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- 2. Observación / Justificación Detallada --}}
                @php
                    $motivoVal = (string)($formMotivoOmision ?? $this->formMotivoOmision ?? '');
                    $motivoRequiereDetalle = ($motivoVal === 'OTRO' || str_contains($motivoVal, 'verbal') || str_contains($motivoVal, 'contraindicado'));
                @endphp

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="formObservacionOmision" class="text-xs font-[700] text-[var(--rm-text-primary)]">
                            Justificación Asistencial Detallada
                            @if($motivoRequiereDetalle)
                                <span class="text-[var(--rm-danger)] font-bold">* (Obligatoria para este motivo)</span>
                            @else
                                <span class="text-[11px] font-normal text-[var(--rm-text-muted)]">(Recomendada)</span>
                            @endif
                        </label>
                        <span class="text-[10.5px] text-[var(--rm-text-muted)] font-mono">
                            {{ strlen((string)($formObservacionOmision ?? $this->formObservacionOmision ?? '')) }}/1000
                        </span>
                    </div>

                    <textarea
                        id="formObservacionOmision"
                        wire:model="formObservacionOmision"
                        maxlength="1000"
                        rows="3"
                        placeholder="{{ $motivoRequiereDetalle ? 'Detalle ampliamente la razón clínica, médico que indicó la suspensión o valores de signos vitales...' : 'Indique detalles complementarios sobre la omisión de la toma...' }}"
                        class="w-full text-xs rounded-xl p-3 border {{ $errors->has('formObservacionOmision') ? 'border-[var(--rm-danger)] focus:ring-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)] focus:ring-[var(--rm-focus)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] focus:outline-none focus:ring-1 resize-none shadow-2xs"></textarea>

                    @error('formObservacionOmision')
                        <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
                            <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

            </div>

        </div>
    </form>

    <x-slot:footer>
        <button
            type="button"
            wire:click="cerrarModalOmision"
            class="rm-btn rm-btn-secondary">
            Cancelar
        </button>

        <button
            type="submit"
            form="form-omision-medicacion"
            wire:loading.attr="disabled"
            class="rm-btn rm-btn-primary">
            <i class="ph-bold ph-warning text-sm" wire:loading.remove wire:target="guardarOmision"></i>
            <span wire:loading.remove wire:target="guardarOmision">Confirmar Omisión</span>
            <span wire:loading wire:target="guardarOmision">Registrando omisión...</span>
        </button>
    </x-slot:footer>
</x-ui.modal-livewire>
@endif
