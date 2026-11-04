{{-- Formulario de tarea; estructura compartida y estado del componente padre. --}}
@if($modalTarea)
<x-ui.modal-livewire id="ficha-tarea" wire:model="modalTarea" closeMethod="cerrarModalTarea" maxWidth="md" :showValidation="false" class="rm-clinical-form-modal">
    <x-slot name="title">Registrar Actividad de Cuidado</x-slot>
<p class="text-xs text-[var(--rm-text-body)] font-bold mt-1.5">{{ $tareaTitulo }}</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Estado de Ejecución *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                wire:click="$set('tareaEstadoAccion', 'REALIZADA')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $tareaEstadoAccion === 'REALIZADA' ? 'bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] border-[var(--rm-success-strong)]' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}">
                            <i class="ph-bold ph-check-circle mr-1"></i> Realizada
                        </button>
                        <button type="button"
                                wire:click="$set('tareaEstadoAccion', 'OMITIDA')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $tareaEstadoAccion === 'OMITIDA' ? 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)] border-[var(--rm-warning-strong)]' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}">
                            <i class="ph-bold ph-prohibit mr-1"></i> Omitida
                        </button>
                    </div>
                </div>

                @if($tareaEstadoAccion === 'REALIZADA')
                    <div>
                        <label class="font-bold text-[var(--rm-text-body)] block mb-1">Resultado / Observación de la Tarea</label>
                        <textarea wire:model="tareaResultado" rows="2" placeholder="Ej: Realizado sin inconvenientes, residente colaborador..." class="rm-input w-full text-xs"></textarea>
                    </div>
                @else
                    <div>
                        <label class="font-bold text-[var(--rm-danger-strong)] block mb-1">Motivo de Omisión *</label>
                        <textarea wire:model="tareaMotivoOmision" rows="2" placeholder="Motivo de no realización..." class="rm-input w-full text-xs"></textarea>
                        @error('tareaMotivoOmision') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
    <x-slot name="footer">
<div class="flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalTarea" class="rounded-xl bg-[var(--rm-surface-soft)] px-4 py-2 text-xs font-bold text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarTarea" wire:loading.attr="disabled" class="rounded-xl bg-[var(--rm-info-action)] px-4 py-2 text-xs font-bold text-[var(--rm-text-inverse)] hover:bg-[var(--rm-action-primary)] transition">
                    <span wire:loading.remove wire:target="guardarTarea">Guardar registro</span>
                    <span wire:loading wire:target="guardarTarea">Guardando...</span>
                </button>
            </div>
    </x-slot>
</x-ui.modal-livewire>
@endif
