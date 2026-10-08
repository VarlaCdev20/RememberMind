<div class="rm-clinical-form" x-data="rmClinicalCapture(@js($registroInicial))" aria-label="Registro de ingesta">
    <x-validation-errors class="rm-clinical-form__errors" />
    <p class="rm-clinical-form__note"><i class="ph-bold ph-lock" aria-hidden="true"></i> La fecha y hora se registran automáticamente al guardar. Cada comida genera un nuevo registro.</p>

    <x-ui.form-section title="Comida e ingesta" icon="ph-bowl-food" :columns="2" class="rm-clinical-form__section">
        <x-ui.field label="Tipo de comida" for="ingesta-comida" error="tipo_comida" :required="true" class="rm-clinical-form__full">
            <select id="ingesta-comida" class="rm-select" wire:model="ingestaTipoComida" required aria-invalid="{{ $errors->has('tipo_comida') ? 'true' : 'false' }}" @error('tipo_comida') aria-describedby="ingesta-comida-error" @enderror>
                <option value="">Seleccionar</option>
                @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::TIPOS_COMIDA as $tipo)
                    <option value="{{ $tipo }}">{{ match ($tipo) { 'MEDIA_MANANA' => 'Media mañana', 'COLACION' => 'Colación', default => mb_convert_case(str_replace('_', ' ', $tipo), MB_CASE_TITLE, 'UTF-8') } }}</option>
                @endforeach
            </select>
        </x-ui.field>
        <x-ui.field label="Porcentaje consumido" for="ingesta-porcentaje" error="porcentaje_consumido" help="Cantidad de la comida que consumió, de 0 a 100 %.">
            <div class="rm-clinical-form__unit"><input id="ingesta-porcentaje" class="rm-input" type="number" wire:model="ingestaPorcentaje" min="0" max="100" step="0.01" inputmode="decimal" aria-label="Porcentaje consumido en porcentaje" aria-invalid="{{ $errors->has('porcentaje_consumido') ? 'true' : 'false' }}" aria-describedby="ingesta-porcentaje-help @error('porcentaje_consumido') ingesta-porcentaje-error @enderror"><span aria-hidden="true">%</span></div>
        </x-ui.field>
        @can('registros_hidratacion.crear')
            <x-ui.field label="Líquidos consumidos" for="ingesta-liquidos" error="cantidad_ml" help="Si los completas, se guardará también un registro de hidratación.">
                <div class="rm-clinical-form__unit"><input id="ingesta-liquidos" class="rm-input" type="number" wire:model="ingestaCantidadMl" min="0" max="999999.99" step="0.01" inputmode="decimal" aria-label="Líquidos consumidos en mililitros" aria-invalid="{{ $errors->has('cantidad_ml') ? 'true' : 'false' }}" aria-describedby="ingesta-liquidos-help @error('cantidad_ml') ingesta-liquidos-error @enderror"><span aria-hidden="true">mL</span></div>
            </x-ui.field>
        @endcan
    </x-ui.form-section>
    <x-ui.form-section title="Tolerancia y deglución" icon="ph-first-aid" :columns="1" class="rm-clinical-form__section">
        <x-ui.field label="Tolerancia" for="ingesta-tolerancia" error="tolerancia">
            <select id="ingesta-tolerancia" class="rm-select" wire:model="ingestaTolerancia" aria-invalid="{{ $errors->has('tolerancia') ? 'true' : 'false' }}" @error('tolerancia') aria-describedby="ingesta-tolerancia-error" @enderror>
                <option value="">Sin registrar</option>
                @foreach(\App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService::TOLERANCIAS_INGESTA as $tipo)
                    <option value="{{ $tipo }}">{{ match($tipo) { 'NAUSEAS' => 'Náuseas', 'VOMITO' => 'Vómito', default => mb_convert_case($tipo, MB_CASE_TITLE, 'UTF-8') } }}</option>
                @endforeach
            </select>
        </x-ui.field>
        <label class="rm-clinical-form__check"><input id="ingesta-deglucion" type="checkbox" wire:model="ingestaDificultadDeglucion" @error('dificultad_deglucion') aria-describedby="ingesta-deglucion-error" aria-invalid="true" @enderror><span>Dificultad para deglutir</span></label>
        @error('dificultad_deglucion')<p id="ingesta-deglucion-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
    </x-ui.form-section>
    <x-ui.form-section title="Observaciones" icon="ph-note-pencil" :columns="1" class="rm-clinical-form__section">
        <x-ui.field label="Asistencia y detalles observados" for="ingesta-observacion" error="observacion" help="Describe la ayuda para comer y otras dificultades, si las hubo.">
            <textarea id="ingesta-observacion" class="rm-textarea" wire:model="ingestaObservacion" rows="3" maxlength="5000" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" aria-describedby="ingesta-observacion-help @error('observacion') ingesta-observacion-error @enderror"></textarea>
        </x-ui.field>
    </x-ui.form-section>
</div>
