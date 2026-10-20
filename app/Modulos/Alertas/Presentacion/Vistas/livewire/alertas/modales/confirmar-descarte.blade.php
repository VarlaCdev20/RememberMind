<p x-show="guardados > 0" x-cloak role="status" class="rm-alert-request-status"><i class="ph-bold ph-hourglass-medium" aria-hidden="true"></i>Procesando el registro. Espera la confirmación antes de cerrar.</p>
<div x-show="errorSolicitud" x-cloak role="alert" class="rm-callout rm-alert-request-error"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i><p x-text="errorSolicitud"></p></div>
@if($confirmarDescarte)
    <div role="alert" class="w-full">
        <x-ui.callout variant="warning" title="Hay una captura sin guardar">
            <p>Puedes continuar editando o descartar esta captura antes de cerrar.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" class="rm-btn rm-btn-primary" wire:click="continuarCaptura">Continuar editando</button>
                <button type="button" class="rm-btn rm-btn-secondary" wire:click="descartarCaptura">Descartar captura y cerrar</button>
            </div>
        </x-ui.callout>
    </div>
@endif
