@props(['model', 'closeMethod', 'title' => 'Resumen del residente', 'subtitle' => null, 'footer' => null])
<x-ui.drawer-livewire wire:model="{{ $model }}" :title="$title" :subtitle="$subtitle" badge="" icon="ph-identification-card" size="lg" :close-method="$closeMethod" :dismiss-on-backdrop="true">
    {{ $slot }}
    @if(isset($footer) && trim((string) $footer) !== '')
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endif
</x-ui.drawer-livewire>
