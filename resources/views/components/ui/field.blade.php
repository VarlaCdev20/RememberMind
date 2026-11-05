@props([
    'label' => null,
    'for' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'hint' => null,
    'valid' => false,
])

<div {{ $attributes->class(['rm-field', 'rm-field--invalid' => $error && $errors->has($error), 'rm-field--valid' => $valid && !($error && $errors->has($error))]) }}>
    @if($label)
        <div class="flex items-start justify-between gap-3">
            <label @if($for) for="{{ $for }}" @endif @class(['rm-label', 'rm-label-required' => $required])>
                {{ $label }}
            </label>

            @if($hint)
                <span class="rm-field-hint">{{ $hint }}</span>
            @endif
        </div>
    @endif

    {{ $slot }}

    @if($error)
        @error($error)
            <p @if($for) id="{{ $for }}-error" @endif class="rm-error" role="alert">
                <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
                <span>{{ $message }}</span>
            </p>
        @enderror
    @endif

    @if($valid && !($error && $errors->has($error)))
        <p @if($for) id="{{ $for }}-valid" @endif class="rm-field-valid" role="status"><i class="ph-bold ph-check-circle" aria-hidden="true"></i> Dato válido</p>
    @endif

    @if($help)
        <p @if($for) id="{{ $for }}-help" @endif class="rm-help">{{ $help }}</p>
    @endif
</div>
