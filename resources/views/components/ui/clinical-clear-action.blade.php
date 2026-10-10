<div x-data="{ confirmClear: false }" class="rm-clinical-clear">
    <button x-show="!confirmClear" type="button" class="rm-btn-secondary" @click="confirmClear = true" wire:loading.attr="disabled"><i class="ph-bold ph-eraser" aria-hidden="true"></i> Limpiar campos</button>
    <div x-show="confirmClear" x-cloak role="group" aria-label="Confirmar limpieza de campos">
        <p class="rm-clinical-form__note" role="status">¿Restablecer los campos sin guardar? El residente y el historial permanecerán intactos.</p>
        <button type="button" class="rm-btn-secondary" @click="confirmClear = false" wire:loading.attr="disabled">Seguir editando</button>
        <button type="button" class="rm-btn-secondary" @click="restoreCapture(); confirmClear = false; $wire.limpiarErroresCaptura().then(() => $nextTick(() => { initialCapture = snapshot(); notifyDirty(); }))" wire:loading.attr="disabled">Confirmar limpieza</button>
    </div>
</div>
