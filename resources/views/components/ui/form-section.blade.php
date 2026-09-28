@props([
    'title',
    'description' => null,
    'columns' => 2,
    'step' => null,
    'icon' => null,
])

<fieldset {{ $attributes->class(['rm-form-section']) }}>
    <legend class="rm-form-section-title">
        @if($step)
            <span class="rm-form-section-step">{{ $step }}</span>
        @endif
        @if($icon)
            <i class="ph-bold {{ $icon }}" aria-hidden="true"></i>
        @endif
        <span>{{ $title }}</span>
    </legend>
    @if($description)
        <p class="rm-form-section-description">
            {{ $description }}
        </p>
    @endif
    <div @class([
        'rm-form-grid',
        'md:grid-cols-2' => (int) $columns === 2,
        'md:grid-cols-3' => (int) $columns === 3,
        'md:grid-cols-4' => (int) $columns === 4,
    ])>
        {{ $slot }}
    </div>
</fieldset>
