<div class="rm-clinical-form" x-data="rmClinicalCapture(@js($registroInicial))" aria-label="Registro de eliminación">
    <x-validation-errors />
    <p class="rm-clinical-form__note"><i class="ph-bold ph-lock" aria-hidden="true"></i> Fecha y hora automáticas al guardar. Se registra un tipo de eliminación por vez.</p>
    <x-ui.form-section title="Tipo de eliminación" icon="ph-drop" :columns="1" class="rm-clinical-form__section">
        <fieldset class="rm-clinical-form__scale" aria-describedby="eliminacion-tipo-help @error('tipo_eliminacion') eliminacion-tipo-error @enderror">
            <legend>Selecciona el tipo <span class="rm-clinical-form__required">Obligatorio</span></legend>
            <div class="rm-form-grid rm-form-grid--2">
                @foreach(['URINARIA' => 'Urinaria', 'INTESTINAL' => 'Intestinal'] as $valor => $texto)
                    <label class="rm-clinical-form__check"><input id="eliminacion-{{ strtolower($valor) }}" type="radio" wire:model.live="elimTipo" name="tipoEliminacion" value="{{ $valor }}" required aria-invalid="{{ $errors->has('tipo_eliminacion') ? 'true' : 'false' }}"> {{ $texto }}</label>
                @endforeach
            </div>
            @error('tipo_eliminacion')<p id="eliminacion-tipo-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
            <p id="eliminacion-tipo-help" class="rm-help">Cambiar de tipo limpia la cantidad, las características y la continencia ingresadas.</p>
        </fieldset>
    </x-ui.form-section>
    @if(in_array($elimTipo, ['URINARIA', 'INTESTINAL'], true))
        @php($sufijoEliminacion = $elimTipo === 'URINARIA' ? 'Urinaria' : 'Intestinal')
        <x-ui.form-section :title="$elimTipo === 'URINARIA' ? 'Eliminación urinaria' : 'Eliminación intestinal'" icon="ph-note-pencil" :columns="2" class="rm-clinical-form__section" wire:key="eliminacion-campos-{{ $elimTipo }}">
            <x-ui.field label="Cantidad observada" for="eliminacion-cantidad" error="cantidad" help="Opcional. Registra la cantidad observada; este registro no define una unidad.">
                <input id="eliminacion-cantidad" class="rm-input" type="number" wire:model="elimCantidad{{ $sufijoEliminacion }}" min="0" step="any" inputmode="decimal" aria-invalid="{{ $errors->has('cantidad') ? 'true' : 'false' }}" aria-describedby="eliminacion-cantidad-help @error('cantidad') eliminacion-cantidad-error @enderror">
            </x-ui.field>
            <x-ui.field label="Continencia" for="eliminacion-continencia" error="continencia">
                <select id="eliminacion-continencia" class="rm-select" wire:model="elimContinencia{{ $sufijoEliminacion }}" aria-invalid="{{ $errors->has('continencia') ? 'true' : 'false' }}" @error('continencia') aria-describedby="eliminacion-continencia-error" @enderror>
                    <option value="">No registrada</option><option value="CONTINENTE">Continente</option>
                    @if($elimTipo === 'URINARIA')<option value="INCONTINENCIA_URINARIA">Incontinencia urinaria</option>@else<option value="INCONTINENCIA_FECAL">Incontinencia fecal</option>@endif
                </select>
            </x-ui.field>
            <x-ui.field label="Características observadas" for="eliminacion-caracteristicas" error="caracteristica" :help="$elimTipo === 'URINARIA' ? 'Describe el color, aspecto u olor observado.' : 'Describe la consistencia y otras características observadas.'" class="rm-clinical-form__full">
                <input id="eliminacion-caracteristicas" class="rm-input" type="text" wire:model="elimCaracteristica{{ $sufijoEliminacion }}" maxlength="120" aria-invalid="{{ $errors->has('caracteristica') ? 'true' : 'false' }}" aria-describedby="eliminacion-caracteristicas-help @error('caracteristica') eliminacion-caracteristicas-error @enderror">
            </x-ui.field>
        </x-ui.form-section>
    @endif
    <x-ui.form-section title="Observaciones" icon="ph-note" :columns="1" class="rm-clinical-form__section">
        <x-ui.field label="Detalles adicionales" for="eliminacion-observacion" error="observacion" help="Describe la asistencia, el dispositivo utilizado u otros detalles relevantes, si corresponde.">
            <textarea id="eliminacion-observacion" class="rm-textarea" wire:model="elimObservacion" rows="3" maxlength="5000" aria-invalid="{{ $errors->has('observacion') ? 'true' : 'false' }}" aria-describedby="eliminacion-observacion-help @error('observacion') eliminacion-observacion-error @enderror"></textarea>
        </x-ui.field>
    </x-ui.form-section>
</div>
