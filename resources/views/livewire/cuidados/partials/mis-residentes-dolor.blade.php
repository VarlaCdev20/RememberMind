<div class="rm-clinical-form" x-data="rmClinicalCapture(@js($registroInicial))" aria-label="Valoración de dolor">
    <x-validation-errors class="rm-clinical-form__errors" />

    <div class="rm-clinical-form__lead">
        <x-ui.form-section title="Momento de la valoración" icon="ph-clock" :columns="1" class="rm-clinical-form__section">
            <x-ui.field label="Fecha y hora de valoración" for="dolor-fecha" error="fecha_hora" :required="true" help="Indica cuándo se realizó la valoración.">
                <input id="dolor-fecha" class="rm-input" type="datetime-local" wire:model="dolorFechaHora" required
                       aria-invalid="{{ $errors->has('fecha_hora') ? 'true' : 'false' }}" aria-describedby="dolor-fecha-help @error('fecha_hora') dolor-fecha-error @enderror">
            </x-ui.field>
            <p class="rm-clinical-form__note">Cada valoración se guarda como un nuevo registro.</p>
        </x-ui.form-section>

        <x-ui.form-section title="Intensidad inicial" icon="ph-thermometer" :columns="1" class="rm-clinical-form__section rm-clinical-form__intensity">
            <fieldset class="rm-clinical-form__scale" x-data="{ eva: $wire.entangle('dolorEva').live }"
                      aria-describedby="dolor-eva-ayuda @error('intensidad') dolor-eva-error @enderror" aria-invalid="{{ $errors->has('intensidad') ? 'true' : 'false' }}">
                <legend>Escala EVA inicial, de 0 a 10 <span class="rm-clinical-form__required">Obligatoria</span></legend>
                <div class="rm-clinical-form__value" aria-live="polite"><strong x-text="eva === '' ? '—' : eva"></strong><span>/ 10</span><small x-text="eva === '' ? 'Selecciona la intensidad' : 'Intensidad seleccionada · Sin guardar'"></small></div>
                <div class="rm-clinical-form__scale-options">
                    @foreach(range(0, 10) as $valor)
                        <label class="rm-clinical-form__scale-option">
                            <input id="dolor-eva-{{ $valor }}" type="radio" name="dolor-eva-inicial" value="{{ $valor }}" wire:model.live="dolorEva" x-model="eva" required aria-label="{{ $valor }} de 10">
                            <span>{{ $valor }}</span>
                        </label>
                    @endforeach
                </div>
                <p id="dolor-eva-ayuda" class="rm-clinical-form__note">0 = sin dolor; 10 = máxima intensidad de la escala.</p>
                @error('intensidad') <p id="dolor-eva-error" role="alert" class="rm-error">{{ $message }}</p> @enderror
            </fieldset>
        </x-ui.form-section>
    </div>

    <x-ui.form-section title="Características del dolor" icon="ph-person" :columns="2" class="rm-clinical-form__section">
        <x-ui.field label="Localización" for="dolor-ubicacion" error="ubicacion" class="rm-clinical-form__full">
            <input id="dolor-ubicacion" class="rm-input" type="text" wire:model="dolorUbicacion" maxlength="120" placeholder="Describe dónde se localiza el dolor"
                   aria-invalid="{{ $errors->has('ubicacion') ? 'true' : 'false' }}" @error('ubicacion') aria-describedby="dolor-ubicacion-error" @enderror>
        </x-ui.field>
        <x-ui.field label="Duración" for="dolor-duracion" error="duracion_valor" help="Si registras una duración, indica también su unidad.">
            <input id="dolor-duracion" class="rm-input" type="number" wire:model="dolorDuracionValor" min="0.01" step="any" inputmode="decimal" placeholder="Ej. 30"
                   aria-invalid="{{ $errors->has('duracion_valor') ? 'true' : 'false' }}" aria-describedby="dolor-duracion-help @error('duracion_valor') dolor-duracion-error @enderror">
        </x-ui.field>
        <x-ui.field label="Unidad de duración" for="dolor-unidad" error="duracion_unidad">
            <input id="dolor-unidad" class="rm-input" type="text" wire:model="dolorDuracionUnidad" maxlength="60" placeholder="Ej. minutos"
                   aria-invalid="{{ $errors->has('duracion_unidad') ? 'true' : 'false' }}" @error('duracion_unidad') aria-describedby="dolor-unidad-error" @enderror>
        </x-ui.field>
        <x-ui.field label="Desencadenante" for="dolor-desencadenante" error="desencadenante" class="rm-clinical-form__full">
            <textarea id="dolor-desencadenante" class="rm-textarea" wire:model="dolorDesencadenante" rows="2" placeholder="Circunstancias que desencadenaron el dolor, si se conocen"
                      aria-invalid="{{ $errors->has('desencadenante') ? 'true' : 'false' }}" @error('desencadenante') aria-describedby="dolor-desencadenante-error" @enderror></textarea>
        </x-ui.field>
    </x-ui.form-section>

    <x-ui.form-section title="Intervención realizada" icon="ph-first-aid" :columns="1" class="rm-clinical-form__section">
        <x-ui.field label="Medidas realizadas, si corresponde" for="dolor-intervencion" error="intervencion">
            <textarea id="dolor-intervencion" class="rm-textarea" wire:model="dolorIntervencion" rows="3" placeholder="Describe la intervención durante esta valoración"
                      aria-invalid="{{ $errors->has('intervencion') ? 'true' : 'false' }}" @error('intervencion') aria-describedby="dolor-intervencion-error" @enderror></textarea>
        </x-ui.field>
    </x-ui.form-section>
    @can('enfermeria.ver_ficha_paciente')
        <p class="rm-clinical-form__continuity"><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i> Consulta la última valoración en el resumen clínico de la ficha.
            <a class="rm-clinical-form__history-link" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente']]) }}" target="_blank" rel="noopener">Consultar ficha clínica <span class="sr-only">(abre otra pestaña)</span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
        </p>
    @endcan
</div>
