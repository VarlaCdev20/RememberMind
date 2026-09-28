@if($modalCrear)
    <x-ui.modal-livewire id="modalCrearAlerta" wire:model="modalCrear" maxWidth="lg" closeMethod="cerrarModales">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-danger-soft)] text-[var(--rm-danger)]">
                <i class="ph-bold ph-bell-ringing text-lg" aria-hidden="true"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div>
                <span class="block">Registrar nueva alerta clínica</span>
                <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-secondary)]">Evento asistencial con notificación al personal de turno</span>
            </div>
        </x-slot>

        <div class="space-y-4">
            <x-ui.form-section title="Selección del residente" step="1" icon="ph-user" :columns="1">
                <x-ui.field label="Residente asignado" for="selectResidente" required error="codResidente">
                    <select id="selectResidente" wire:model.live="codResidente" @class(['rm-select', 'rm-select-error' => $errors->has('codResidente')])>
                        <option value="">Seleccione un residente asistido</option>
                        @foreach($adultos as $ad)
                            <option value="{{ $ad->cod_residente }}">
                                {{ $ad->ap_paterno }} {{ $ad->ap_materno }} {{ $ad->nombres }} ({{ $ad->ubicacion_texto }})
                            </option>
                        @endforeach
                    </select>
                </x-ui.field>
            </x-ui.form-section>

            <x-ui.form-section title="Parámetros clínicos" step="2" icon="ph-sliders-horizontal" :columns="2">
                <x-ui.field label="Origen del evento" for="selectOrigen" required error="origen">
                    <select id="selectOrigen" wire:model="origen" @class(['rm-select', 'rm-select-error' => $errors->has('origen')])>
                        <option value="SIGNOS">Signos vitales alterados</option>
                        <option value="MEDICACION">Administración de fármaco</option>
                        <option value="INCIDENTE">Incidente o caída asistencial</option>
                        <option value="MANUAL">Reporte de turno o manual</option>
                        <option value="PLAN">Plan de cuidados continuos</option>
                        <option value="SEGUIMIENTO">Seguimiento clínico especial</option>
                        <option value="SOLICITUD_MEDICA">Solicitud médica directa</option>
                        <option value="FICHA">Ficha clínica integral</option>
                        <option value="VALORACION">Valoración funcional</option>
                    </select>
                </x-ui.field>

                <x-ui.field label="Tipo o diagnóstico clínico" for="inputTipoAlerta" required error="tipoAlerta">
                    <input id="inputTipoAlerta" type="text" wire:model="tipoAlerta" placeholder="Ej.: CRISIS_HIPERTENSIVA" @class(['rm-input font-mono uppercase', 'rm-input-error' => $errors->has('tipoAlerta')])>
                </x-ui.field>

                <div class="md:col-span-2">
                    <x-ui.field label="Nivel de severidad y prioridad" required error="nivel">
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <x-ui.choice-card name="nivel" value="CRITICO" label="Crítico" description="Riesgo vital" icon="ph-warning-octagon" variant="danger" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="ALTO" label="Alto" description="Urgente" icon="ph-warning" variant="danger" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="MEDIO" label="Medio" description="En turno" icon="ph-info" variant="warning" wire:model.live="nivel" />
                            <x-ui.choice-card name="nivel" value="BAJO" label="Bajo" description="Preventivo" icon="ph-check-circle" variant="info" wire:model.live="nivel" />
                        </div>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Justificación clínica" step="3" icon="ph-note-pencil" :columns="1">
                <x-ui.field label="Descripción del cuadro clínico o incidente" for="textareaMotivo" required error="motivo" hint="Mín. 10 caracteres">
                    <textarea id="textareaMotivo" wire:model="motivo" rows="4" placeholder="Detalle los hallazgos observados y la causa que motiva esta alerta." @class(['rm-textarea', 'rm-textarea-error' => $errors->has('motivo')])></textarea>
                </x-ui.field>
            </x-ui.form-section>
        </div>

        <x-slot name="footer">
            <x-ui.action-button variant="ghost" size="sm" wire:click="cerrarModales">Cancelar</x-ui.action-button>
            <x-ui.action-button variant="primary" size="sm" wire:click="guardarAlerta" loading="guardarAlerta">
                <span wire:loading.remove wire:target="guardarAlerta" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-floppy-disk" aria-hidden="true"></i>Guardar alerta clínica</span>
                <span wire:loading wire:target="guardarAlerta" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-spinner animate-spin" aria-hidden="true"></i>Registrando...</span>
            </x-ui.action-button>
        </x-slot>
    </x-ui.modal-livewire>
@endif
