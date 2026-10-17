{{-- Formulario de medicacion; estructura compartida y estado del componente padre. --}}
@if($modalMed)
<x-ui.modal-livewire id="ficha-medicacion" wire:model="modalMed" closeMethod="cerrarModalMedicacion" maxWidth="md" :showValidation="false" class="rm-clinical-form-modal">
    <x-slot name="title">Administración de Medicación</x-slot>
<div class="mt-2 rounded-xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-xs space-y-1">
                <p class="font-bold text-[var(--rm-text-title)] text-sm">{{ $medNombre }}</p>
                <p class="text-[var(--rm-text-body)]">Dosis: <strong>{{ $medDosis }}</strong> · Vía: <strong>{{ $medVia }}</strong></p>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Acción Asistencial *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                wire:click="$set('medAccion', 'ADMINISTRAR')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'ADMINISTRAR' ? 'bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] border-[var(--rm-success-strong)]' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}">
                            <i class="ph-bold ph-check mr-1"></i> Administrada
                        </button>
                        <button type="button"
                                wire:click="$set('medAccion', 'OMITIR')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'OMITIR' ? 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)] border-[var(--rm-warning-strong)]' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}">
                            <i class="ph-bold ph-warning mr-1"></i> Omitida
                        </button>
                        <button type="button" wire:click="$set('medAccion', 'RECHAZAR')" class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'RECHAZAR' ? 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}"><i class="ph-bold ph-hand mr-1"></i> Rechazada</button>
                        <button type="button" wire:click="$set('medAccion', 'NO_DISPONIBLE')" class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'NO_DISPONIBLE' ? 'bg-estado-peligroBg text-estado-peligro border-estado-peligroBorde' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}"><i class="ph-bold ph-package mr-1"></i> No disponible</button>
                    </div>
                </div>

                @if($medAccion !== 'ADMINISTRAR')
                    <div>
                        <label class="font-bold text-[var(--rm-danger-strong)] block mb-1">Motivo de Omisión *</label>
                        <textarea wire:model="medMotivoOmision" rows="2" placeholder="Rechazo del paciente, náuseas, etc." class="rm-input w-full text-xs"></textarea>
                        @error('medMotivoOmision') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if($medEsPrn && $medAccion === 'ADMINISTRAR')
                    <div class="rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 space-y-3">
                        <p class="text-xs font-bold text-[var(--rm-text-title)]">PRN · {{ $medCondicionPrn }}</p>
                        <label class="block font-bold text-[var(--rm-text-body)]">Síntoma o motivo actual *<textarea wire:model="medMotivoPrn" rows="2" class="rm-input mt-1 w-full text-xs"></textarea>@error('medMotivoPrn')<span class="text-estado-peligro text-[10px] font-bold">{{ $message }}</span>@enderror</label>
                        <label class="block font-bold text-[var(--rm-text-body)]">Valoración previa *<textarea wire:model="medValoracionPrevia" rows="2" class="rm-input mt-1 w-full text-xs"></textarea>@error('medValoracionPrevia')<span class="text-estado-peligro text-[10px] font-bold">{{ $message }}</span>@enderror</label>
                        <label class="block font-bold text-[var(--rm-text-body)]">Intensidad 0–10<input wire:model="medIntensidadPrevia" type="number" min="0" max="10" class="rm-input mt-1 w-full text-xs"></label>
                        <p class="text-[10px] font-bold text-[var(--rm-text-muted)]">Se programará una reevaluación una hora después.</p>
                    </div>
                @endif

                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Observaciones / Efectos Observados</label>
                    <textarea wire:model="medEfectoObs" rows="2" placeholder="Tolerancia adecuada..." class="rm-input w-full text-xs"></textarea>
                </div>
            </div>
    <x-slot name="footer">
<div class="flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalMedicacion" class="rounded-xl bg-[var(--rm-surface-soft)] px-4 py-2 text-xs font-bold text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarMedicacion" wire:loading.attr="disabled" class="rounded-xl bg-[var(--rm-success-action)] px-4 py-2 text-xs font-bold text-[var(--rm-text-inverse)] hover:bg-[var(--rm-action-primary)] transition">
                    <span wire:loading.remove wire:target="guardarMedicacion">Confirmar registro</span>
                    <span wire:loading wire:target="guardarMedicacion">Guardando...</span>
                </button>
            </div>
    </x-slot>
</x-ui.modal-livewire>
@endif
