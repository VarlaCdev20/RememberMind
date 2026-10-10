@php($clave = $campo['key'])
@php($errorClave = $campo['error'])
<div class="rm-signos__field" x-bind:class="{ 'rm-signos__field--invalid': hasError('{{ $clave }}') || @js($errors->has($errorClave)) }">
    @if(isset($campo['label']))
        <label for="signos-{{ $clave }}">{{ $campo['label'] }}</label>
    @endif
    <div class="rm-signos__input-wrap">
        <input id="signos-{{ $clave }}" type="number" inputmode="decimal"
               aria-label="{{ $campo['label'] }}{{ $campo['unit'] !== '' ? ' en '.$campo['unit'] : ($clave === 'sis' ? ' en mmHg' : '') }}"
               min="{{ $limitesTecnicos[$clave]['min'] }}" max="{{ $limitesTecnicos[$clave]['max'] }}" step="{{ $campo['step'] }}"
               wire:model.live.debounce.350ms="{{ $campo['wire'] }}" x-model="values.{{ $clave }}"
               @focus="focusMeasurement('{{ $clave }}', $event.currentTarget)" @blur="validate('{{ $clave }}')"
               @click="if (!trendOpen) focusMeasurement('{{ $clave }}', $event.currentTarget)"
               aria-controls="signos-grafica-popup" :aria-expanded="trendOpen && active === @js(in_array($clave, ['sis', 'dia'], true) ? 'pa' : $clave)"
               @input="activateMeasurement('{{ $clave }}')" @input.debounce.250ms="validate('{{ $clave }}', false)"
               x-bind:aria-invalid="(hasError('{{ $clave }}') || @js($errors->has($errorClave))) ? 'true' : 'false'"
               aria-describedby="signos-{{ $clave }}-error{{ $errors->has($errorClave) ? ' signos-'.$clave.'-server-error' : '' }}">
        @if($campo['unit'] !== '')<span class="rm-signos__input-unit" aria-hidden="true">{{ $campo['unit'] }}</span>@endif
    </div>
    <p id="signos-{{ $clave }}-error" class="rm-signos__error" x-show="hasError('{{ $clave }}')" x-cloak role="alert"><i class="ph-bold ph-x-circle" aria-hidden="true"></i> <span x-text="errors.{{ $clave }}"></span></p>
    @error($errorClave) <p id="signos-{{ $clave }}-server-error" class="rm-signos__error" role="alert"><i class="ph-bold ph-x-circle" aria-hidden="true"></i> {{ $message }}</p> @enderror
</div>
