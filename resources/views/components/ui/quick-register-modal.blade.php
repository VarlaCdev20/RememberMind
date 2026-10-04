@props([
    'model', 'title', 'subtitle' => null, 'closeMethod', 'backMethod' => null,
    'backLabel' => 'Volver', 'id' => 'quick-register', 'icon' => null,
    'context' => null, 'footer' => null,
])
<x-ui.modal-livewire :id="$id" wire:model="{{ $model }}" class="{{ $attributes->get('class') }}" :title="$title" :subtitle="$subtitle" max-width="lg" :close-method="$closeMethod" :back-method="$backMethod" :back-label="$backLabel" :show-validation="false" :dismiss-on-backdrop="true" :draggable="true">
    @if(isset($icon))
        <x-slot:icon>{{ $icon }}</x-slot:icon>
    @endif
    @if(isset($context))
        <x-slot:context>{{ $context }}</x-slot:context>
    @endif
    {{ $slot }}
    @if(isset($footer))
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endif
</x-ui.modal-livewire>
