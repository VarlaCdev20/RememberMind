<div>
    @if($mostrar)
        <x-ui.modal-livewire id="modalRegistroSignosVitales" wire:model="mostrar" maxWidth="lg" closeMethod="cerrar">
            <x-slot name="icon">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-danger-soft)] text-[var(--rm-danger)]">
                    <i class="ph-bold ph-heartbeat text-lg" aria-hidden="true"></i>
                </span>
            </x-slot>

            <x-slot name="title">
                <div>
                    <span class="block">Registrar signos vitales</span>
                    @if($adulto)
                        <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-secondary)]">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</span>
                    @endif
                </div>
            </x-slot>

            <div class="space-y-4">
                @error('general')
                    <x-ui.callout variant="danger" title="No se pudo registrar el control">{{ $message }}</x-ui.callout>
                @enderror

                <x-ui.form-section title="Fecha y hora" icon="ph-calendar-blank" :columns="2">
                    <x-ui.field label="Fecha" for="signos-fecha" required error="fecha">
                        <input id="signos-fecha" wire:model="fecha" type="date" @class(['rm-input', 'rm-input-error' => $errors->has('fecha')])>
                    </x-ui.field>
                    <x-ui.field label="Hora" for="signos-hora" error="hora">
                        <input id="signos-hora" wire:model="hora" type="time" @class(['rm-input', 'rm-input-error' => $errors->has('hora')])>
                    </x-ui.field>
                </x-ui.form-section>

                <x-ui.form-section title="Cardiovascular" icon="ph-heart" :columns="3">
                    <x-ui.field label="PA sistólica (mmHg)" for="signos-pa-sistolica" error="pa_sistolica">
                        <input id="signos-pa-sistolica" wire:model.live="pa_sistolica" type="number" min="1" max="400" placeholder="120" @class(['rm-input', 'rm-input-error' => $errors->has('pa_sistolica')])>
                    </x-ui.field>
                    <x-ui.field label="PA diastólica (mmHg)" for="signos-pa-diastolica" error="pa_diastolica">
                        <input id="signos-pa-diastolica" wire:model.live="pa_diastolica" type="number" min="1" max="400" placeholder="80" @class(['rm-input', 'rm-input-error' => $errors->has('pa_diastolica')])>
                    </x-ui.field>
                    <x-ui.field label="Frecuencia cardiaca (bpm)" for="signos-fc" error="fc">
                        <input id="signos-fc" wire:model.live="fc" type="number" min="1" max="300" placeholder="70" @class(['rm-input', 'rm-input-error' => $errors->has('fc')])>
                    </x-ui.field>

                    @if($pa_sistolica && ($pa_sistolica > 140 || $pa_sistolica < 90))
                        <x-ui.callout class="md:col-span-3" variant="warning" title="Presión arterial fuera del rango esperado">
                            PA {{ $pa_sistolica > 140 ? 'elevada' : 'baja' }}; requiere valoración según protocolo.
                        </x-ui.callout>
                    @endif

                    @if(is_numeric($pa_sistolica) && is_numeric($pa_diastolica) && (int) $pa_sistolica <= (int) $pa_diastolica)
                        <label class="md:col-span-3 flex cursor-pointer items-start gap-3 rounded-[var(--rm-radius-input)] border border-[var(--rm-warning)] bg-[var(--rm-warning-soft)] p-3 text-xs font-semibold text-[var(--rm-warning-strong)]">
                            <input wire:model="confirmar_presion_atipica" type="checkbox" class="rm-checkbox mt-0.5">
                            <span>Repetí la medición y confirmo que estos valores atípicos son correctos.</span>
                        </label>
                    @endif
                </x-ui.form-section>

                <x-ui.form-section title="Respiratorio" icon="ph-wind" :columns="2">
                    <x-ui.field label="Frecuencia respiratoria (rpm)" for="signos-fr" error="fr">
                        <input id="signos-fr" wire:model.live="fr" type="number" min="1" max="100" placeholder="16" @class(['rm-input', 'rm-input-error' => $errors->has('fr')])>
                    </x-ui.field>
                    <x-ui.field label="Saturación O₂ (%)" for="signos-saturacion" error="saturacion">
                        <input id="signos-saturacion" wire:model.live="saturacion" type="number" min="0" max="100" placeholder="98" @class(['rm-input', 'rm-input-error' => $errors->has('saturacion')])>
                    </x-ui.field>
                    @if($saturacion && $saturacion < 92)
                        <x-ui.callout class="md:col-span-2" variant="danger" title="Saturación crítica">
                            Saturación registrada: {{ $saturacion }}%. Active el protocolo asistencial correspondiente.
                        </x-ui.callout>
                    @endif
                </x-ui.form-section>

                <x-ui.form-section title="Temperatura y glucemia" icon="ph-thermometer" :columns="2">
                    <x-ui.field label="Temperatura (°C)" for="signos-temperatura" error="temperatura">
                        <input id="signos-temperatura" wire:model.live="temperatura" type="number" step="0.1" min="25" max="45" placeholder="36.5" @class(['rm-input', 'rm-input-error' => $errors->has('temperatura')])>
                    </x-ui.field>
                    <x-ui.field label="Glucosa (mg/dL)" for="signos-glucosa" error="glucosa">
                        <input id="signos-glucosa" wire:model.live="glucosa" type="number" step="0.1" min="0" placeholder="100" @class(['rm-input', 'rm-input-error' => $errors->has('glucosa')])>
                    </x-ui.field>
                </x-ui.form-section>

                <x-ui.form-section title="Antropometría" icon="ph-ruler" :columns="3">
                    <x-ui.field label="Peso (kg)" for="signos-peso" error="peso">
                        <input id="signos-peso" wire:model.live="peso" type="number" step="0.1" min="10" max="300" placeholder="kg" @class(['rm-input', 'rm-input-error' => $errors->has('peso')])>
                    </x-ui.field>
                    <x-ui.field label="Talla (cm)" for="signos-talla" error="talla">
                        <input id="signos-talla" wire:model.live="talla" type="number" step="0.1" min="50" max="240" placeholder="165" @class(['rm-input', 'rm-input-error' => $errors->has('talla')])>
                    </x-ui.field>
                    <x-ui.field label="IMC">
                        <output class="rm-input flex items-center font-extrabold" aria-live="polite">
                            {{ $imc ? number_format($imc, 1) : '—' }}
                            @if($imc)
                                <span class="ml-2 text-xs font-semibold text-[var(--rm-text-secondary)]">{{ $imc < 18.5 ? 'Bajo' : ($imc < 25 ? 'Normal' : ($imc < 30 ? 'Sobrepeso' : 'Obesidad')) }}</span>
                            @endif
                        </output>
                    </x-ui.field>
                </x-ui.form-section>

                <x-ui.form-section title="Dolor y observaciones" icon="ph-activity" :columns="1">
                    <x-ui.field label="Escala visual de dolor (EVA 0–10)" for="signos-dolor">
                        <div class="flex items-center gap-3">
                            <input id="signos-dolor" wire:model.live="dolor" type="range" min="0" max="10" step="1" class="flex-1 accent-[var(--rm-action-primary)]">
                            <output class="rm-badge rm-badge-neutral min-w-10 justify-center">{{ $dolor ?? 0 }}</output>
                            <span class="min-w-20 text-xs font-semibold text-[var(--rm-text-secondary)]">{{ ($dolor ?? 0) == 0 ? 'Sin dolor' : (($dolor ?? 0) < 4 ? 'Leve' : (($dolor ?? 0) < 7 ? 'Moderado' : 'Severo')) }}</span>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Observación" for="signos-observacion" error="observacion">
                        <textarea id="signos-observacion" wire:model="observacion" rows="3" placeholder="Comentarios adicionales" @class(['rm-textarea', 'rm-textarea-error' => $errors->has('observacion')])></textarea>
                    </x-ui.field>
                </x-ui.form-section>
            </div>

            <x-slot name="footer">
                <x-ui.action-button variant="ghost" size="sm" wire:click="cerrar">Cancelar</x-ui.action-button>
                <x-ui.action-button variant="primary" size="sm" wire:click="guardar" loading="guardar">
                    <span wire:loading.remove wire:target="guardar" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-check" aria-hidden="true"></i>Registrar control</span>
                    <span wire:loading wire:target="guardar" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-spinner animate-spin" aria-hidden="true"></i>Guardando...</span>
                </x-ui.action-button>
            </x-slot>
        </x-ui.modal-livewire>
    @endif
</div>
