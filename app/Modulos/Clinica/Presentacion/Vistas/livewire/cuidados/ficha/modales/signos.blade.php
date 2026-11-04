{{-- Formulario de signos; estructura compartida y estado del componente padre. --}}
@if($modalSignos)
<x-ui.modal-livewire id="ficha-signos" wire:model="modalSignos" closeMethod="cerrarModalSignos" maxWidth="md" :showValidation="false" class="rm-clinical-form-modal" subtitle="Parámetros fisiológicos y hemodinámicos del residente.">
    <x-slot name="title">Registrar Control de Signos Vitales</x-slot>
<div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Presión Arterial (PA) *</label>
                    <input type="text" wire:model="signoPA" placeholder="120/80" class="rm-input w-full text-xs" />
                    @error('signoPA') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Frecuencia Cardaca (FC)</label>
                    <input type="number" wire:model="signoFC" placeholder="75" class="rm-input w-full text-xs" />
                    @error('signoFC') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Frecuencia Resp. (FR)</label>
                    <input type="number" wire:model="signoFR" placeholder="18" class="rm-input w-full text-xs" />
                    @error('signoFR') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Temperatura (°C)</label>
                    <input type="number" step="0.1" wire:model="signoTemp" placeholder="36.5" class="rm-input w-full text-xs" />
                    @error('signoTemp') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Saturación SpO2 (%)</label>
                    <input type="number" wire:model="signoSat" placeholder="98" class="rm-input w-full text-xs" />
                    @error('signoSat') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Glucosa (mg/dL)</label>
                    <input type="number" wire:model="signoGlucosa" placeholder="105" class="rm-input w-full text-xs" />
                    @error('signoGlucosa') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Nivel de Dolor (Escala EVA 0 a 10)</label>
                    <div class="flex items-center gap-3">
                        <input type="range" min="0" max="10" wire:model.live="signoDolor" class="w-full accent-blue-600" />
                        <span class="font-bold text-sm px-2.5 py-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] min-w-[3rem] text-center">
                            {{ $signoDolor ?? 0 }}/10
                        </span>
                    </div>
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Posición durante el control</label>
                    <select wire:model="signoPosicion" class="rm-input w-full text-xs"><option value="">No registrada</option><option>SENTADO</option><option>ACOSTADO</option><option>DE_PIE</option></select>
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <input id="signo-oxigeno" type="checkbox" wire:model="signoUsaOxigeno" class="rounded border-[var(--rm-border)] text-boton-principal">
                    <label for="signo-oxigeno" class="font-bold text-[var(--rm-text-body)]">Usa oxígeno</label>
                </div>
                @error('signoPA')
                    <label class="col-span-2 flex items-start gap-2 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 text-xs font-bold text-[var(--rm-text-title)]">
                        <input type="checkbox" wire:model="signoConfirmarAtipico" class="mt-0.5 rounded border-[var(--rm-border)] text-boton-principal">
                        Confirmo que repetí la medición y deseo conservar este valor atípico.
                    </label>
                @enderror
                <div class="col-span-2">
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Observaciones</label>
                    <textarea wire:model="signoObs" rows="2" placeholder="Notas clínicas adicionales..." class="rm-input w-full text-xs"></textarea>
                </div>
            </div>
    <x-slot name="footer">
<div class="flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalSignos" class="rounded-xl bg-[var(--rm-surface-soft)] px-4 py-2 text-xs font-bold text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarSignos" wire:loading.attr="disabled" class="rounded-xl bg-[var(--rm-info-action)] px-4 py-2 text-xs font-bold text-[var(--rm-text-inverse)] hover:bg-[var(--rm-action-primary)] transition">
                    <span wire:loading.remove wire:target="guardarSignos">Guardar signos</span>
                    <span wire:loading wire:target="guardarSignos">Guardando...</span>
                </button>
            </div>
    </x-slot>
</x-ui.modal-livewire>
@endif
