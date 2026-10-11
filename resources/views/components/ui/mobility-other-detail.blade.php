@props(['field', 'selection', 'label'])
<div x-show="values[@js($selection)] === 'OTRO'" x-cloak>
    <x-ui.field :for="'movilidad-'.$field" :label="$label" :error="$field" required help="Obligatorio al elegir Otro. Hasta 500 caracteres; se conserva en las observaciones del registro.">
        <input id="movilidad-{{ $field }}" class="rm-input" type="text" maxlength="500" x-model="values[@js($field)]" @input="setValue(@js($field), $event.target.value)" :disabled="values[@js($selection)] !== 'OTRO'" :required="values[@js($selection)] === 'OTRO'" aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}" aria-describedby="movilidad-{{ $field }}-help @error($field) movilidad-{{ $field }}-error @enderror">
    </x-ui.field>
</div>
