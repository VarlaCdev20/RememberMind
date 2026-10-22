@if($modalCrear)
    <x-ui.modal-livewire class="rm-alert-dialog" title="Registrar nueva alerta" subtitle="Describe la situación, su origen y la prioridad del aviso." id="modalCrearAlerta" wire:model="modalCrear" maxWidth="lg" badge="Alertas · nuevo aviso" closeMethod="solicitarCerrarModales" alpineBusy="guardados > 0">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]">
                <i class="ph-bold ph-bell-ringing text-lg" aria-hidden="true"></i>
            </span>
        </x-slot>


        @if($preview)<x-ui.callout variant="info" title="Formulario en solo lectura">Puedes consultar todos los campos del aviso. La selección y el guardado están deshabilitados en previsualización.</x-ui.callout>@endif
        <fieldset @disabled($preview) class="space-y-4">
            <x-ui.callout variant="info" title="Aviso manual">Registrar una alerta no acredita una medición, un diagnóstico ni una atención. Los registros de origen se documentan en su proceso autorizado.</x-ui.callout>
            <x-ui.form-section title="Selección del residente" step="1" icon="ph-user" :columns="1">
                <x-ui.field label="Buscar residente asignado" for="buscarResidenteAlerta" required error="codResidente" help="Selecciona una tarjeta de tu ámbito autorizado. La selección permanece al buscar otra página.">
                    <input type="search" id="buscarResidenteAlerta" class="rm-input" wire:model.live.debounce.300ms="buscarResidente" placeholder="Nombre, apellidos o código" aria-invalid="{{ $errors->has('codResidente') ? 'true' : 'false' }}" aria-describedby="buscarResidenteAlerta-help {{ $errors->has('codResidente') ? 'buscarResidenteAlerta-error' : '' }}">
                </x-ui.field>
                @if($residenteSeleccionado)<p class="rm-alert-trace__selection" role="status">Residente seleccionado: <strong>{{ $residenteSeleccionado->nombre_completo }}</strong> · {{ $residenteSeleccionado->ubicacion_formateada }}</p>@endif
                <fieldset class="rm-field"><legend class="rm-label rm-label-required">Residente asignado</legend><div class="rm-alert-choice-grid">
                    @forelse($adultos as $ad)
                        <x-ui.choice-card name="codResidente" :value="$ad->cod_residente" :label="$ad->nombre_completo" :description="$ad->ubicacion_formateada" icon="ph-user" wire:model.live="codResidente" />
                    @empty<p role="status">No hay residentes autorizados que coincidan con esta búsqueda.</p>@endforelse
                </div></fieldset>
                <nav class="rm-alert-choice-pager" aria-label="Páginas de residentes"><button type="button" class="rm-btn rm-btn-secondary" wire:click="paginaResidentes(-1)" @disabled($paginaResidentes <= 1)>Anterior</button><span>Página {{ $paginaResidentes }} · {{ $totalResidentes }} coincidencias</span><button type="button" class="rm-btn rm-btn-secondary" wire:click="paginaResidentes(1)" @disabled($paginaResidentes * 8 >= $totalResidentes)>Siguiente</button></nav>
            </x-ui.form-section>

            <x-ui.form-section title="Origen y prioridad" step="2" icon="ph-sliders-horizontal" :columns="2">
                <fieldset class="rm-field md:col-span-2"><legend class="rm-label rm-label-required">Origen del evento</legend><div class="rm-alert-choice-grid">
                    @foreach(['SIGNOS'=>['Signos vitales','ph-heartbeat'],'MEDICACION'=>['Medicación','ph-pill'],'INCIDENTE'=>['Incidente','ph-warning'],'MANUAL'=>['Aviso manual','ph-note-pencil'],'PLAN'=>['Plan de cuidados','ph-list-checks'],'SEGUIMIENTO'=>['Seguimiento','ph-path'],'SOLICITUD_MEDICA'=>['Solicitud médica','ph-stethoscope'],'FICHA'=>['Ficha del residente','ph-folder'],'VALORACION'=>['Valoración','ph-clipboard-text']] as $valor=>[$label,$icono])
                        <x-ui.choice-card name="origen" :value="$valor" :label="$label" :icon="$icono" wire:model.live="origen" />
                    @endforeach
                </div>@error('origen')<p class="rm-error" role="alert">{{ $message }}</p>@enderror</fieldset>

                <x-ui.field label="Tipo de alerta" for="inputTipoAlerta" required error="tipoAlerta">
                    <input id="inputTipoAlerta" aria-invalid="{{ $errors->has('tipoAlerta') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('tipoAlerta') ? 'inputTipoAlerta-error' : '' }}" type="text" wire:model="tipoAlerta" minlength="3" maxlength="60" placeholder="Ej.: Situación que requiere revisión" @class(['rm-input', 'rm-input-error' => $errors->has('tipoAlerta')])>
                </x-ui.field>

                <div class="md:col-span-2">
                    <fieldset class="rm-field"><legend class="rm-label rm-label-required">Prioridad registrada</legend>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <x-ui.choice-card name="nivel" value="CRITICO" label="Crítico" description="Prioridad crítica" icon="ph-warning-octagon" variant="danger" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="ALTO" label="Alto" description="Prioridad alta" icon="ph-warning" variant="warning" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="MEDIO" label="Medio" description="Prioridad media" icon="ph-info" variant="info" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="BAJO" label="Bajo" description="Prioridad baja" icon="ph-info" variant="info" wire:model.live="nivel" />
                        </div>
                        @error('nivel')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                    </fieldset>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Descripción de la situación" step="3" icon="ph-note-pencil" :columns="1">
                <x-ui.field label="Hecho o situación que requiere seguimiento" for="textareaMotivo" required error="motivo" hint="10 a 10.000 caracteres">
                    <textarea id="textareaMotivo" aria-invalid="{{ $errors->has('motivo') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('motivo') ? 'textareaMotivo-error' : '' }}" wire:model="motivo" rows="4" minlength="10" maxlength="10000" placeholder="Describe hechos observados y por qué requieren revisión o seguimiento." @class(['rm-textarea', 'rm-textarea-error' => $errors->has('motivo')])></textarea>
                </x-ui.field>
            </x-ui.form-section>
        </fieldset>

        <x-slot name="footer">
            <div class="w-full space-y-3">
                @include('livewire.alertas.modales.confirmar-descarte')
                <div class="flex flex-wrap justify-end gap-3">
            <x-ui.action-button variant="ghost" size="sm" wire:click="solicitarCerrarModales" x-bind:disabled="guardados > 0">Cancelar</x-ui.action-button>
            @if(!$preview)<x-ui.action-button variant="primary" size="sm" wire:click="guardarAlerta" loading="guardarAlerta">
                <span wire:loading.remove wire:target="guardarAlerta" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-floppy-disk" aria-hidden="true"></i>Registrar alerta clínica</span>
                <span wire:loading wire:target="guardarAlerta" class="inline-flex items-center gap-1.5">Registrando...</span>
            </x-ui.action-button>@else<button type="button" class="rm-btn rm-btn-primary" disabled><i class="ph-bold ph-lock-simple" aria-hidden="true"></i>Registro del aviso bloqueado · solo lectura</button>@endif
                </div>
            </div>
        </x-slot>
    </x-ui.modal-livewire>
@endif
