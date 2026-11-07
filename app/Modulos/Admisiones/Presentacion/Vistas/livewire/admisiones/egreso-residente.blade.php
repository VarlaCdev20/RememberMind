<div>
    <button type="button" class="rm-btn rm-btn--secondary" wire:click="abrir" wire:loading.attr="disabled">Registrar egreso</button>
    @unless($modalAbierto)
        @error('egreso')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
    @endunless

    <x-ui.modal-livewire id="egreso-residente" wire:model="modalAbierto" title="Confirmar egreso definitivo" max-width="lg" close-method="cerrar">
        <p class="rm-body-sm mb-4">Se cerrará la admisión de <strong>{{ $residente->nombres }} {{ $residente->apellido_paterno }} {{ $residente->apellido_materno }}</strong>. Si tiene una cama asignada, se liberará su ocupación. El historial del residente se conserva.</p>
        <label class="rm-label" for="motivo-egreso">Motivo del egreso</label>
        <textarea class="rm-input w-full" id="motivo-egreso" wire:model="motivo" required maxlength="1000" rows="3"></textarea>
        @error('motivo')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
        @error('egreso')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
        <x-slot:footer>
            <button type="button" class="rm-btn rm-btn--secondary" wire:click="cerrar" wire:loading.attr="disabled" wire:target="confirmar">Cancelar</button>
            <button type="button" class="rm-btn rm-btn-danger" wire:click="confirmar" wire:loading.attr="disabled" wire:target="confirmar">Confirmar egreso</button>
        </x-slot:footer>
    </x-ui.modal-livewire>
</div>
