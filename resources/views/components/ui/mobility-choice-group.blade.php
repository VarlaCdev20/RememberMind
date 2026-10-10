@props(['field', 'label', 'options', 'icon' => 'ph-circle', 'required' => false])
<fieldset class="rm-movilidad__group" data-field="{{ $field }}" aria-describedby="movilidad-{{ $field }}-help @error($field) movilidad-{{ $field }}-error @enderror">
    <legend><i class="ph-bold {{ $icon }}" aria-hidden="true"></i> {{ $label }} @if($required)<small>Obligatorio</small>@endif</legend>
    <span id="movilidad-{{ $field }}-help" class="sr-only">{{ $required ? 'Selecciona una opción.' : 'Opcional. Selecciona una opción o deja el campo sin registrar.' }} Usa las flechas para cambiar la selección.</span>
    <div role="radiogroup" aria-label="{{ $label }}" aria-required="{{ $required ? 'true' : 'false' }}" class="rm-movilidad__choices">
        @foreach($options as $index => $option)
            <button type="button" role="radio" :aria-checked="values[@js($field)] === @js($option['value'])" :tabindex="values[@js($field)] === @js($option['value']) || (!@js(array_column($options, 'value')).includes(values[@js($field)]) && {{ $index }} === 0) ? 0 : -1" @click="setValue(@js($field), @js($option['value']))" @keydown.arrow-right.prevent="chooseNext($event, @js($field), @js($options), 1)" @keydown.arrow-left.prevent="chooseNext($event, @js($field), @js($options), -1)" @keydown.arrow-down.prevent="chooseNext($event, @js($field), @js($options), 1)" @keydown.arrow-up.prevent="chooseNext($event, @js($field), @js($options), -1)" aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" aria-describedby="movilidad-{{ $field }}-help @error($field) movilidad-{{ $field }}-error @enderror" data-choice-color="{{ $option['value'] === '' || $option['value'] === null || $option['value'] === 'NO_VALORABLE' ? 'neutral' : ['care', 'info', 'violet', 'sand'][$index % 4] }}" data-tone="{{ in_array($field, ['fatiga', 'riesgo_caida'], true) && in_array($option['value'], ['SEVERA', 'ALTO'], true) ? 'danger' : (in_array($option['value'], ['LEVE', 'MODERADA', 'MEDIO', 'INESTABLE'], true) ? 'warning' : 'care') }}">
                @if(in_array($field, ['actividad_realizada', 'marcha', 'traslado', 'dispositivo', 'cambio_habitual'], true))<x-ui.mobility-choice-icon :code="$option['value']" />@endif
                <span>{{ $option['label'] }}</span>
            </button>
        @endforeach
    </div>
    @error($field)<p id="movilidad-{{ $field }}-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
</fieldset>
