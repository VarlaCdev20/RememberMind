<div>
    <x-ui.modal-livewire id="modalAdministracionMedicacion" wire:model="showModal" maxWidth="2xl" closeMethod="cerrarModal">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)]">
                <i class="ph-bold ph-calendar-check text-lg" aria-hidden="true"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div>
                <span class="block">Registrar administración o toma</span>
                <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-secondary)]">Control asistencial de medicación</span>
            </div>
        </x-slot>

        <form wire:submit="guardar" id="formAdministracion" class="space-y-4">
            <section aria-labelledby="orden-medica-title">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-[var(--rm-info)]">Orden médica · solo lectura</p>
                        <h3 id="orden-medica-title" class="text-base font-extrabold text-[var(--rm-text-primary)]">{{ $medicamento_nombre ?: 'Sin seleccionar' }}</h3>
                    </div>
                    <x-ui.status-badge estado="ACTIVA" />
                </div>
                <dl class="rm-clinical-readonly-order">
                    <div><dt>Dosis prescrita</dt><dd>{{ $dosis_prescrita ?: '—' }} {{ $unidad_dosis }}</dd></div>
                    <div><dt>Vía</dt><dd>{{ $via_administracion ?: '—' }}</dd></div>
                    <div><dt>Frecuencia</dt><dd>{{ $frecuencia ?: '—' }}</dd></div>
                    <div><dt>Horario</dt><dd>{{ $hora_programada ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt>Indicación</dt><dd>{{ $indicacion ?: 'Sin indicación adicional' }}</dd></div>
                    <div class="sm:col-span-2"><dt>Profesional prescriptor</dt><dd>{{ $medico_prescriptor }}</dd></div>
                </dl>
                <p class="mt-2 text-[11px] font-semibold text-[var(--rm-text-secondary)]">
                    Enfermería puede consultar esta orden y registrar su ejecución; no puede modificar dosis, vía, frecuencia ni suspenderla.
                </p>
            </section>

            <x-ui.form-section title="Estado de la toma" step="1" :columns="2">
                <x-ui.choice-card name="administrado" value="1" label="Administrado" description="La toma fue realizada" icon="ph-check-circle" variant="success" wire:model.live="administrado" />
                <x-ui.choice-card name="administrado" value="0" label="Omitido o rechazado" description="Requiere justificación" icon="ph-x-circle" variant="danger" wire:model.live="administrado" />
            </x-ui.form-section>

            <x-ui.form-section title="Detalle del registro" step="2" :columns="2">
                <x-ui.field label="Fecha de toma" for="administracion-fecha" required error="fecha">
                    <input id="administracion-fecha" type="date" wire:model="fecha" @class(['rm-input', 'rm-input-error' => $errors->has('fecha')])>
                </x-ui.field>

                @if($administrado)
                    <x-ui.field label="Hora real de toma" for="administracion-hora" required error="hora_real">
                        <input id="administracion-hora" type="time" wire:model="hora_real" @class(['rm-input', 'rm-input-error' => $errors->has('hora_real')])>
                    </x-ui.field>
                    <x-ui.field label="Dosis administrada" for="administracion-dosis" required error="dosis_administrada" :hint="$unidad_dosis">
                        <input id="administracion-dosis" type="number" min="0.001" step="0.001" wire:model="dosis_administrada" @class(['rm-input', 'rm-input-error' => $errors->has('dosis_administrada')])>
                    </x-ui.field>
                @endif
            </x-ui.form-section>

            @if(!$administrado)
                <x-ui.form-section title="Motivo de omisión o rechazo" step="3" icon="ph-warning" :columns="1">
                    <x-ui.field label="Justificación asistencial" for="administracion-motivo" required error="motivo_omision" help="Registre el motivo observado sin sustituir la valoración clínica.">
                        <textarea id="administracion-motivo" wire:model="motivo_omision" rows="3" placeholder="Ej.: el residente rechazó la toma o presentó náuseas." @class(['rm-textarea', 'rm-textarea-error' => $errors->has('motivo_omision')])></textarea>
                    </x-ui.field>
                </x-ui.form-section>
            @endif

            <x-ui.form-section title="Observaciones adicionales" :step="$administrado ? '3' : '4'" :columns="2">
                @if($administrado)
                    <x-ui.field label="Efecto observado" for="administracion-efecto" error="efecto_observado">
                        <textarea id="administracion-efecto" wire:model="efecto_observado" rows="2" placeholder="Ej.: tolerancia adecuada, respuesta clínica observada" @class(['rm-textarea', 'rm-textarea-error' => $errors->has('efecto_observado')])></textarea>
                    </x-ui.field>
                    <x-ui.field label="Reacción adversa" for="administracion-reaccion" error="reaccion_adversa" help="Si se observa una reacción, descríbala y active el protocolo clínico correspondiente.">
                        <textarea id="administracion-reaccion" wire:model="reaccion_adversa" rows="2" placeholder="Sin reacción observada" @class(['rm-textarea', 'rm-textarea-error' => $errors->has('reaccion_adversa')])></textarea>
                    </x-ui.field>
                @endif

                <x-ui.field class="md:col-span-2" label="Nota de turno" for="administracion-observacion" error="observacion">
                    <textarea id="administracion-observacion" wire:model="observacion" rows="2" placeholder="Detalle complementario de enfermería" @class(['rm-textarea', 'rm-textarea-error' => $errors->has('observacion')])></textarea>
                </x-ui.field>
            </x-ui.form-section>
        </form>

        <x-slot name="footer">
            <x-ui.action-button variant="ghost" size="sm" wire:click="cerrarModal">Cancelar</x-ui.action-button>
            <x-ui.action-button :variant="$administrado ? 'primary' : 'accent'" size="sm" tipo="submit" form="formAdministracion" loading="guardar" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin" aria-hidden="true"></i>
                <i wire:loading.remove wire:target="guardar" class="ph-bold {{ $administrado ? 'ph-check-circle' : 'ph-x-circle' }}" aria-hidden="true"></i>
                <span>{{ $administrado ? 'Confirmar toma' : 'Registrar omisión' }}</span>
            </x-ui.action-button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
