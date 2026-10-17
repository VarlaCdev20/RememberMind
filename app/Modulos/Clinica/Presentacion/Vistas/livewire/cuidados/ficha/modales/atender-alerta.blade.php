{{-- Formulario de atender-alerta; estructura compartida y estado del componente padre. --}}
@if($modalAtenderAlerta)
<x-ui.modal-livewire id="ficha-atender-alerta" wire:model="modalAtenderAlerta" closeMethod="cerrarModalAtenderAlerta" maxWidth="md" :showValidation="false" class="rm-clinical-form-modal" subtitle="Registrar primera intervención y pasar alerta a estado EN ATENCIÓN.">
    <x-slot name="title">Atender Alerta Clínica</x-slot>
<div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Acción Inmediata Realizada *</label>
                    <textarea wire:model="accionTomadaAlerta" rows="3" placeholder="Ej: Se acomodó en cama, se verificó vía aérea, se administró medicación indicada..." class="rm-input w-full text-xs"></textarea>
                    @error('accionTomadaAlerta') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>
    <x-slot name="footer">
<div class="flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalAtenderAlerta" class="rounded-xl bg-[var(--rm-surface-soft)] px-4 py-2 text-xs font-bold text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarAtenderAlerta" wire:loading.attr="disabled" class="rounded-xl bg-[var(--rm-info-action)] px-4 py-2 text-xs font-bold text-[var(--rm-text-inverse)] hover:bg-[var(--rm-action-primary)] transition">
                    <span wire:loading.remove wire:target="guardarAtenderAlerta">Registrar atención</span>
                    <span wire:loading wire:target="guardarAtenderAlerta">Guardando...</span>
                </button>
            </div>
    </x-slot>
</x-ui.modal-livewire>
@endif
