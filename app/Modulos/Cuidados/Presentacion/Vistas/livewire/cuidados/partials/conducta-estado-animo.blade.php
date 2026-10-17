<div class="rm-free-text-select rm-conducta__mood-picker" x-data="rmFreeTextSelect($wire.entangle('datos.estado_animo'), ['TRANQUILO','ANSIOSO','TRISTE','IRRITABLE','TEMEROSO','ALEGRE'])" @conducta-captura-reiniciada.window="choice = ''; customText = ''">
    <fieldset class="rm-conducta__mood-options"><legend class="rm-label">Estado de ánimo</legend><div class="rm-conducta__tri">
        @foreach(['' => 'Sin registrar', 'TRANQUILO' => 'Tranquilo', 'ANSIOSO' => 'Ansioso', 'TRISTE' => 'Triste', 'IRRITABLE' => 'Irritable', 'TEMEROSO' => 'Temeroso', 'ALEGRE' => 'Alegre', '__custom' => 'Otro / escribir'] as $value => $label)
            <label><input type="radio" name="conducta-estado_animo" value="{{ $value }}" :checked="choice === @js($value)" @change="choose($event.target.value)"><span>{{ $label }}</span></label>
        @endforeach
    </div></fieldset>
    <div x-show="choice === '__custom'" x-cloak class="rm-free-text-select__custom"><x-ui.field for="conducta-estado_animo-custom" label="Especifica el estado de ánimo" error="datos.estado_animo"><input id="conducta-estado_animo-custom" x-ref="custom" class="rm-input" type="text" :value="customText" @input="write($event.target.value)" :disabled="choice !== '__custom'" maxlength="40" aria-invalid="{{ $errors->has('datos.estado_animo') ? 'true' : 'false' }}" @error('datos.estado_animo') aria-describedby="conducta-estado_animo-custom-error" @enderror></x-ui.field></div>
    @if($errors->has('datos.estado_animo'))<p class="rm-error" role="alert">{{ $errors->first('datos.estado_animo') }}</p>@endif
</div>
