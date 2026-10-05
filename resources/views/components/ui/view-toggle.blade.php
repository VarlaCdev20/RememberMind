@props([
    'model' => 'vistaModo',
    'value' => 'tarjetas',
    'options' => [
        ['value' => 'tarjetas', 'label' => 'Grid', 'icon' => 'ph-squares-four'],
        ['value' => 'tabla', 'label' => 'Lista', 'icon' => 'ph-list-dashes'],
    ],
    'label' => 'Modo de vista',
])

<div {{ $attributes->class(['rm-view-toggle']) }} role="group" aria-label="{{ $label }}">
    @foreach($options as $option)
        <button
            type="button"
            wire:click="$set('{{ $model }}', '{{ $option['value'] }}')"
            aria-label="{{ $option['label'] }}"
            aria-pressed="{{ $value === $option['value'] ? 'true' : 'false' }}"
            class="rm-view-toggle__btn {{ $value === $option['value'] ? 'is-active' : '' }}"
            title="{{ $option['label'] }}"
        >
            <i class="ph-bold {{ $option['icon'] }}" aria-hidden="true"></i>
            <span class="rm-view-toggle__label">{{ $option['label'] }}</span>
        </button>
    @endforeach
</div>