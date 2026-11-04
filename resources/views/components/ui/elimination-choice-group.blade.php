@props(['field', 'title', 'options', 'icon' => 'ph-circle', 'boolean' => false])
<fieldset class="rm-eliminacion__section" @if($boolean) :data-attention="values[@js($field)] === true ? 'true' : 'false'" @endif aria-describedby="@error($field) eliminacion-{{ $field }}-error @enderror">
    <legend><i class="ph-bold {{ $icon }}" aria-hidden="true"></i> {{ $title }}</legend>
    <div class="rm-eliminacion__choices">
        @foreach($options as $option)<x-ui.clinical-capture-choice :field="$field" :value="$option['value']" :label="$option['label']" data-empty="{{ $option['value'] === null || $option['value'] === '' ? 'true' : 'false' }}" data-observed-color="{{ $field === 'color_heces' ? $option['value'] : '' }}" />@endforeach
    </div>
    @if(array_filter($options, fn ($option) => $option['value'] === 'OTRO'))
        <div x-show="values[@js($field)] === 'OTRO'" x-cloak class="rm-eliminacion__other" wire:key="eliminacion-otro-{{ $field }}">
            <x-ui.field for="eliminacion-{{ $field }}-otro" label="Especifica la otra opción" :error="$field.'_otro'" required help="Hasta 250 caracteres. Se conserva en las observaciones.">
                <input id="eliminacion-{{ $field }}-otro" class="rm-input" type="text" maxlength="250" x-model="values[@js($field.'_otro')]" @input="setValue(@js($field.'_otro'), $event.target.value)" :required="values[@js($field)] === 'OTRO'" :disabled="values[@js($field)] !== 'OTRO'" aria-invalid="{{ $errors->has($field.'_otro') ? 'true' : 'false' }}" aria-describedby="eliminacion-{{ $field }}-otro-help @error($field.'_otro') eliminacion-{{ $field }}-otro-error @enderror">
            </x-ui.field>
        </div>
    @endif
    @error($field)<p id="eliminacion-{{ $field }}-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
</fieldset>
