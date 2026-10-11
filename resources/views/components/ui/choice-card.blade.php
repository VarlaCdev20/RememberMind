@props([
    'name',
    'value',
    'label',
    'description' => null,
    'icon' => 'ph-check-circle',
    'variant' => 'primary',
])

<label {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.defer'])->class(['rm-choice-card']) }}>
    <input type="radio" name="{{ $name }}" value="{{ $value }}" {{ $attributes->whereStartsWith('wire:model') }} class="peer sr-only">
    <span class="rm-choice-card-surface rm-choice-card-{{ $variant }}">
        <i class="ph-bold {{ $icon }} rm-choice-card-icon" aria-hidden="true"></i>
        <span class="rm-choice-card-label">{{ $label }}</span>
        @if($description)
            <span class="rm-choice-card-description">{{ $description }}</span>
        @endif
    </span>
</label>
