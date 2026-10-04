@props(['model', 'value', 'options', 'label' => 'Vista'])
<div {{ $attributes->class(['rm-view-toggle', 'rm-resident-directory__filter-view']) }} role="group" aria-label="{{ $label }}">
    @foreach($options as $option)
        <button type="button" wire:click="$set('{{ $model }}', '{{ $option['value'] }}')" aria-label="{{ $option['label'] }}" aria-pressed="{{ $value === $option['value'] ? 'true' : 'false' }}" class="{{ $value === $option['value'] ? 'is-active' : '' }}"><i class="ph-bold {{ $option['icon'] }}" aria-hidden="true"></i></button>
    @endforeach
</div>
