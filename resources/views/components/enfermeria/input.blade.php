@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'icon' => null,
    'error' => null,
    'hint' => null,
])

@php
$inputId = $id ?? ($name ?? 'enf-input-'.uniqid());
$hasError = $error || ($name && $errors->has($name));
$errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full space-y-1">
    @if($label)
        <label for="{{ $inputId }}" class="enf-label">
            {{ $label }}
            @if($attributes->has('required'))
                <span class="text-[#C8645A]">*</span>
            @endif
        </label>
    @endif

    <div class="relative flex items-center">
        @if($icon)
            <div class="pointer-events-none absolute left-3 flex items-center text-[#6F7B8F]">
                <i class="ph-bold {{ $icon }} text-base"></i>
            </div>
        @endif

        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            {{ $attributes->merge(['class' => 'enf-input ' . ($icon ? 'pl-9 ' : '') . ($hasError ? 'enf-input-error' : '')]) }}
        />
    </div>

    @if($hasError)
        <p class="text-[11px] font-semibold text-[#C8645A] mt-1 flex items-center gap-1">
            <i class="ph-bold ph-warning-circle text-xs"></i>
            <span>{{ $errorMessage }}</span>
        </p>
    @elseif($hint)
        <p class="text-[11px] text-[#6F7B8F] mt-1">{{ $hint }}</p>
    @endif
</div>
