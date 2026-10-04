@props([
    'options',
    'mode' => 'alpine',
    'model' => 'vista',
    'value' => null,
    'changeMethod' => null,
    'label' => 'Modo de vista',
])

<div {{ $attributes->class(['rm-view-toggle']) }} role="group" aria-label="{{ $label }}">
    @foreach($options as $option)
        @php $active = $value === $option['value']; @endphp
        @if($mode === 'url')
            <a class="rm-view-toggle__btn {{ $active ? 'is-active' : '' }}" href="{{ $option['href'] }}" @if($active) aria-current="page" @endif>
                <i class="ph-bold {{ $option['icon'] }}" aria-hidden="true"></i><span class="rm-view-toggle__label">{{ $option['label'] }}</span>
            </a>
        @elseif($mode === 'livewire')
            <button type="button" class="rm-view-toggle__btn {{ $active ? 'is-active' : '' }}" wire:click="{{ $changeMethod }}('{{ $option['value'] }}')" aria-pressed="{{ $active ? 'true' : 'false' }}">
                <i class="ph-bold {{ $option['icon'] }}" aria-hidden="true"></i><span class="rm-view-toggle__label">{{ $option['label'] }}</span>
            </button>
        @else
            <button type="button" class="rm-view-toggle__btn" x-on:click="{{ $model }} = '{{ $option['value'] }}'" x-bind:class="{ 'is-active': {{ $model }} === '{{ $option['value'] }}' }" x-bind:aria-pressed="{{ $model }} === '{{ $option['value'] }}'">
                <i class="ph-bold {{ $option['icon'] }}" aria-hidden="true"></i><span class="rm-view-toggle__label">{{ $option['label'] }}</span>
            </button>
        @endif
    @endforeach
</div>
