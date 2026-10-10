@props(['field', 'title', 'options', 'icon' => 'ph-circle', 'boolean' => false])
<fieldset class="rm-eliminacion__section" @if($boolean) :data-attention="values[@js($field)] === true ? 'true' : 'false'" @endif aria-describedby="@error($field) eliminacion-{{ $field }}-error @enderror">
    <legend><i class="ph-bold {{ $icon }}" aria-hidden="true"></i> {{ $title }}</legend>
    <div class="rm-eliminacion__choices">
        @foreach($options as $option)<x-ui.clinical-capture-choice :field="$field" :value="$option['value']" :label="$option['label']" data-empty="{{ $option['value'] === null || $option['value'] === '' ? 'true' : 'false' }}" data-observed-color="{{ $field === 'color_heces' ? $option['value'] : '' }}" />@endforeach
    </div>
    @error($field)<p id="eliminacion-{{ $field }}-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
</fieldset>
