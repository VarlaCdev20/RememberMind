@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'placeholder' => null,
    'error' => null,
    'hint' => null,
])

@php
$selectId = $id ?? ($name ?? 'enf-select-'.uniqid());
$hasError = $error || ($name && $errors->has($name));
$errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full space-y-1">
    @if($label)
        <label for="{{ $selectId }}" class="enf-label">
            {{ $label }}
            @if($attributes->has('required'))
                <span class="text-[#C8645A]">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $selectId }}"
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'enf-select ' . ($hasError ? 'enf-input-error' : '')]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if($hasError)
        <p class="text-[11px] font-semibold text-[#C8645A] mt-1 flex items-center gap-1">
            <i class="ph-bold ph-warning-circle text-xs"></i>
            <span>{{ $errorMessage }}</span>
        </p>
    @elseif($hint)
        <p class="text-[11px] text-[#6F7B8F] mt-1">{{ $hint }}</p>
    @endif
</div>
