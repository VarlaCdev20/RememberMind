@pHp($clave = $campo['key'])
@php($errorClave = $campo['error'])
<div class="rm-signos__field" x-bind:class="{ 'rm-signos__field--invalid': hasError('{{ $clave }}') || @js($errors->has($errorClave)) }">
    @if(isset($campo['label']) && !in_array($clave, ['fc', 'fr', 'temp', 'sat', 'glucosa'], true))
        <label for="signos-{{ $clave }}">{{ $campo['label'] }}</label>
    @endif
    <div class="rm-signos__input-wrap">
        <input id="signos-{{ $clave }}" type="number" inputmode="decimal"
               min="{{ $clave === 'temp' ? '-99.9' : '0' }}" max="{{ $campo['max'] }}" step="{{ $campo['step'] }}"
               wire:model="{{ $campo['wire'] }}" x-model="values.{{ $clave }}"
               @focus="focus('{{ $clave }}')" @blur="validate('{{ $clave }}')"
               @input="if (touched.{{ $clave }}) validate('{{ $clave }}')"
               x-bind:aria-invalid="(hasError('{{ $clave }}') || @js($errors->has($errorClave))) ? 'true' : 'false'"
               aria-describedby="signos-{{ $clave }}-error signos-{{ $clave }}-server-error">
        <span class="rm-signos__input-unit" aria-hidden="true">{{ $campo['unit'] }}</span>
    </div>
    <p id="signos-{{ $clave }}-error" class="rm-signos__error" x-show="hasError('{{ $clave }}')" x-cloak role="alert"><i class="ph-bold ph-x-circle" aria-hidden="true"></i> <span x-text="errors.{{ $clave }}"></span></p>
    @error($errorClave) <p id="signos-{{ $clave }}-server-error" class="rm-signos__error" role="alert"><i class="ph-bold ph-x-circle" aria-hidden="true"></i> {{ $message }}</p> @enderror
</div>
