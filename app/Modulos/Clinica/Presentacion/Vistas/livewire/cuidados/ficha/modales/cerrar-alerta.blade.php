{{-- Formulario de cerrar-alerta; estructura compartida y estado del componente padre. --}}
@if($modalCerrarAlerta)
<x-ui.modal-livewire id="ficha-cerrar-alerta" wire:model="modalCerrarAlerta" closeMethod="cerrarModalCerrarAlerta" maxWidth="md" :showValidation="false" class="rm-clinical-form-modal" subtitle="Confirme la estabilización del residente y el cierre del evento.">
    <x-slot name="title">Cerrar Alerta Clínica</x-slot>
<div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Observación de Cierre / Resolución *</label>
                    <textarea wire:model="observacionCierreAlerta" rows="3" placeholder="Parámetros normalizados, residente estable..." class="rm-input w-full text-xs"></textarea>
                    @error('observacionCierreAlerta') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>
    <x-slot name="footer">
<div class="flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalCerrarAlerta" class="rounded-xl bg-[var(--rm-surface-soft)] px-4 py-2 text-xs font-bold text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarCerrarAlerta" wire:loading.attr="disabled" class="rounded-xl bg-[var(--rm-success-action)] px-4 py-2 text-xs font-bold text-[var(--rm-text-inverse)] hover:bg-[var(--rm-action-primary)] transition">
                    <span wire:loading.remove wire:target="guardarCerrarAlerta">Cerrar alerta</span>
                    <span wire:loading wire:target="guardarCerrarAlerta">Cerrando...</span>
                </button>
            </div>
    </x-slot>
</x-ui.modal-livewire>
@endif
