@props(['name', 'label', 'type' => 'text', 'value' => null, 'helper' => null, 'unit' => null, 'required' => false])
@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $describedBy = trim(($helper ? $id.'-help ' : '').($error ? $id.'-error' : ''));
@endphp
<div class="rm-field">
    <label class="rm-label {{ $required ? 'rm-label-required' : '' }}" for="{{ $id }}">{{ $label }}@if($unit) <span class="rm-field-unit">({{ $unit }})</span>@endif</label>
    <input {{ $attributes->except(['id'])->class(['rm-input', 'rm-input-error' => $error])->merge(['id' => $id, 'name' => $name, 'type' => $type, 'value' => old($name, $value), 'required' => $required ?: null, 'aria-invalid' => $error ? 'true' : 'false', 'aria-describedby' => $describedBy ?: null]) }}>
    @if($helper)<p id="{{ $id }}-help" class="rm-help">{{ $helper }}</p>@endif
    @if($error)<p id="{{ $id }}-error" class="rm-error" role="alert">{{ $error }}</p>@endif
</div>
