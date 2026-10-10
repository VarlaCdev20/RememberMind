@props(['id', 'label', 'model', 'captureKey', 'options', 'error', 'customType' => 'text', 'maxlength' => 60])
{{-- Atajos de captura; la opción personalizada conserva el contrato libre del registro. --}}
<div class="rm-dolor__capture-field" x-data="{ choice: @js(array_keys($options)).map(String).includes(String(values[@js($captureKey)] ?? '')) ? String(values[@js($captureKey)]) : '__custom' }">
    <label for="{{ $id }}">{{ $label }} <small>Opcional</small></label>
    <div class="rm-dolor__select-wrap">
    <select id="{{ $id }}" class="rm-select" x-model="choice" @change="chooseCaptureOption(@js($captureKey), @js($model), choice)"
            aria-invalid="{{ $errors->has($error) ? 'true' : 'false' }}" @error($error) aria-describedby="{{ $id }}-error" @enderror>
        @foreach($options as $value => $text)<option value="{{ $value }}">{{ $text }}</option>@endforeach
        <option value="__custom">Otro valor…</option>
    </select>
    <i class="ph-bold ph-caret-down" aria-hidden="true"></i>
    </div>
    <div x-show="choice === '__custom'" x-cloak class="rm-dolor__custom-value">
        <label for="{{ $id }}-custom">{{ $label }} personalizada</label>
        <input id="{{ $id }}-custom" class="rm-input" type="{{ $customType }}" wire:model="{{ $model }}" x-model="values.{{ $captureKey }}"
               @if($customType === 'number') min="0.01" step="any" inputmode="decimal" @else maxlength="{{ $maxlength }}" @endif
               aria-invalid="{{ $errors->has($error) ? 'true' : 'false' }}" @error($error) aria-describedby="{{ $id }}-error" @enderror>
    </div>
    @error($error)<p id="{{ $id }}-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
</div>
