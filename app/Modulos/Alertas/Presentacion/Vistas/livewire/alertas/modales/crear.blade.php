@if($modalCrear)
    <x-ui.modal-livewire id="modalCrearAlerta" wire:model="modalCrear" maxWidth="lg" closeMethod="solicitarCerrarModales" alpineBusy="guardados > 0">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-danger-soft)] text-[var(--rm-danger)]">
                <i class="ph-bold ph-bell-ringing text-lg" aria-hidden="true"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div>
                <span class="block">Registrar nueva alerta</span>
                <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-secondary)]">Describe la situación, su origen y la prioridad del aviso.</span>
            </div>
        </x-slot>

        <div class="space-y-4">
            <x-ui.callout variant="info" title="Aviso manual">Registrar una alerta no acredita una medición, un diagnóstico ni una atención. Los registros de origen se documentan en su proceso autorizado.</x-ui.callout>
            <x-ui.form-section title="Selección del residente" step="1" icon="ph-user" :columns="1">
                <x-ui.field :label="$perfil['foco'] === 'turno' ? 'Residente de mi ámbito' : 'Residente'" for="selectResidente" required error="codResidente">
                    <x-ui.selector id="selectResidente" label="Residente" wire:model.live="codResidente" teleport="#modalCrearAlerta" :aria-invalid="$errors->has('codResidente') ? 'true' : 'false'" aria-describedby="selectResidente-error">
                        <option value="">Seleccione un residente disponible</option>
                        @foreach($adultos as $ad)
                            <option value="{{ $ad->cod_residente }}">
                                {{ $ad->apellido_paterno }} {{ $ad->apellido_materno }} {{ $ad->nombres }} ({{ $ad->ubicacion_formateada }})
                            </option>
                        @endforeach
                    </x-ui.selector>
                </x-ui.field>
            </x-ui.form-section>

            <x-ui.form-section title="Origen y prioridad" step="2" icon="ph-sliders-horizontal" :columns="2">
                <x-ui.field label="Origen del evento" for="selectOrigen" required error="origen">
                    <x-ui.selector id="selectOrigen" label="Origen del evento" wire:model="origen" teleport="#modalCrearAlerta" :aria-invalid="$errors->has('origen') ? 'true' : 'false'" aria-describedby="selectOrigen-error">
                        <option value="SIGNOS">Signos vitales</option>
                        <option value="MEDICACION">Medicación</option>
                        <option value="INCIDENTE">Incidente</option>
                        <option value="MANUAL">Aviso manual</option>
                        <option value="PLAN">Plan de cuidados</option>
                        <option value="SEGUIMIENTO">Seguimiento</option>
                        <option value="SOLICITUD_MEDICA">Solicitud médica</option>
                        <option value="FICHA">Ficha del residente</option>
                        <option value="VALORACION">Valoración</option>
                    </x-ui.selector>
                </x-ui.field>

                <x-ui.field label="Tipo de alerta" for="inputTipoAlerta" required error="tipoAlerta">
                    <input id="inputTipoAlerta" type="text" wire:model="tipoAlerta" minlength="3" maxlength="60" placeholder="Ej.: Situación que requiere revisión" @class(['rm-input', 'rm-input-error' => $errors->has('tipoAlerta')])>
                </x-ui.field>

                <div class="md:col-span-2">
                    <fieldset class="rm-field"><legend class="rm-label rm-label-required">Prioridad registrada</legend>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <x-ui.choice-card name="nivel" value="CRITICO" label="Crítico" description="Prioridad crítica" icon="ph-warning-octagon" variant="danger" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="ALTO" label="Alto" description="Prioridad alta" icon="ph-warning" variant="danger" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="MEDIO" label="Medio" description="Prioridad media" icon="ph-info" variant="warning" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="BAJO" label="Bajo" description="Prioridad baja" icon="ph-check-circle" variant="info" wire:model.live="nivel" />
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
        </div>

        <x-slot name="footer">
            <div class="w-full space-y-3">
                @include('livewire.alertas.modales.confirmar-descarte')
                <div class="flex flex-wrap justify-end gap-3">
            <x-ui.action-button variant="ghost" size="sm" wire:click="solicitarCerrarModales" x-bind:disabled="guardados > 0">Cancelar</x-ui.action-button>
            <x-ui.action-button variant="primary" size="sm" wire:click="guardarAlerta" loading="guardarAlerta">
                <span wire:loading.remove wire:target="guardarAlerta" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-floppy-disk" aria-hidden="true"></i>Guardar alerta</span>
                <span wire:loading wire:target="guardarAlerta" class="inline-flex items-center gap-1.5">Registrando...</span>
            </x-ui.action-button>
                </div>
            </div>
        </x-slot>
    </x-ui.modal-livewire>
@endif
