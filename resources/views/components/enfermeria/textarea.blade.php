@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'rows' => 3,
    'error' => null,
    'hint' => null,
])

@php
$areaId = $id ?? ($name ?? 'enf-textarea-'.uniqid());
$hasError = $error || ($name && $errors->has($name));
$errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full space-y-1">
    @if($label)
        <label for="{{ $areaId }}" class="enf-label">
            {{ $label }}
            @if($attributes->has('required'))
                <span class="text-[#C8645A]">*</span>
            @endif
        </label>
    @endif

    <textarea
        id="{{ $areaId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'enf-textarea ' . ($hasError ? 'enf-input-error' : '')]) }}
    >{{ $slot }}</textarea>

    @if($hasError)
        <p class="text-[11px] font-semibold text-[#C8645A] mt-1 flex items-center gap-1">
            <i class="ph-bold ph-warning-circle text-xs"></i>
            <span>{{ $errorMessage }}</span>
        </p>
    @elseif($hint)
        <p class="text-[11px] text-[#6F7B8F] mt-1">{{ $hint }}</p>
    @endif
</div>
